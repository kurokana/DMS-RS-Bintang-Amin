<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PolyclinicDoctor extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'polyclinic_doctors';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'polyclinic_id',
        'name',
        'photo_path',
        'specialty',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function polyclinic(): BelongsTo
    {
        return $this->belongsTo(Polyclinic::class, 'polyclinic_id');
    }

    public function queues(): HasMany
    {
        return $this->hasMany(PolyclinicQueue::class, 'doctor_id');
    }

    /**
     * Antrian hari ini saja, diurutkan berdasarkan queue_number.
     */
    public function todayQueue(): HasMany
    {
        return $this->hasMany(PolyclinicQueue::class, 'doctor_id')
            ->where('queue_date', today())
            ->orderByRaw("CASE WHEN status = 'terlewat' THEN 1 ELSE 0 END")
            ->orderBy('queue_number');
    }

    /**
     * Full URL foto dokter.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path
            ? asset('storage/' . $this->photo_path)
            : null;
    }
}
