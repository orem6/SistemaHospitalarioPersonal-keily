<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $request->header('X-Tenant-ID');

        if ($tenantId === null || $tenantId === '') {
            return response()->json([
                'message' => 'La cabecera X-Tenant-ID es obligatoria.',
            ], 400);
        }

        /** @var Tenant|null $tenant */
        $tenant = Tenant::query()->find($tenantId);

        if ($tenant === null) {
            return response()->json([
                'message' => 'Tenant no encontrado.',
            ], 404);
        }

        $request->attributes->set('tenant', $tenant);
        app()->instance('currentTenant', $tenant);

        return $next($request);
    }
}
