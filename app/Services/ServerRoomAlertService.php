<?php

namespace App\Services;

use App\Models\IotDevice;
use App\Models\IotSensorReading;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ServerRoomAlertService
{
    /**
     * Evaluates sensor reading and sends instant ntfy.sh push alerts if thresholds are exceeded.
     */
    public static function checkAndSendAlert(IotDevice $device, IotSensorReading $reading): void
    {
        $enabled = env('NTFY_ALERT_ENABLED', true);
        if (!$enabled) {
            return;
        }

        $topic         = env('NTFY_TOPIC', 'rsba-server-room-alerts-2026');
        $tempThreshold = (float) env('NTFY_TEMP_THRESHOLD', 28.0);
        $humThreshold  = (float) env('NTFY_HUMIDITY_THRESHOLD', 70.0);
        $cooldownMin   = (int) env('NTFY_ALERT_COOLDOWN_MINUTES', 15);

        $temp = (float) $reading->temperature;
        $hum  = (float) $reading->humidity;

        $isCritical = ($temp >= $tempThreshold) || ($hum >= $humThreshold);
        $cacheAlertKey    = "server_room_alert_active_{$device->id}";
        $cacheCooldownKey = "server_room_alert_cooldown_{$device->id}";

        if ($isCritical) {
            $isOnCooldown = Cache::has($cacheCooldownKey);
            
            if (!$isOnCooldown) {
                // Send Critical Push Alert via ntfy.sh
                try {
                    $reasons = [];
                    if ($temp >= $tempThreshold) {
                        $reasons[] = "Suhu Tinggi ({$temp}°C >= {$tempThreshold}°C)";
                    }
                    if ($hum >= $humThreshold) {
                        $reasons[] = "Kelembapan Tinggi ({$hum}% >= {$humThreshold}%)";
                    }
                    $reasonStr = implode(', ', $reasons);

                    $location = $device->location ?? 'Ruang Server Utama RSBA';

                    Http::timeout(5)->post("https://ntfy.sh/{$topic}", [
                        'topic'    => $topic,
                        'title'    => "🚨 [ALERT RSBA] Ruang Server Overheat / Lembap!",
                        'message'  => "Terdeteksi {$reasonStr} di {$location}.\nSuhu: {$temp}°C | Kelembapan: {$hum}%\nPerangkat: {$device->serial_number}\nWaktu: " . now()->format('d/m/Y H:i:s') . "\nSegera periksa AC pendingin!",
                        'priority' => 5, // Urgent alarm sound on phone
                        'tags'     => ['warning', 'fire', 'thermometer'],
                    ]);

                    Cache::put($cacheAlertKey, true, now()->addDays(1));
                    Cache::put($cacheCooldownKey, true, now()->addMinutes($cooldownMin));
                    Log::warning("IoT Alert dispatched to ntfy.sh/{$topic} for {$device->serial_number}: {$reasonStr}");
                } catch (\Exception $e) {
                    Log::error("Failed to send ntfy alert: " . $e->getMessage());
                }
            }
        } else {
            // Check if recovered from previous critical state
            if (Cache::has($cacheAlertKey)) {
                try {
                    $location = $device->location ?? 'Ruang Server Utama RSBA';
                    Http::timeout(5)->post("https://ntfy.sh/{$topic}", [
                        'topic'    => $topic,
                        'title'    => "✅ [RECOVERED] Kondisi Ruang Server Normal",
                        'message'  => "Suhu dan kelembapan di {$location} telah kembali stabil.\nSuhu: {$temp}°C | Kelembapan: {$hum}%\nWaktu: " . now()->format('d/m/Y H:i:s'),
                        'priority' => 3,
                        'tags'     => ['white_check_mark', 'shield'],
                    ]);
                    Cache::forget($cacheAlertKey);
                    Cache::forget($cacheCooldownKey);
                    Log::info("IoT Recovery alert dispatched to ntfy.sh/{$topic}");
                } catch (\Exception $e) {
                    Log::error("Failed to send ntfy recovery alert: " . $e->getMessage());
                }
            }
        }
    }
}
