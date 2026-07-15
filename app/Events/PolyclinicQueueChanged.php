<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PolyclinicQueueChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $polyclinicId;

    /**
     * Create a new event instance.
     */
    public function __construct(string $polyclinicId)
    {
        $this->polyclinicId = $polyclinicId;
    }

    /**
     * Get the channels the event should broadcast on.
     * Uses global 'displays' channel so all display devices can receive it.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('displays'),
        ];
    }

    /**
     * Event name for broadcasting.
     * Frontend must listen with dot prefix: .listen('.PolyclinicQueueChanged', ...)
     */
    public function broadcastAs(): string
    {
        return 'PolyclinicQueueChanged';
    }
}
