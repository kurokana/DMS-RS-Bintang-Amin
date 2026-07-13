<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DisplayMapping extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'display_mappings';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'display_device_id',
        'target_type',
        'target_id',
        'effective_at',
    ];

    protected function casts(): array
    {
        return [
            'effective_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(DisplayDevice::class, 'display_device_id');
    }

    /**
     * Polymorphic target (WardClass or OperatingRoom)
     */
    public function target(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'target_type', 'target_id');
    }
}
