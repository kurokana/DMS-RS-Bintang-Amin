<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IotDevice extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'iot_devices';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'serial_number',
        'name',
        'location',
        'mac_address',
        'ip_address',
        'firmware_version',
        'status',
        'api_token',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
        ];
    }

    public function readings(): HasMany
    {
        return $this->hasMany(IotSensorReading::class, 'device_id');
    }

    /**
     * Evaluates status dynamically based on 10-minute threshold.
     */
    public function isOnline(): bool
    {
        if (!$this->last_seen_at) {
            return false;
        }

        return $this->last_seen_at->greaterThanOrEqualTo(now()->subMinutes(10));
    }

    public function getEvaluatedStatusAttribute(): string
    {
        return $this->isOnline() ? 'ONLINE' : 'OFFLINE';
    }
}
