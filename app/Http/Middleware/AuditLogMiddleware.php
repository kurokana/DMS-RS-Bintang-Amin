<?php

namespace App\Http\Middleware;

use App\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Skip GET requests
        if ($request->isMethod('GET')) {
            return $next($request);
        }

        // 2. Identify the target entity before changes (for PUT/PATCH/DELETE)
        $beforeData = null;
        $entityId = 'system';
        $entityType = 'system';
        $module = $this->deduceModule($request);

        $route = $request->route();
        $parameters = $route ? $route->parameters() : [];
        
        // Find the first parameter (like 'id' or model instance)
        if (!empty($parameters)) {
            $paramValue = reset($parameters);
            
            if ($paramValue instanceof \Illuminate\Database\Eloquent\Model) {
                $beforeData = $paramValue->toArray();
                $entityId = $paramValue->getKey();
                $entityType = get_class($paramValue);
            } elseif (is_string($paramValue) || is_numeric($paramValue)) {
                $entityId = (string) $paramValue;
                // Try to guess the model and fetch before data
                $beforeData = $this->fetchEntityData($module, $entityId);
                $entityType = $this->guessEntityType($module);
            }
        }

        // 3. Process request
        $response = $next($request);

        // 4. Capture after data if request succeeded
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $afterData = null;

            // If it's a create request, try to get the ID from the response JSON
            if ($request->isMethod('POST')) {
                $content = json_decode($response->getContent(), true);
                if (isset($content['data']['id'])) {
                    $entityId = $content['data']['id'];
                    $afterData = $content['data'];
                    $entityType = $this->guessEntityType($module);
                } else {
                    $afterData = $request->except(['password', 'password_confirmation']);
                }
            } else {
                // For updates, fetch the fresh data from the database
                if ($entityId !== 'system') {
                    $afterData = $this->fetchEntityData($module, $entityId);
                }
            }

            // Log to database
            $this->auditLogService->log(
                $module,
                strtolower($request->method()),
                $entityType,
                $entityId,
                $beforeData,
                $afterData
            );
        }

        return $response;
    }

    /**
     * Deduce module name from request path.
     */
    protected function deduceModule(Request $request): string
    {
        if ($request->is('*/mapping*')) {
            return 'mapping';
        }
        if ($request->is('*/displays*')) {
            return 'display';
        }
        if ($request->is('*/auth*')) {
            return 'auth';
        }
        return 'general';
    }

    /**
     * Guess Entity Class name based on module.
     */
    protected function guessEntityType(string $module): string
    {
        switch ($module) {
            case 'display':
                return \App\Models\DisplayDevice::class;
            case 'mapping':
                return \App\Models\DisplayMapping::class;
            default:
                return 'system';
        }
    }

    /**
     * Fetch fresh entity data for logging.
     */
    protected function fetchEntityData(string $module, string $id): ?array
    {
        try {
            switch ($module) {
                case 'display':
                    $model = \App\Models\DisplayDevice::find($id);
                    return $model ? $model->toArray() : null;
                case 'mapping':
                    $model = \App\Models\DisplayMapping::find($id);
                    return $model ? $model->toArray() : null;
                default:
                    return null;
            }
        } catch (\Exception $e) {
            return null;
        }
    }
}
