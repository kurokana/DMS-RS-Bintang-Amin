<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Polyclinic extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'polyclinics';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
    ];

    public function doctors(): HasMany
    {
        return $this->hasMany(PolyclinicDoctor::class, 'polyclinic_id');
    }

    /**
     * Dokter aktif, diurutkan berdasarkan sort_order.
     */
    public function activeDoctors(): HasMany
    {
        return $this->hasMany(PolyclinicDoctor::class, 'polyclinic_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function queues(): HasMany
    {
        return $this->hasMany(PolyclinicQueue::class, 'polyclinic_id');
    }
}
