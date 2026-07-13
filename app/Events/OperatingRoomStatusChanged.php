<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OperatingRoomStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $displayId;
    public array $schedules;

    /**
     * Create a new event instance.
     */
    public function __construct(string $displayId, array $schedules)
    {
        $this->displayId = $displayId;
        $this->schedules = $schedules;
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
        return 'OperatingRoomStatusChanged';
    }
}
