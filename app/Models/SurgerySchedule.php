<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurgerySchedule extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'surgery_schedules';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'bpjs_schedule_id',
        'operating_room_id',
        'patient_name',
        'scheduled_start_at',
        'actual_start_at',
        'status', // menunggu | sedang_dilaksanakan | selesai
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_start_at' => 'datetime',
            'actual_start_at' => 'datetime',
            'synced_at' => 'datetime',
        ];
    }

    public function operatingRoom(): BelongsTo
    {
        return $this->belongsTo(OperatingRoom::class, 'operating_room_id');
    }
}
