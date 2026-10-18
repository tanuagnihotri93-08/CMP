<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    private const MODULES = ['leads', 'customers', 'follow_ups', 'activities', 'authentication', 'users', 'roles'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'module' => ['nullable', Rule::in(self::MODULES)],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);

        $logs = AuditLog::query()
            ->with('user')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('description', 'like', "%{$search}%")
                        ->orWhere('user_name', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhere('record_type', 'like', "%{$search}%");
                });
            })
            ->when($filters['module'] ?? null, fn ($query, string $module) => $query->where('module', $module))
            ->when($filters['user_id'] ?? null, fn ($query, int $userId) => $query->where('user_id', $userId))
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->where('created_at', '>=', $date.' 00:00:00'))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->where('created_at', '<', Carbon::parse($date)->addDay()->toDateString()))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $users = User::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->orderBy('id')
            ->lazy(500);

        return view('audit-logs.index', compact('logs', 'users', 'filters'));
    }
}
