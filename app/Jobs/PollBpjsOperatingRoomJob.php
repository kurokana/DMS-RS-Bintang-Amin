<?php

namespace App\Jobs;

use App\Services\BpjsOperatingRoomSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PollBpjsOperatingRoomJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public array $backoff = [2, 4, 8];

    /**
     * Execute the job.
     */
    public function handle(BpjsOperatingRoomSyncService $syncService): void
    {
        $syncService->sync();
    }
}
