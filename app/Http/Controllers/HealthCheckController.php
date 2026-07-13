<?php

namespace App\Http\Controllers;

use App\Models\SyncLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthCheckController extends Controller
{
    /**
     * Check application health status.
     */
    public function check(): JsonResponse
    {
        $errors = [];
        
        // 1. Check DB Connection
        $dbStatus = 'ok';
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $dbStatus = 'failed';
            $errors[] = 'Database connection failed: ' . $e->getMessage();
        }

        // 2. Check Redis Connection
        $redisStatus = 'ok';
        try {
            Redis::connection()->ping();
        } catch (\Exception $e) {
            $redisStatus = 'failed';
            $errors[] = 'Redis connection failed: ' . $e->getMessage();
        }

        // 3. Get last sync information
        $wardLastSync = SyncLog::where('source', 'bpjs_ward')
            ->where('status', 'success')
            ->latest('synced_at')
            ->first();

        $orLastSync = SyncLog::where('source', 'bpjs_operating_room')
            ->where('status', 'success')
            ->latest('synced_at')
            ->first();

        $bpjsStatus = 'connected';
        
        // If last sync failed or is older than 5 minutes, mark BPJS as disconnected
        $threshold = now()->subMinutes(5);
        
        $wardSyncLog = SyncLog::where('source', 'bpjs_ward')->latest()->first();
        $orSyncLog = SyncLog::where('source', 'bpjs_operating_room')->latest()->first();

        $wardFailed = $wardSyncLog && $wardSyncLog->status === 'failed';
        $orFailed = $orSyncLog && $orSyncLog->status === 'failed';

        if ($wardFailed || $orFailed || ($wardLastSync && $wardLastSync->synced_at < $threshold) || ($orLastSync && $orLastSync->synced_at < $threshold)) {
            $bpjsStatus = 'disconnected';
        }

        $statusCode = empty($errors) ? 200 : 500;

        return response()->json([
            'status' => empty($errors) ? 'ok' : 'error',
            'db' => $dbStatus,
            'redis' => $redisStatus,
            'bpjs_status' => $bpjsStatus,
            'bpjs_ward_last_sync' => $wardLastSync?->synced_at?->toIso8601String(),
            'bpjs_or_last_sync' => $orLastSync?->synced_at?->toIso8601String(),
            'errors' => $errors,
        ], $statusCode);
    }
}
