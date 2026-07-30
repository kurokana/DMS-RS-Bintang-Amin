<?php

namespace App\Services\Simrs;

use App\Contracts\SimrsQueueServiceInterface;
use App\DTOs\SimrsQueueData;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HttpSimrsDriver implements SimrsQueueServiceInterface
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;
    protected int $retryAttempts;
    protected string $apiVersion;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.simrs.base_url', 'http://127.0.0.1:9000'), '/');
        $this->apiKey = (string) config('services.simrs.api_key', '');
        $this->timeout = (int) config('services.simrs.timeout', 5);
        $this->retryAttempts = (int) config('services.simrs.retry', 2);
        $this->apiVersion = (string) config('services.simrs.version', 'v1');
    }

    protected function client()
    {
        $request = Http::timeout($this->timeout);

        if ($this->retryAttempts > 0) {
            $request = $request->retry($this->retryAttempts, 100);
        }

        if ($this->apiKey) {
            $request = $request->withHeaders(['X-SIMRS-API-KEY' => $this->apiKey]);
        }

        return $request->baseUrl("{$this->baseUrl}/api/{$this->apiVersion}");
    }

    public function getTodayQueue(string $polyCode, ?string $doctorCode = null): array
    {
        try {
            $params = ['poly_code' => $polyCode];
            if ($doctorCode) {
                $params['doctor_code'] = $doctorCode;
            }

            Log::channel('simrs')->info("[SIMRS_REQUEST] Fetching queue for poly {$polyCode}", $params);

            $response = $this->client()->get('/queues', $params);

            if ($response->successful()) {
                $items = (array) ($response->json('data') ?? []);
                Log::channel('simrs')->info("[SIMRS_RESPONSE] Success fetching " . count($items) . " queue records.");
                
                return array_map(fn($item) => SimrsQueueData::fromArray($item, isFallback: false)->toArray(), $items);
            }

            Log::channel('simrs')->warning("[SIMRS_HTTP_ERROR] Status {$response->status()}", ['body' => $response->body()]);
            throw new \RuntimeException("SIMRS API Error: " . $response->status());
        } catch (\Exception $e) {
            Log::channel('simrs')->error("[SIMRS_EXCEPTION] " . $e->getMessage());
            throw $e;
        }
    }

    public function updateQueueStatus(string $queueId, string $status): bool
    {
        try {
            Log::channel('simrs')->info("[SIMRS_STATUS_UPDATE] Queue {$queueId} -> {$status}");
            $response = $this->client()->put("/queues/{$queueId}/status", [
                'status' => $status,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::channel('simrs')->error("[SIMRS_UPDATE_FAILED] " . $e->getMessage());
            throw $e;
        }
    }

    public function checkConnection(): bool
    {
        try {
            $response = Http::timeout(2)->get("{$this->baseUrl}/api/{$this->apiVersion}/health");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
