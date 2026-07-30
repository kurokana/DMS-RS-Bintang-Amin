<?php

namespace App\Contracts;

interface SimrsQueueServiceInterface
{
    /**
     * Fetch patient queue for a polyclinic (and optionally doctor).
     * Returns array of SimrsQueueData DTO objects or structured arrays.
     */
    public function getTodayQueue(string $polyCode, ?string $doctorCode = null): array;

    /**
     * Update queue item status.
     */
    public function updateQueueStatus(string $queueId, string $status): bool;

    /**
     * Check whether SIMRS integration is healthy and responding.
     */
    public function checkConnection(): bool;
}
