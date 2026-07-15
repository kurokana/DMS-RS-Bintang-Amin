<?php

namespace App\Http\Controllers\Api\Polyclinic;

use App\Http\Controllers\Controller;
use App\Models\Polyclinic;
use App\Models\PolyclinicQueue;
use App\Services\PolyclinicService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PolyclinicQueueController extends Controller
{
    protected PolyclinicService $service;

    public function __construct(PolyclinicService $service)
    {
        $this->service = $service;
    }

    /**
     * List antrian per poli (filter opsional: doctor_id, date).
     */
    public function index(Request $request, string $polyId): JsonResponse
    {
        $polyclinic = Polyclinic::find($polyId);

        if (!$polyclinic) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Poliklinik tidak ditemukan.'],
            ], 404);
        }

        $query = PolyclinicQueue::where('polyclinic_id', $polyId)
            ->orderBy('queue_number');

        // Filter by doctor
        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->input('doctor_id'));
        }

        // Filter by date (default: hari ini)
        $date = $request->input('date', today()->toDateString());
        $query->where('queue_date', $date);

        $queues = $query->with('doctor:id,name')->get();

        return response()->json([
            'success' => true,
            'data' => $queues->map(fn(PolyclinicQueue $q) => [
                'id' => $q->id,
                'polyclinic_id' => $q->polyclinic_id,
                'doctor_id' => $q->doctor_id,
                'doctor_name' => $q->doctor?->name,
                'queue_number' => $q->queue_number,
                'patient_name' => $q->patient_name,
                'status' => $q->status,
                'queue_date' => $q->queue_date->toDateString(),
                'called_at' => $q->called_at?->toIso8601String(),
                'completed_at' => $q->completed_at?->toIso8601String(),
            ])->toArray(),
        ]);
    }

    /**
     * Tambah pasien ke antrian.
     * Wadah API untuk integrasi SIMRS.
     */
    public function store(Request $request, string $polyId): JsonResponse
    {
        $validated = $request->validate([
            'doctor_id' => 'required|uuid|exists:polyclinic_doctors,id',
            'patient_name' => 'required|string|max:255',
        ]);

        try {
            $queue = $this->service->addPatientToQueue(
                $polyId,
                $validated['doctor_id'],
                $validated['patient_name']
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $queue->id,
                    'queue_number' => $queue->queue_number,
                    'patient_name' => $queue->patient_name,
                    'status' => $queue->status,
                    'queue_date' => $queue->queue_date->toDateString(),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'QUEUE_ERROR', 'message' => $e->getMessage()],
            ], 422);
        }
    }

    /**
     * Update status antrian (menunggu→dilayani, dilayani→selesai, menunggu→terlewat).
     */
    public function updateStatus(Request $request, string $polyId, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:menunggu,dilayani,selesai,terlewat',
        ]);

        try {
            $queue = $this->service->updateQueueStatus($id, $validated['status']);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $queue->id,
                    'queue_number' => $queue->queue_number,
                    'patient_name' => $queue->patient_name,
                    'status' => $queue->status,
                    'called_at' => $queue->called_at?->toIso8601String(),
                    'completed_at' => $queue->completed_at?->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'STATUS_ERROR', 'message' => $e->getMessage()],
            ], 422);
        }
    }

    /**
     * Panggil ulang pasien terlewat (turun 2 posisi).
     */
    public function requeue(string $polyId, string $id): JsonResponse
    {
        try {
            $queue = $this->service->requeueSkippedPatient($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $queue->id,
                    'queue_number' => $queue->queue_number,
                    'patient_name' => $queue->patient_name,
                    'status' => $queue->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'REQUEUE_ERROR', 'message' => $e->getMessage()],
            ], 422);
        }
    }

    /**
     * Hapus entry antrian.
     */
    public function destroy(string $polyId, string $id): JsonResponse
    {
        $queue = PolyclinicQueue::where('polyclinic_id', $polyId)
            ->where('id', $id)
            ->first();

        if (!$queue) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Antrian tidak ditemukan.'],
            ], 404);
        }

        $queue->delete();

        return response()->json([
            'success' => true,
            'data' => ['message' => 'Antrian berhasil dihapus.'],
        ]);
    }
}
