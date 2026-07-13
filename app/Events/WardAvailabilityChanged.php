<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WardAvailabilityChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $displayId;
    public array $availability;

    /**
     * Create a new event instance.
     */
    public function __construct(string $displayId, array $availability)
    {
        $this->displayId = $displayId;
        $this->availability = $availability;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('display.' . $this->displayId),
        ];
    }

    /**
     * Event name for broadcasting.
     */
    public function broadcastAs(): string
    {
        return 'WardAvailabilityChanged';
    }
}
