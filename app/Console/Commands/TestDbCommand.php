<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:test-db-command')]
#[Description('Command description')]
class TestDbCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = app(\App\Http\Controllers\DisplayService\DisplayServiceController::class);
        $response = $controller->state('DSP001');
        $this->info($response->content());
    }
}
