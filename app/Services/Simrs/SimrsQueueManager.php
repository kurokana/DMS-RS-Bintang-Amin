<?php

namespace App\Services\Simrs;

use App\Contracts\SimrsQueueServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SimrsQueueManager implements SimrsQueueServiceInterface
{
    protected LocalSimrsDriver $localDriver;
    protected HttpSimrsDriver $httpDriver;
    protected string $defaultDriver;
    protected int $cooldownSeconds;

    public function __construct(LocalSimrsDriver $localDriver, HttpSimrsDriver $httpDriver)
    {
        $this->localDriver = $localDriver;
        $this->httpDriver = $httpDriver;
        $this->defaultDriver = (string) config('services.simrs.driver', 'local');
        $this->cooldownSeconds = (int) config('services.simrs.recovery_cooldown', 30);
    }

    /**
     * Check if HTTP driver is currently in cooldown due to previous failure.
     */
    protected function isHttpInCooldown(): bool
    {
        return Cache::has('simrs_offline_cooldown');
    }

    /**
     * Set cooldown window when HTTP request fails.
     */
    protected function triggerCooldown(): void
    {
        Cache::put('simrs_offline_cooldown', true, $this->cooldownSeconds);
        Log::channel('simrs')->warning("[SIMRS_COOLDOWN_ACTIVATED] Cooldown set for {$this->cooldownSeconds}s. Falling back to LocalSimrsDriver.");
    }

    /**
     * Clear cooldown when connection recovers.
     */
    public function clearCooldown(): void
    {
        if (Cache::has('simrs_offline_cooldown')) {
            Cache::forget('simrs_offline_cooldown');
            Log::channel('simrs')->info("[SIMRS_RECOVERED] SIMRS API back online! Cooldown cleared.");
        }
    }

    public function getTodayQueue(string $polyCode, ?string $doctorCode = null): array
    {
        if ($this->defaultDriver === 'http') {
            if ($this->isHttpInCooldown()) {
                Log::channel('simrs')->info("[SIMRS_COOLDOWN_ACTIVE] Serving queue from LocalSimrsDriver fallback.");
                return $this->localDriver->getTodayQueue($polyCode, $doctorCode);
            }

            try {
                $data = $this->httpDriver->getTodayQueue($polyCode, $doctorCode);
                $this->clearCooldown();
                return $data;
            } catch (\Exception $e) {
                $this->triggerCooldown();
                return $this->localDriver->getTodayQueue($polyCode, $doctorCode);
            }
        }

        return $this->localDriver->getTodayQueue($polyCode, $doctorCode);
    }

    public function updateQueueStatus(string $queueId, string $status): bool
    {
        if ($this->defaultDriver === 'http' && !$this->isHttpInCooldown()) {
            try {
                $result = $this->httpDriver->updateQueueStatus($queueId, $status);
                // Also update local copy for persistence/consistency
                $this->localDriver->updateQueueStatus($queueId, $status);
                return $result;
            } catch (\Exception $e) {
                $this->triggerCooldown();
            }
        }

        return $this->localDriver->updateQueueStatus($queueId, $status);
    }

    public function checkConnection(): bool
    {
        if ($this->defaultDriver === 'http') {
            $healthy = $this->httpDriver->checkConnection();
            if ($healthy) {
                $this->clearCooldown();
            }
            return $healthy;
        }

        return $this->localDriver->checkConnection();
    }
}
