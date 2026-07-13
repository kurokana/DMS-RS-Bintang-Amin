<?php

namespace App\Adapters;

use App\Contracts\BpjsOperatingRoomAdapterInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OfficeBpjsOperatingRoomAdapter implements BpjsOperatingRoomAdapterInterface
{
    /**
     * Cache key for OR data fallback
     */
    const CACHE_KEY = 'fallback_bpjs_operating_rooms';
    
    /**
     * Fallback TTL in seconds (e.g. 24 hours)
     */
    const CACHE_TTL = 86400;

    /**
     * Get operating rooms data. Try from Office API, fallback to Cache.
     */
    public function fetchOperatingRoomSchedules(): array
    {
        $apiUrl = env('OFFICE_API_URL', 'http://office.test/api') . '/bpjs/rooms';

        try {
            $response = Http::timeout(10)->get($apiUrl);

            if ($response->successful()) {
                $body = $response->json();
                if (isset($body['success']) && $body['success'] && isset($body['data'])) {
                    $rooms = $body['data'];
                    
                    // Format to match old Fake adapter's expected structure
                    $formattedData = [];
                    foreach ($rooms as $room) {
                        $schedules = [];
                        foreach ($room['schedules'] as $sch) {
                            $schedules[] = [
                                'schedule_id' => $sch['id'],
                                'patient_name' => $sch['patient_name'],
                                'scheduled_start' => $sch['start_time'],
                                'actual_start' => $sch['actual_start'] ?? null,
                                'status' => $sch['status'],
                            ];
                        }

                        $formattedData[] = [
                            'bpjs_or_code' => $room['room_code'],
                            'name' => $room['room_name'],
                            'schedules' => $schedules,
                        ];
                    }

                    // Save to cache for fallback
                    Cache::put(self::CACHE_KEY, $formattedData, self::CACHE_TTL);

                    return $formattedData;
                }
            }

            Log::warning("Office API for rooms returned unsuccessful response. Status: " . $response->status());
        } catch (\Exception $e) {
            Log::error("Failed to fetch rooms from Office API: " . $e->getMessage());
        }

        // Fallback to cache if request failed
        if (Cache::has(self::CACHE_KEY)) {
            Log::info("Using fallback cache for BPJS operating rooms data.");
            return Cache::get(self::CACHE_KEY);
        }

        // If no cache exists yet, return empty array
        Log::warning("No fallback cache exists for BPJS operating rooms data.");
        return [];
    }
}
