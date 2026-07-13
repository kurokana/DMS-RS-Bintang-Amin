<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'sync_logs';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'source',
        'status',
        'error_message',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'synced_at' => 'datetime',
        ];
    }
}
