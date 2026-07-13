<?php

namespace App\Adapters;

use App\Contracts\BpjsWardAdapterInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OfficeBpjsWardAdapter implements BpjsWardAdapterInterface
{
    /**
     * Cache key for wards data fallback
     */
    const CACHE_KEY = 'fallback_bpjs_wards';
    
    /**
     * Fallback TTL in seconds (e.g. 24 hours)
     */
    const CACHE_TTL = 86400;

    /**
     * Get wards data. Try from Office API, fallback to Cache.
     * Return formatted data similar to the old Fake adapter.
     */
    public function fetchWardAvailability(): array
    {
        $apiUrl = env('OFFICE_API_URL', 'http://office.test/api') . '/bpjs/wards';

        try {
            $response = Http::timeout(10)->get($apiUrl);

            if ($response->successful()) {
                $body = $response->json();
                if (isset($body['success']) && $body['success'] && isset($body['data'])) {
                    $wards = $body['data'];
                    
                    // Format to match old Fake adapter's expected structure for the jobs
                    $formattedData = [];
                    foreach ($wards as $ward) {
                        $formattedData[] = [
                            'bpjs_class_code' => $ward['kodekelas'],
                            'name' => $ward['kelas'],
                            'bed_total' => $ward['kapasitas'],
                            'bed_occupied' => $ward['terisi'] ?? ($ward['kapasitas'] - $ward['tersedia']),
                            'bed_available' => $ward['tersedia'],
                        ];
                    }

                    // Save to cache for fallback
                    Cache::put(self::CACHE_KEY, $formattedData, self::CACHE_TTL);

                    return $formattedData;
                }
            }

            Log::warning("Office API for wards returned unsuccessful response. Status: " . $response->status());
        } catch (\Exception $e) {
            Log::error("Failed to fetch wards from Office API: " . $e->getMessage());
        }

        // Fallback to cache if request failed
        if (Cache::has(self::CACHE_KEY)) {
            Log::info("Using fallback cache for BPJS wards data.");
            return Cache::get(self::CACHE_KEY);
        }

        // If no cache exists yet, return empty array to prevent crashing
        Log::warning("No fallback cache exists for BPJS wards data.");
        return [];
    }
}
