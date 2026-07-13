<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditLogService
{
    /**
     * Create a new audit log record.
     */
    public function log(
        string $module,
        string $operation,
        string $entityType,
        string $entityId,
        ?array $before = null,
        ?array $after = null
    ): AuditLog {
        return AuditLog::create([
            'user_dms_id' => auth()->id(),
            'module' => $module,
            'operation' => $operation,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'before_data' => $before,
            'after_data' => $after,
            'ip_address' => request()->ip(),
        ]);
    }
}
