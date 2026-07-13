<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;
use App\Jobs\PollBpjsWardJob;
use App\Jobs\PollBpjsOperatingRoomJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use App\Jobs\CheckDisplayHeartbeatJob;

// Polling BPJS every 10 seconds
Schedule::job(PollBpjsWardJob::class)->everyTenSeconds();
Schedule::job(PollBpjsOperatingRoomJob::class)->everyTenSeconds();

// Check display heartbeat every minute
Schedule::job(CheckDisplayHeartbeatJob::class)->everyMinute();

