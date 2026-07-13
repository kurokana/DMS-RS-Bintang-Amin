<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class WardClass extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'ward_classes';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'bpjs_class_code',
        'name',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'synced_at' => 'datetime',
        ];
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(WardAvailability::class, 'ward_class_id');
    }

    public function currentAvailability(): HasOne
    {
        return $this->hasOne(WardAvailability::class, 'ward_class_id')->latestOfMany('synced_at');
    }

    public function mappings(): MorphMany
    {
        return $this->morphMany(DisplayMapping::class, 'target', 'target_type', 'target_id');
    }
}
