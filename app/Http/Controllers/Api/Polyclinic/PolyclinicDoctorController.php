<?php

namespace App\Http\Controllers\Api\Polyclinic;

use App\Http\Controllers\Controller;
use App\Models\Polyclinic;
use App\Models\PolyclinicDoctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PolyclinicDoctorController extends Controller
{
    /**
     * List dokter di poli tertentu.
     */
    public function index(string $polyclinicId): JsonResponse
    {
        $polyclinic = Polyclinic::find($polyclinicId);

        if (!$polyclinic) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Poliklinik tidak ditemukan.'],
            ], 404);
        }

        $doctors = $polyclinic->doctors()->orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'data' => $doctors->map(fn(PolyclinicDoctor $doc) => [
                'id' => $doc->id,
                'polyclinic_id' => $doc->polyclinic_id,
                'name' => $doc->name,
                'photo_url' => $doc->photo_url,
                'photo_path' => $doc->photo_path,
                'specialty' => $doc->specialty,
                'is_active' => $doc->is_active,
                'sort_order' => $doc->sort_order,
            ])->toArray(),
        ]);
    }

    /**
     * Tambah dokter ke poli (multipart upload foto).
     */
    public function store(Request $request, string $polyclinicId): JsonResponse
    {
        $polyclinic = Polyclinic::find($polyclinicId);

        if (!$polyclinic) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Poliklinik tidak ditemukan.'],
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'doctor_code' => 'nullable|string|max:50',
            'master_doctor_uuid' => 'nullable|string|max:50',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'specialty' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('doctors', 'public');
        }

        $doctor = PolyclinicDoctor::create([
            'polyclinic_id' => $polyclinic->id,
            'name' => $validated['name'],
            'doctor_code' => $validated['doctor_code'] ?? null,
            'master_doctor_uuid' => $validated['master_doctor_uuid'] ?? null,
            'photo_path' => $photoPath,
            'specialty' => $validated['specialty'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $doctor->id,
                'polyclinic_id' => $doctor->polyclinic_id,
                'name' => $doctor->name,
                'doctor_code' => $doctor->doctor_code,
                'master_doctor_uuid' => $doctor->master_doctor_uuid,
                'photo_url' => $doctor->photo_url,
                'specialty' => $doctor->specialty,
                'is_active' => $doctor->is_active,
                'sort_order' => $doctor->sort_order,
            ],
        ], 201);
    }

    /**
     * Detail dokter.
     */
    public function show(string $polyclinicId, string $id): JsonResponse
    {
        $doctor = PolyclinicDoctor::where('polyclinic_id', $polyclinicId)
            ->where('id', $id)
            ->first();

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Dokter tidak ditemukan.'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $doctor->id,
                'polyclinic_id' => $doctor->polyclinic_id,
                'name' => $doctor->name,
                'photo_url' => $doctor->photo_url,
                'specialty' => $doctor->specialty,
                'is_active' => $doctor->is_active,
                'sort_order' => $doctor->sort_order,
            ],
        ]);
    }

    /**
     * Update dokter (nama, foto, specialty, status aktif, urutan).
     */
    public function update(Request $request, string $polyclinicId, string $id): JsonResponse
    {
        $doctor = PolyclinicDoctor::where('polyclinic_id', $polyclinicId)
            ->where('id', $id)
            ->first();

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Dokter tidak ditemukan.'],
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'specialty' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        // Handle photo upload — replace old photo
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($doctor->photo_path) {
                Storage::disk('public')->delete($doctor->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('doctors', 'public');
        }

        // Remove 'photo' key (the file object) — we use 'photo_path' in DB
        unset($validated['photo']);

        $doctor->update($validated);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $doctor->id,
                'polyclinic_id' => $doctor->polyclinic_id,
                'name' => $doctor->name,
                'photo_url' => $doctor->photo_url,
                'specialty' => $doctor->specialty,
                'is_active' => $doctor->is_active,
                'sort_order' => $doctor->sort_order,
            ],
        ]);
    }

    /**
     * Hapus dokter (cascade hapus antrian).
     */
    public function destroy(string $polyclinicId, string $id): JsonResponse
    {
        $doctor = PolyclinicDoctor::where('polyclinic_id', $polyclinicId)
            ->where('id', $id)
            ->first();

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Dokter tidak ditemukan.'],
            ], 404);
        }

        // Delete photo file
        if ($doctor->photo_path) {
            Storage::disk('public')->delete($doctor->photo_path);
        }

        $doctor->delete();

        return response()->json([
            'success' => true,
            'data' => ['message' => 'Dokter berhasil dihapus.'],
        ]);
    }
}
