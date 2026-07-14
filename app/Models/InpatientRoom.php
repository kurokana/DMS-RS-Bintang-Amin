<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InpatientRoom extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'inpatient_rooms';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'room_code',
        'name',
        'floor',
        'building',
        'bed_total',
        'bed_occupied',
        'bed_available',
    ];
}
