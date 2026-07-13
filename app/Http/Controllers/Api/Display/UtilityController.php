<?php

namespace App\Http\Controllers\Api\Display;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\OperatingRoom;
use App\Models\WardClass;
use App\Services\BpjsOperatingRoomSyncService;
use App\Services\BpjsWardSyncService;
use Illuminate\Http\JsonResponse;

class UtilityController extends Controller
{
    /**
     * Get list of wards with current availability.
     */
    public function wards(): JsonResponse
    {
        $wards = WardClass::with('currentAvailability')->get();
        return response()->json(['data' => $wards]);
    }

    /**
     * Get list of operating rooms.
     */
    public function rooms(): JsonResponse
    {
        $rooms = OperatingRoom::with('schedules')->get();
        return response()->json(['data' => $rooms]);
    }

    /**
     * Trigger manual sync of wards.
     */
    public function syncWards(BpjsWardSyncService $syncService): JsonResponse
    {
        try {
            $syncService->sync();
            return response()->json(['message' => 'Sync ward availability successful']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Trigger manual sync of schedules.
     */
    public function syncSchedules(BpjsOperatingRoomSyncService $syncService): JsonResponse
    {
        try {
            $syncService->sync();
            return response()->json(['message' => 'Sync operating schedules successful']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get audit logs.
     */
    public function auditLogs(): JsonResponse
    {
        $logs = AuditLog::with('user')->orderBy('created_at', 'desc')->take(30)->get();
        return response()->json(['data' => $logs]);
    }
}
