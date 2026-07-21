<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DisplayDevice extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'display_devices';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'display_id',
        'name',
        'ip_address',
        'status',
        'last_heartbeat_at',
    ];

    protected function casts(): array
    {
        return [
            'last_heartbeat_at' => 'datetime',
        ];
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(DisplayMapping::class, 'display_device_id');
    }

    public function isOnline(): bool
    {
        return $this->status === 'online';
    }
}
