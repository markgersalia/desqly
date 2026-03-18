<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantWriteAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Filament::getTenant();

        if (! $tenant instanceof Company || $tenant->canWrite()) {
            return $next($request);
        }

        if ($this->isAlwaysAllowed($request)) {
            return $next($request);
        }

        if (! $this->isMutatingRequest($request)) {
            return $next($request);
        }

        $message = 'Subscription inactive. Update billing to make changes.';

        if ($request->expectsJson() || $request->routeIs('livewire.update')) {
            return response()->json(['message' => $message], 403);
        }

        $billingUrl = Filament::getTenantBillingUrl(tenant: $tenant);

        if ($billingUrl) {
            return redirect()->to($billingUrl)->with('status', $message);
        }

        abort(403, $message);
    }

    private function isAlwaysAllowed(Request $request): bool
    {
        if ($request->routeIs('filament.admin.auth.*')) {
            return true;
        }

        if ($request->routeIs('filament.admin.tenant.registration') || $request->routeIs('filament.admin.tenant.profile') || $request->routeIs('filament.admin.tenant.billing')) {
            return true;
        }

        $path = '/' . trim((string) $request->path(), '/');

        return str_ends_with($path, '/billing') || str_ends_with($path, '/profile') || str_ends_with($path, '/new');
    }

    private function isMutatingRequest(Request $request): bool
    {
        if (! $request->routeIs('livewire.update')) {
            return ! in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true);
        }

        $components = $request->input('components', []);

        if (! is_array($components)) {
            return true;
        }

        foreach ($components as $component) {
            $path = (string) data_get($component, 'snapshot.memo.path', '');

            if (str_contains($path, '/billing') || str_contains($path, '/profile')) {
                continue;
            }

            if (str_contains($path, '/create') || str_contains($path, '/edit')) {
                return true;
            }

            $calls = data_get($component, 'calls', []);

            foreach ((array) $calls as $call) {
                $method = (string) data_get($call, 'method', '');

                if (in_array($method, [
                    'create',
                    'save',
                    'delete',
                    'deleteAll',
                    'callMountedAction',
                    'callMountedTableAction',
                    'mountAction',
                    'mountTableAction',
                ], true)) {
                    return true;
                }
            }
        }

        return false;
    }
}
