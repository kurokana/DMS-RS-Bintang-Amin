<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolyclinicQueue extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'polyclinic_queue';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'polyclinic_id',
        'doctor_id',
        'queue_number',
        'patient_name',
        'status',
        'queue_date',
        'called_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'queue_date' => 'date',
            'called_at' => 'datetime',
            'completed_at' => 'datetime',
            'queue_number' => 'integer',
        ];
    }

    public function polyclinic(): BelongsTo
    {
        return $this->belongsTo(Polyclinic::class, 'polyclinic_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(PolyclinicDoctor::class, 'doctor_id');
    }

    /**
     * Scope: filter antrian hari ini saja.
     */
    public function scopeToday($query)
    {
        return $query->where('queue_date', today());
    }

    /**
     * Scope: filter per dokter.
     */
    public function scopeByDoctor($query, string $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }
}
