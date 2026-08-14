<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IotSensorReading extends Model
{
    use HasFactory;

    protected $table = 'iot_sensor_readings';
    public $timestamps = false; // Custom created_at & recorded_at

    protected $fillable = [
        'device_id',
        'temperature',
        'humidity',
        'rssi',
        'latency_ms',
        'uptime_seconds',
        'recorded_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'temperature' => 'float',
            'humidity'    => 'float',
            'rssi'        => 'integer',
            'latency_ms'  => 'integer',
            'uptime_seconds' => 'integer',
            'recorded_at' => 'datetime',
            'created_at'  => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(IotDevice::class, 'device_id');
    }
}
