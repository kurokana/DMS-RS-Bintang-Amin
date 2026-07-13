<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WardAvailability extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'ward_availability';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'ward_class_id',
        'bed_total',
        'bed_occupied',
        'bed_available',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'synced_at' => 'datetime',
        ];
    }

    public function wardClass(): BelongsTo
    {
        return $this->belongsTo(WardClass::class, 'ward_class_id');
    }
}
