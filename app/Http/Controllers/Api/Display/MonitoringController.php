<?php

namespace App\Http\Controllers\Api\Display;

use App\Http\Controllers\Controller;
use App\Models\DisplayDevice;
use Illuminate\Http\JsonResponse;

class MonitoringController extends Controller
{
    /**
     * Get display devices monitoring dashboard.
     */
    public function index(): JsonResponse
    {
        $devices = DisplayDevice::with(['mappings.target'])->get();

        $total = $devices->count();
        $online = $devices->where('status', 'online')->count();
        $offline = $devices->where('status', 'offline')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total' => $total,
                    'online' => $online,
                    'offline' => $offline,
                ],
                'devices' => $devices
            ]
        ]);
    }
}
