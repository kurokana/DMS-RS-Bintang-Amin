<?php

namespace App\Http\Controllers\Api\Iot;

use App\Http\Controllers\Controller;
use App\Models\IotDevice;
use App\Models\IotSensorReading;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ServerRoomTelemetryController extends Controller
{
    /**
     * Public ping endpoint for ESP32 latency measurement.
     * GET /api/v1/iot/ping
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'pong' => true,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * Ingest telemetry data sent by ESP32 every 5 minutes.
     * POST /api/v1/iot/telemetry
     */
    public function telemetry(Request $request): JsonResponse
    {
        // Gather input from JSON payload or Form data with robust fallback
        $payload = $request->all();

        if (empty($payload)) {
            $rawContent = $request->getContent();
            $decoded = json_decode($rawContent, true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        // 1. Extract Bearer token or token parameter
        $token = $request->bearerToken() ?? ($payload['api_token'] ?? env('IOT_DEVICE_API_TOKEN', 'rsba_iot_server_room_secret_token_2026'));
        $serialNumber = $payload['device_serial'] ?? env('IOT_DEVICE_SERIAL', 'ESP32-SERVER-ROOM-001');

        // 2. Validate sensor payload
        $validator = Validator::make($payload, [
            'temperature'   => 'required|numeric',
            'humidity'      => 'required|numeric',
            'rssi'          => 'nullable|numeric',
            'ip_address'    => 'nullable|string',
            'latency_ms'    => 'nullable|numeric',
            'uptime'        => 'nullable|numeric',
            'mac_address'   => 'nullable|string',
            'device_serial' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        // 3. Find or auto-register matching device
        $device = IotDevice::where('api_token', $token)->first();

        if (!$device) {
            $device = IotDevice::where('serial_number', $serialNumber)->first();
        }

        $now = now();
        $macAddress = $validated['mac_address'] ?? '48:9D:31:04:D6:E8';

        if (!$device) {
            // Auto-create device record on first telemetry packet
            $device = IotDevice::create([
                'serial_number'    => $serialNumber,
                'name'             => 'Sensor Ruang Server Utama',
                'location'         => 'Ruang Server Utama RSBA',
                'mac_address'      => $macAddress,
                'ip_address'       => $validated['ip_address'] ?? $request->ip(),
                'firmware_version' => '1.0.0',
                'status'           => 'ONLINE',
                'api_token'        => $token,
                'last_seen_at'     => $now,
            ]);
        } else {
            // Update Device state
            $device->update([
                'status'       => 'ONLINE',
                'last_seen_at' => $now,
                'ip_address'   => $validated['ip_address'] ?? $request->ip(),
                'mac_address'  => $macAddress,
            ]);
        }

        // 4. Store Sensor Reading Log
        $reading = IotSensorReading::create([
            'device_id'      => $device->id,
            'temperature'   => $validated['temperature'],
            'humidity'      => $validated['humidity'],
            'rssi'          => $validated['rssi'] ?? 0,
            'latency_ms'    => $validated['latency_ms'] ?? null,
            'uptime_seconds' => $validated['uptime'] ?? null,
            'recorded_at'   => $now,
        ]);

        Log::info("IoT Ingestion Success [{$device->serial_number}]: Temp={$validated['temperature']}C, Hum={$validated['humidity']}%, RSSI={$validated['rssi']}dBm");

        return response()->json([
            'status'      => 'success',
            'message'     => 'Telemetry recorded successfully',
            'server_time' => $now->toIso8601String(),
            'reading_id'  => $reading->id,
            'data'        => [
                'temperature' => $reading->temperature,
                'humidity'    => $reading->humidity,
            ]
        ]);
    }

    /**
     * Fetch Server Room Monitoring Data for RSBA Office Backoffice.
     * GET /api/v1/iot/server-room
     */
    public function getServerRoomStatus(Request $request): JsonResponse
    {
        // Find device by serial number or latest active device
        $device = IotDevice::where('serial_number', env('IOT_DEVICE_SERIAL', 'ESP32-SERVER-ROOM-001'))
            ->orWhere('serial_number', 'ESP32-SERVER-001')
            ->orWhere('status', 'ONLINE')
            ->orderBy('last_seen_at', 'desc')
            ->first()
            ?? IotDevice::first();

        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Belum ada perangkat IoT Ruang Server yang terdaftar.',
                'device' => null,
                'latest_reading' => null,
                'readings' => [],
            ]);
        }

        // Evaluate 10-minute heartbeat threshold
        $isOnline = $device->isOnline();
        $deviceStatus = $isOnline ? 'ONLINE' : 'OFFLINE';

        if ($device->status !== $deviceStatus) {
            $device->update(['status' => $deviceStatus]);
        }

        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');
        $limit     = (int) $request->query('limit', 100);

        $query = IotSensorReading::where('device_id', $device->id)
            ->orderBy('recorded_at', 'desc');

        if ($startDate) {
            $query->where('recorded_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('recorded_at', '<=', $endDate);
        }

        $readings = $query->take($limit)->get();
        $latestReading = $readings->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'device' => [
                    'id'               => $device->id,
                    'serial_number'    => $device->serial_number,
                    'name'             => $device->name,
                    'location'         => $device->location,
                    'mac_address'      => $device->mac_address,
                    'ip_address'       => $device->ip_address,
                    'firmware_version' => $device->firmware_version,
                    'status'           => $deviceStatus,
                    'is_online'        => $isOnline,
                    'api_token'        => $device->api_token,
                    'last_seen_at'     => $device->last_seen_at?->toIso8601String(),
                ],
                'latest_reading' => $latestReading ? [
                    'temperature'   => $latestReading->temperature,
                    'humidity'      => $latestReading->humidity,
                    'rssi'          => $latestReading->rssi,
                    'latency_ms'    => $latestReading->latency_ms,
                    'uptime_seconds' => $latestReading->uptime_seconds,
                    'recorded_at'   => $latestReading->recorded_at->toIso8601String(),
                ] : null,
                'readings' => $readings->map(fn($r) => [
                    'id'            => $r->id,
                    'temperature'   => $r->temperature,
                    'humidity'      => $r->humidity,
                    'rssi'          => $r->rssi,
                    'latency_ms'    => $r->latency_ms,
                    'recorded_at'   => $r->recorded_at->toIso8601String(),
                ])->toArray(),
            ]
        ]);
    }

    /**
     * Seed or return default ESP32 Device credentials for testing & deployment.
     * POST /api/v1/iot/devices/seed-default
     */
    public function seedDefaultDevice(): JsonResponse
    {
        $device = IotDevice::firstOrCreate(
            ['serial_number' => env('IOT_DEVICE_SERIAL', 'ESP32-SERVER-ROOM-001')],
            [
                'name'             => 'ESP32 Ruang Server Utama',
                'location'         => 'Ruang Server Utama RSBA',
                'mac_address'      => '48:9D:31:04:D6:E8',
                'ip_address'       => '192.168.137.8',
                'firmware_version' => '1.0.0',
                'status'           => 'OFFLINE',
                'api_token'        => env('IOT_DEVICE_API_TOKEN', 'rsba_iot_server_room_secret_token_2026'),
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Default ESP32 Server Room Device initialized',
            'device' => $device,
        ]);
    }
}
