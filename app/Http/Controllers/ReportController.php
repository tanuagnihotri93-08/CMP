<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    private const PDF_EXPORT_ROW_LIMIT = 1000;

    private const MODULES = ['leads', 'customers', 'follow_ups', 'activities'];

    private const STATUSES = ['new', 'qualified', 'proposal', 'won', 'lost', 'active', 'inactive', 'scheduled', 'pending', 'completed'];

    private const STATUSES_BY_MODULE = [
        'leads' => ['new', 'qualified', 'proposal', 'won', 'lost'],
        'customers' => ['active', 'inactive'],
        'follow_ups' => ['scheduled', 'completed'],
        'activities' => ['pending', 'completed'],
    ];

    public function index(Request $request): View
    {
        $filters = $this->validateFilters($request);
        $queries = $this->queries($request, $filters);
        $visibleModules = ($filters['module'] ?? null)
            ? [$filters['module']]
            : (($filters['status'] ?? null)
                ? array_values(array_filter(self::MODULES, fn ($module) => in_array($filters['status'], self::STATUSES_BY_MODULE[$module], true)))
                : self::MODULES);
        $reports = array_fill_keys(self::MODULES, null);

        foreach ($visibleModules as $module) {
            $reports[$module] = match ($module) {
                'leads' => $this->leadReport($queries['leads']),
                'customers' => $this->customerReport($queries['customers']),
                'follow_ups' => $this->followUpReport($queries['follow_ups']),
                'activities' => $this->activityReport($queries['activities']),
            };
        }

        return view('reports.index', [
            'filters' => $filters,
            'users' => User::where('is_active', true)
                ->select(['id', 'name'])
                ->orderBy('name')
                ->orderBy('id')
                ->lazy(500),
            'moduleOptions' => self::MODULES,
            'moduleStatuses' => self::STATUSES_BY_MODULE,
            'visibleModules' => $visibleModules,
            'leadReport' => $reports['leads'],
            'customerReport' => $reports['customers'],
            'followUpReport' => $reports['follow_ups'],
            'activityReport' => $reports['activities'],
        ]);
    }

    public function export(Request $request, string $module, string $format): Response
    {
        abort_unless(in_array($module, self::MODULES, true), 404);
        abort_unless(in_array($format, ['excel', 'pdf'], true), 404);
        abort_unless($request->user()->hasPermission('reports.export'), 403);

        $filters = $this->validateFilters($request, $module);
        $query = $this->queries($request, $filters)[$module];
        [$title, $headings] = $this->exportDefinition($module);
        $rowCount = (clone $query)->count();

        if ($format === 'pdf' && $rowCount > self::PDF_EXPORT_ROW_LIMIT) {
            return back()->withErrors([
                'export' => 'PDF exports are limited to '.number_format(self::PDF_EXPORT_ROW_LIMIT).' rows. Apply filters or use Excel for larger exports.',
            ]);
        }

        $generatedAt = now();
        $generatedBy = $request->user()->name;

        AuditLog::record(
            'exported',
            'reports',
            'Report',
            null,
            "{$generatedBy} exported ".ucfirst(str_replace('_', ' ', $module)).' Report '.strtoupper($format).'.',
            null,
            [
                'module' => $module,
                'format' => $format,
                'filters' => $filters,
                'row_count' => $rowCount,
            ]
        );

        $filename = $module.'-report-'.$generatedAt->format('Ymd-His');
        if ($format === 'excel') {
            return Excel::download(new ReportExport($module, $headings, $query), "{$filename}.xlsx");
        }

        $rows = (new ReportExport($module, $headings, $query))->rows();
        $pdf = Pdf::loadView('reports.pdf', compact(
            'title',
            'headings',
            'rows',
            'generatedAt',
            'generatedBy',
            'filters'
        ))->setPaper('a4', 'landscape');

        return $pdf->download("{$filename}.pdf");
    }

    private function validateFilters(Request $request, ?string $exportModule = null): array
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'module' => ['nullable', Rule::in(self::MODULES)],
        ]);

        if ($exportModule !== null) {
            $filters['module'] = $exportModule;
        }

        if (($filters['module'] ?? null) && ($filters['status'] ?? null)) {
            $request->validate([
                'status' => ['nullable', Rule::in(self::STATUSES_BY_MODULE[$filters['module']])],
            ]);
        }

        return $filters;
    }

    private function queries(Request $request, array $filters): array
    {
        $user = $request->user();

        return [
            'leads' => $this->applyFilters(
                $user->constrainToAssignedRecords(Lead::query()),
                'created_at',
                $filters,
                ['new', 'qualified', 'proposal', 'won', 'lost']
            ),
            'customers' => $this->applyFilters(
                $user->constrainToAssignedRecords(Customer::query()),
                'created_at',
                $filters,
                ['active', 'inactive']
            ),
            'follow_ups' => $this->applyFilters(
                $user->constrainToAssignedRecords(FollowUp::query()),
                'date',
                $filters,
                ['scheduled', 'completed']
            ),
            'activities' => $this->applyFilters(
                $user->constrainToAssignedRecords(Activity::query()),
                'activity_date',
                $filters,
                ['pending', 'completed']
            ),
        ];
    }

    private function applyFilters(Builder $query, string $dateColumn, array $filters, array $validStatuses): Builder
    {
        return $query
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->where($dateColumn, '>=', $date.' 00:00:00'))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->where($dateColumn, '<', Carbon::parse($date)->addDay()->toDateString()))
            ->when($filters['user_id'] ?? null, fn (Builder $query, int $userId) => $query->where('assigned_to_user_id', $userId))
            ->when(
                isset($filters['status']) && in_array($filters['status'], $validStatuses, true),
                fn (Builder $query) => $query->where('status', $filters['status'])
            );
    }

    private function leadReport(Builder $query): array
    {
        $total = (clone $query)->count();
        $byStatus = (clone $query)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');
        $bySource = (clone $query)
            ->selectRaw("COALESCE(NULLIF(source, ''), 'Unspecified') as source_name, COUNT(*) as aggregate")
            ->groupBy('source_name')
            ->pluck('aggregate', 'source_name');
        $won = $byStatus->get('won', 0);

        return [
            'total' => $total,
            'by_status' => $byStatus,
            'new_in_range' => $byStatus->get('new', 0),
            'converted' => $won,
            'lost' => $byStatus->get('lost', 0),
            'conversion_rate' => $total ? round($won / $total * 100, 1) : 0,
            'by_source' => $bySource,
        ];
    }

    private function customerReport(Builder $query): array
    {
        $growth = (clone $query)
            ->selectRaw("COALESCE(SUBSTR(created_at, 1, 7), 'Unknown') as month, COUNT(*) as aggregate")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('aggregate', 'month');
        $total = (clone $query)->count();

        return [
            'total' => $total,
            'new_in_range' => $total,
            'active' => (clone $query)->where('status', 'active')->count(),
            'converted_from_lead' => (clone $query)->whereNotNull('lead_id')->count(),
            'growth' => $growth,
        ];
    }

    private function followUpReport(Builder $query): array
    {
        $byUser = (clone $query)
            ->leftJoin('users', 'follow_ups.assigned_to_user_id', '=', 'users.id')
            ->selectRaw("COALESCE(users.name, 'Unassigned') as assigned_name, COUNT(*) as total, SUM(CASE WHEN follow_ups.status = 'completed' THEN 1 ELSE 0 END) as completed")
            ->groupByRaw("COALESCE(users.name, 'Unassigned')")
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->assigned_name => [
                    'total' => (int) $row->total,
                    'completed' => (int) $row->completed,
                ],
            ]);

        return [
            'total' => (clone $query)->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'pending' => (clone $query)->where('status', 'scheduled')->count(),
            'overdue' => (clone $query)->where('status', 'scheduled')->where('date', '<', now())->count(),
            'by_user' => $byUser,
        ];
    }

    private function activityReport(Builder $query): array
    {
        $byType = (clone $query)
            ->selectRaw('type, COUNT(*) as aggregate')
            ->groupBy('type')
            ->pluck('aggregate', 'type');

        return [
            'total' => (clone $query)->count(),
            'by_type' => $byType,
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
        ];
    }

    private function exportDefinition(string $module): array
    {
        return match ($module) {
            'leads' => [
                'Lead Report',
                ['Lead ID', 'Name', 'Company', 'Email', 'Phone', 'Source', 'Status', 'Assigned User', 'Created At'],
            ],
            'customers' => [
                'Customer Report',
                ['Customer ID', 'Name', 'Company', 'Email', 'Phone', 'Status', 'Converted Lead ID', 'Assigned User', 'Created At'],
            ],
            'follow_ups' => [
                'Follow Up Report',
                ['Follow Up ID', 'Lead ID', 'Type', 'Date', 'Status', 'Remark', 'Assigned User'],
            ],
            'activities' => [
                'Activity Report',
                ['Activity ID', 'Lead ID', 'Customer ID', 'Type', 'Subject', 'Date', 'Status', 'Assigned User'],
            ],
        };
    }
}
