<?php

namespace App\Http\Controllers\Api\Polyclinic;

use App\Http\Controllers\Controller;
use App\Models\Polyclinic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PolyclinicController extends Controller
{
    /**
     * List semua poli (eager load doctors).
     */
    public function index(): JsonResponse
    {
        $polyclinics = Polyclinic::with(['doctors' => fn($q) => $q->orderBy('sort_order')])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $polyclinics->map(fn(Polyclinic $poly) => [
                'id' => $poly->id,
                'code' => $poly->code,
                'name' => $poly->name,
                'ruangan_code' => $poly->ruangan_code,
                'simrs_code' => $poly->simrs_code,
                'bpjs_code' => $poly->bpjs_code,
                'display_name' => $poly->display_name,
                'doctors_count' => $poly->doctors->count(),
                'doctors' => $poly->doctors->map(fn($doc) => [
                    'id' => $doc->id,
                    'name' => $doc->name,
                    'doctor_code' => $doc->doctor_code,
                    'master_doctor_uuid' => $doc->master_doctor_uuid,
                    'photo_url' => $doc->photo_url,
                    'specialty' => $doc->specialty,
                    'is_active' => $doc->is_active,
                    'sort_order' => $doc->sort_order,
                ])->toArray(),
                'created_at' => $poly->created_at?->toIso8601String(),
            ])->toArray(),
        ]);
    }

    /**
     * Tambah poli baru.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:polyclinics,code',
            'name' => 'required|string|max:255',
            'ruangan_code' => 'nullable|string|max:50',
            'simrs_code' => 'nullable|string|max:50',
            'bpjs_code' => 'nullable|string|max:50',
            'display_name' => 'nullable|string|max:255',
        ]);

        $validated['code'] = strtoupper($validated['code']);

        $polyclinic = Polyclinic::create($validated);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $polyclinic->id,
                'code' => $polyclinic->code,
                'name' => $polyclinic->name,
                'ruangan_code' => $polyclinic->ruangan_code,
            ],
        ], 201);
    }

    /**
     * Detail poli.
     */
    public function show(string $id): JsonResponse
    {
        $polyclinic = Polyclinic::with(['doctors' => fn($q) => $q->orderBy('sort_order')])->find($id);

        if (!$polyclinic) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Poliklinik tidak ditemukan.'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $polyclinic->id,
                'code' => $polyclinic->code,
                'name' => $polyclinic->name,
                'doctors' => $polyclinic->doctors->map(fn($doc) => [
                    'id' => $doc->id,
                    'name' => $doc->name,
                    'photo_url' => $doc->photo_url,
                    'specialty' => $doc->specialty,
                    'is_active' => $doc->is_active,
                    'sort_order' => $doc->sort_order,
                ])->toArray(),
            ],
        ]);
    }

    /**
     * Update poli.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $polyclinic = Polyclinic::find($id);

        if (!$polyclinic) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Poliklinik tidak ditemukan.'],
            ], 404);
        }

        $validated = $request->validate([
            'code' => 'sometimes|string|max:50|unique:polyclinics,code,' . $polyclinic->id,
            'name' => 'sometimes|string|max:255',
        ]);

        if (isset($validated['code'])) {
            $validated['code'] = strtoupper($validated['code']);
        }

        $polyclinic->update($validated);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $polyclinic->id,
                'code' => $polyclinic->code,
                'name' => $polyclinic->name,
            ],
        ]);
    }

    /**
     * Hapus poli (cascade hapus dokter & antrian).
     */
    public function destroy(string $id): JsonResponse
    {
        $polyclinic = Polyclinic::find($id);

        if (!$polyclinic) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Poliklinik tidak ditemukan.'],
            ], 404);
        }

        $polyclinic->delete();

        return response()->json([
            'success' => true,
            'data' => ['message' => 'Poliklinik berhasil dihapus.'],
        ]);
    }
}
