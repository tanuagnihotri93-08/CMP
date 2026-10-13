<?php

namespace App\Http\Middleware;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();
        $routeName = (string) $request->route()?->getName();
        $action = $this->permissionAction($routeName, $request->method());
        $permission = match ($module) {
            'dashboard' => 'dashboard.view',
            'audit_logs' => 'audit_logs.view',
            'reports' => 'reports.view',
            'users' => 'users.manage',
            'roles' => 'roles.manage',
            default => str_contains($module, '.') ? $module : "{$module}.{$action}",
        };
        abort_unless($user?->hasPermission($permission), 403);

        $recordParameter = match ($module) {
            'leads' => 'lead',
            'customers' => 'customer',
            'follow_ups' => 'followup',
            'activities' => 'activity',
            default => null,
        };

        $recordId = $recordParameter ? $request->route($recordParameter) : null;
        if ($module === 'follow_ups' && ! $recordId) {
            $recordId = $request->route('followUp');
        }
        if ($recordId && is_scalar($recordId)) {
            $model = $this->model($module);
            abort_unless($user->canAccessRecord($model::findOrFail($recordId)), 404);
        }

        return $next($request);
    }

    private function permissionAction(string $routeName, string $method): string
    {
        $suffix = str($routeName)->afterLast('.')->toString();

        return match ($suffix) {
            'create', 'store' => 'create',
            'edit', 'update' => 'update',
            'destroy' => 'delete',
            'complete' => 'complete',
            default => $method === 'GET' ? 'view' : 'update',
        };
    }

    private function model(string $module): string
    {
        return match ($module) {
            'leads' => Lead::class,
            'customers' => Customer::class,
            'follow_ups' => FollowUp::class,
            'activities' => Activity::class,
        };
    }
}
