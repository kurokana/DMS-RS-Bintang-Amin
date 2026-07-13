<?php

namespace App\Http\Controllers\Api\Display;

use App\Http\Controllers\Controller;
use App\Services\DisplayDeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DisplayController extends Controller
{
    protected DisplayDeviceService $deviceService;

    public function __construct(DisplayDeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    /**
     * Get all display devices.
     */
    public function index(): JsonResponse
    {
        $devices = $this->deviceService->getAllDevices();

        return response()->json([
            'success' => true,
            'data' => $devices
        ]);
    }

    /**
     * Create a new display device.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'display_id' => 'required|string|max:50',
                'name' => 'required|string|max:255',
            ]);

            $device = $this->deviceService->createDevice($validated);

            return response()->json([
                'success' => true,
                'data' => $device
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'DISPLAY_ID_TAKEN',
                    'message' => $e->getMessage(),
                    'details' => $e->errors(),
                ]
            ], 409);
        }
    }
    /**
     * Update an existing display device.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $device = collect($this->deviceService->getAllDevices())->firstWhere('display_id', $id);
        
        if (!$device) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Display device not found.'
                ]
            ], 404);
        }

        $updatedDevice = $this->deviceService->updateDevice($id, $validated);

        return response()->json([
            'success' => true,
            'data' => $updatedDevice
        ]);
    }
}
