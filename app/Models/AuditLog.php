<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'audit_logs';
    protected $keyType = 'string';
    public $incrementing = false;

    // Only created_at, no updated_at
    public $timestamps = false;

    protected $fillable = [
        'user_dms_id',
        'module',
        'operation',
        'entity_type',
        'entity_id',
        'before_data',
        'after_data',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'before_data' => 'array',
            'after_data' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserDms::class, 'user_dms_id');
    }
}
