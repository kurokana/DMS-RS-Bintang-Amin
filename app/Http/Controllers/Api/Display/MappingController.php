<?php

namespace App\Http\Controllers\Api\Display;

use App\Http\Controllers\Controller;
use App\Models\DisplayDevice;
use App\Services\DisplayMappingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MappingController extends Controller
{
    protected DisplayMappingService $mappingService;

    public function __construct(DisplayMappingService $mappingService)
    {
        $this->mappingService = $mappingService;
    }

    /**
     * Update display mapping.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $device = DisplayDevice::findOrFail($id);

        try {
            $validated = $request->validate([
                'target_type' => 'required|string|in:ward_class,operating_room,ward_summary,inpatient_room',
                'target_id' => 'required|string',
            ]);

            $mapping = $this->mappingService->updateMapping(
                $device,
                $validated['target_type'],
                $validated['target_id']
            );

            return response()->json([
                'success' => true,
                'data' => $mapping
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'MAPPING_TARGET_INVALID',
                    'message' => $e->getMessage(),
                    'details' => $e->errors(),
                ]
            ], 422);
        }
    }
}
