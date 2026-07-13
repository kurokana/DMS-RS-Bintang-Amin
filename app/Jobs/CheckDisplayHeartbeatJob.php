<?php

namespace App\Jobs;

use App\Events\DeviceStatusChanged;
use App\Models\DisplayDevice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckDisplayHeartbeatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Find displays that haven't sent a heartbeat in the last 60 seconds
        $offlineThreshold = now()->subSeconds(60);

        $timedOutDevices = DisplayDevice::where('status', 'online')
            ->where(function ($query) use ($offlineThreshold) {
                $query->whereNull('last_heartbeat_at')
                      ->orWhere('last_heartbeat_at', '<', $offlineThreshold);
            })
            ->get();

        foreach ($timedOutDevices as $device) {
            $device->update(['status' => 'offline']);

            // Broadcast status change so Office Admin Panel can react
            try {
                event(new DeviceStatusChanged(
                    $device->display_id,
                    $device->name,
                    'offline',
                    $device->last_heartbeat_at?->toIso8601String(),
                ));
            } catch (\Exception $e) {
                Log::warning("Failed to broadcast DeviceStatusChanged for [{$device->display_id}]: " . $e->getMessage());
            }

            Log::info("Display [{$device->display_id}] marked offline due to heartbeat timeout.");
        }
    }
}
