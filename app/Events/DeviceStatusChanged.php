<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeviceStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $displayId;
    public string $name;
    public string $status;
    public ?string $lastHeartbeatAt;

    /**
     * Create a new event instance.
     */
    public function __construct(string $displayId, string $name, string $status, ?string $lastHeartbeatAt = null)
    {
        $this->displayId       = $displayId;
        $this->name            = $name;
        $this->status          = $status;
        $this->lastHeartbeatAt = $lastHeartbeatAt;
    }

    /**
     * Broadcast on the public 'displays' channel so Office admin can listen.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('displays'),
        ];
    }

    /**
     * Event name for broadcasting.
     */
    public function broadcastAs(): string
    {
        return 'DeviceStatusChanged';
    }
}
