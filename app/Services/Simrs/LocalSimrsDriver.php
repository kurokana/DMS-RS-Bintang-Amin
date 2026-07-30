<?php

namespace App\Services\Simrs;

use App\Contracts\SimrsQueueServiceInterface;
use App\DTOs\SimrsQueueData;
use App\Models\Polyclinic;
use App\Models\PolyclinicQueue;

class LocalSimrsDriver implements SimrsQueueServiceInterface
{
    public function getTodayQueue(string $polyCode, ?string $doctorCode = null): array
    {
        $polyclinic = Polyclinic::where('code', strtoupper($polyCode))
            ->orWhere('id', $polyCode)
            ->first();

        if (!$polyclinic) {
            return [];
        }

        $query = PolyclinicQueue::where('polyclinic_id', $polyclinic->id)
            ->where('queue_date', today());

        if ($doctorCode) {
            $query->whereHas('doctor', function ($q) use ($doctorCode) {
                $q->where('doctor_code', $doctorCode)
                  ->orWhere('id', $doctorCode);
            });
        }

        $queues = $query->orderByRaw("CASE WHEN status = 'terlewat' THEN 1 ELSE 0 END")
            ->orderBy('queue_number')
            ->get();

        return $queues->map(fn($q) => SimrsQueueData::fromArray([
            'id' => $q->id,
            'polyclinic_id' => $q->polyclinic_id,
            'doctor_id' => $q->doctor_id,
            'queue_number' => $q->queue_number,
            'patient_name' => $q->patient_name,
            'status' => $q->status,
            'queue_date' => $q->queue_date->format('Y-m-d'),
            'called_at' => $q->called_at?->toIso8601String(),
            'completed_at' => $q->completed_at?->toIso8601String(),
        ], isFallback: true)->toArray())->toArray();
    }

    public function updateQueueStatus(string $queueId, string $status): bool
    {
        $queue = PolyclinicQueue::find($queueId);
        if (!$queue) {
            return false;
        }

        $updateData = ['status' => $status];
        if ($status === 'dilayani') {
            $updateData['called_at'] = now();
        } elseif ($status === 'selesai') {
            $updateData['completed_at'] = now();
        }

        return $queue->update($updateData);
    }

    public function checkConnection(): bool
    {
        // Local DB is always available if database connection is up
        return true;
    }
}
