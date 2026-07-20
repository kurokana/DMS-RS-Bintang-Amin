<?php

namespace App\Services;

use App\Events\PolyclinicQueueChanged;
use App\Models\Polyclinic;
use App\Models\PolyclinicDoctor;
use App\Models\PolyclinicQueue;
use Illuminate\Validation\ValidationException;

class PolyclinicService
{
    /**
     * Ambil state lengkap poli untuk display (dokter aktif + antrian hari ini).
     */
    public function getPolyclinicState(string $polyclinicId): ?array
    {
        $polyclinic = Polyclinic::with([
            'doctors' => fn($q) => $q->where('is_active', true)->orderBy('sort_order'),
            'doctors.todayQueue',
        ])->find($polyclinicId);

        if (!$polyclinic) {
            return null;
        }

        return [
            'polyclinic_code' => $polyclinic->code,
            'polyclinic_name' => $polyclinic->name,
            'doctors' => $polyclinic->doctors->map(fn(PolyclinicDoctor $doc) => [
                'id' => $doc->id,
                'name' => $doc->name,
                'photo_url' => $doc->photo_url,
                'specialty' => $doc->specialty,
                'queue' => $doc->todayQueue->map(fn(PolyclinicQueue $q) => [
                    'id' => $q->id,
                    'queue_number' => $q->queue_number,
                    'patient_name' => $q->patient_name, // Nama lengkap, TIDAK di-mask
                    'status' => $q->status,
                    'called_at' => $q->called_at?->toIso8601String(),
                ])->toArray(),
            ])->toArray(),
        ];
    }

    /**
     * Tambah pasien ke antrian — auto-assign queue_number.
     * queue_number = max(queue_number) + 1 per doctor per hari.
     */
    public function addPatientToQueue(string $polyclinicId, string $doctorId, string $patientName): PolyclinicQueue
    {
        // Validate polyclinic exists
        $polyclinic = Polyclinic::findOrFail($polyclinicId);

        // Validate doctor exists and belongs to this polyclinic
        $doctor = PolyclinicDoctor::where('id', $doctorId)
            ->where('polyclinic_id', $polyclinicId)
            ->firstOrFail();

        // Auto-increment queue number per doctor per day
        $nextNumber = PolyclinicQueue::where('polyclinic_id', $polyclinicId)
            ->where('doctor_id', $doctorId)
            ->where('queue_date', today())
            ->max('queue_number');

        $nextNumber = ($nextNumber ?? 0) + 1;

        $queue = PolyclinicQueue::create([
            'polyclinic_id' => $polyclinicId,
            'doctor_id' => $doctorId,
            'queue_number' => $nextNumber,
            'patient_name' => $patientName,
            'status' => 'menunggu',
            'queue_date' => today(),
        ]);

        $this->broadcastQueueChange($polyclinicId);

        return $queue;
    }

    /**
     * Update status antrian.
     *
     * Transisi yang valid:
     * - menunggu  → dilayani  (set called_at)
     * - menunggu  → terlewat
     * - dilayani  → selesai   (set completed_at)
     * - terlewat  → menunggu  (via requeue, bukan method ini)
     */
    public function updateQueueStatus(string $queueId, string $newStatus): PolyclinicQueue
    {
        $queue = PolyclinicQueue::findOrFail($queueId);

        $validTransitions = [
            'menunggu' => ['dilayani', 'terlewat'],
            'dilayani' => ['selesai'],
            'terlewat' => ['menunggu', 'dilayani'], 
            'selesai' => [],
        ];

        $allowed = $validTransitions[$queue->status] ?? [];

        if (!in_array($newStatus, $allowed)) {
            throw ValidationException::withMessages([
                'status' => ["Tidak bisa mengubah status dari '{$queue->status}' ke '{$newStatus}'."],
            ]);
        }

        $updateData = ['status' => $newStatus];

        if ($newStatus === 'dilayani') {
            $updateData['called_at'] = now();
        } elseif ($newStatus === 'selesai') {
            $updateData['completed_at'] = now();
        }

        $queue->update($updateData);

        $this->broadcastQueueChange($queue->polyclinic_id);

        return $queue->fresh();
    }

    /**
     * Requeue: panggil ulang pasien yang terlewat.
     *
     * Mekanisme:
     * 1. Cari posisi queue_number pasien yang saat ini berstatus 'dilayani'
     * 2. Target posisi = posisi dilayani + 2
     * 3. Geser queue_number pasien yang >= target posisi (increment +1)
     * 4. Set pasien terlewat ke target posisi, status → menunggu
     */
    public function requeueSkippedPatient(string $queueId): PolyclinicQueue
    {
        $queue = PolyclinicQueue::findOrFail($queueId);

        if ($queue->status !== 'terlewat') {
            throw ValidationException::withMessages([
                'status' => ['Hanya pasien berstatus terlewat yang bisa dipanggil ulang.'],
            ]);
        }

        // Cari pasien yang sedang dilayani oleh dokter yang sama hari ini
        $currentlyServing = PolyclinicQueue::where('doctor_id', $queue->doctor_id)
            ->where('queue_date', today())
            ->where('status', 'dilayani')
            ->first();

        // Jika ada yang dilayani, base = posisi dilayani; jika tidak, base = posisi menunggu pertama
        if ($currentlyServing) {
            $basePosition = $currentlyServing->queue_number;
        } else {
            $basePosition = PolyclinicQueue::where('doctor_id', $queue->doctor_id)
                ->where('queue_date', today())
                ->whereIn('status', ['menunggu'])
                ->min('queue_number');

            // Jika tidak ada yang menunggu, taruh di akhir
            if ($basePosition === null) {
                $basePosition = PolyclinicQueue::where('doctor_id', $queue->doctor_id)
                    ->where('queue_date', today())
                    ->max('queue_number') ?? 0;
            }
        }

        $targetPosition = $basePosition + 2;

        // Geser antrian: semua pasien menunggu yang queue_number >= targetPosition naik 1
        PolyclinicQueue::where('doctor_id', $queue->doctor_id)
            ->where('queue_date', today())
            ->where('queue_number', '>=', $targetPosition)
            ->where('status', 'menunggu')
            ->increment('queue_number');

        // Set pasien ke posisi baru, status → menunggu
        $queue->update([
            'queue_number' => $targetPosition,
            'status' => 'menunggu',
            'called_at' => null,
            'completed_at' => null,
        ]);

        $this->broadcastQueueChange($queue->polyclinic_id);

        return $queue->fresh();
    }

    /**
     * Broadcast perubahan antrian ke semua display yang mapped ke poli ini.
     */
    protected function broadcastQueueChange(string $polyclinicId): void
    {
        event(new PolyclinicQueueChanged($polyclinicId));
    }
}
