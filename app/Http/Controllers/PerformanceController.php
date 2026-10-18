<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Performance;
use App\Models\PerformanceHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PerformanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        abort_unless($user && $user->role, 403);

        /* Role based visibility */
        $allowedRoles = match ($user->role->name) {
            'super_admin' => ['manager', 'employee'],
            'manager' => ['employee'],
            default => abort(403, 'Performance access denied'),
        };

        $visibleUserIds = User::where('is_active', true)
            ->whereHas('role', fn($q) => $q->whereIn('name', $allowedRoles))
            ->select('id');

        /* Team Performance + Ranking (snapshot, read-only) */
        $performance = Performance::with('user.role')
            ->whereIn('user_id', $visibleUserIds)
            ->orderByDesc('total_score')
            ->orderBy('user_id')
            ->get();

        $ranking = $performance;

        $averageScore = $performance->isNotEmpty()
            ? (int) round($performance->avg('total_score'))
            : 0;

        $lastUpdated = $performance->max('updated_at');

        /* Dashboard Cards */
        $totalLeads = Lead::whereIn('assigned_to_user_id', $visibleUserIds)->count();

        $totalCustomers = Customer::whereIn('assigned_to_user_id', $visibleUserIds)->count();

        $totalActivities = Activity::whereIn('assigned_to_user_id', $visibleUserIds)->count();

        $totalPendingFollowUps = FollowUp::whereIn('assigned_to_user_id', $visibleUserIds)
            ->where('status', 'scheduled')
            ->count();

        $upcomingFollowUps = FollowUp::whereIn('assigned_to_user_id', $visibleUserIds)
            ->whereDate('date', '>=', today())
            ->orderBy('date')
            ->limit(5)
            ->get();

        $conversionRate = $totalLeads > 0
            ? (int) round(($totalCustomers / $totalLeads) * 100)
            : 0;

        /* Monthly Leads (current year) */
        $monthExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%m', created_at) AS INTEGER)"
            : 'MONTH(created_at)';

        $yearStart = now()->startOfYear();
        $nextYearStart = $yearStart->copy()->addYear();

        $monthlyLeads = Lead::whereIn('assigned_to_user_id', $visibleUserIds)
            ->where('created_at', '>=', $yearStart)
            ->where('created_at', '<', $nextYearStart)
            ->selectRaw($monthExpression.' as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->all();

        /* Lead Status (single query) */
        $statusCounts = Lead::whereIn('assigned_to_user_id', $visibleUserIds)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $leadStatus = [
            'New' => $statusCounts['New'] ?? 0,
            'Proposal' => $statusCounts['Proposal'] ?? 0,
            'Won' => $statusCounts['Won'] ?? 0,
            'Lost' => $statusCounts['Lost'] ?? 0,
        ];

        /* History (ek hi query, trend aur meter dono ke liye) */
        $historyByUser = PerformanceHistory::with('user')
            ->whereIn('user_id', $visibleUserIds)
            ->where('snapshot_date', '>=', today()->subDays(89)->toDateString())
            ->orderBy('snapshot_date')
            ->get()
            ->groupBy('user_id');

        /* Performance Trend Data */
        $performanceTrend = $historyByUser
            ->map(function ($history) {
                return [
                    'name' => optional($history->first()->user)->name ?? 'Unknown',
                    'dates' => $history->pluck('snapshot_date')
                        ->map(fn($date) => $date->format('d M'))
                        ->values(),
                    'scores' => $history->pluck('total_score')->values(),
                ];
            })
            ->values();

        /* Smart Meter: previous snapshot se difference */
        $meter = $performance->mapWithKeys(function ($row) use ($historyByUser) {
            $snaps = $historyByUser->get($row->user_id, collect())->values();
            $count = $snaps->count();

            $current = (int) $row->total_score;
            $previous = $count > 1 ? (int) $snaps[$count - 2]->total_score : null;
            $diff = $previous === null ? null : $current - $previous;

            $status = match (true) {
                $diff === null => 'New',
                $diff > 0 => 'Improving',
                $diff < 0 => 'Declining',
                default => 'Stable',
            };

            return [$row->user_id => ['current' => $current, 'diff' => $diff, 'status' => $status]];
        });

        return view('performance.index', compact(
            'performance',
            'ranking',
            'averageScore',
            'lastUpdated',
            'totalLeads',
            'totalCustomers',
            'totalActivities',
            'totalPendingFollowUps',
            'upcomingFollowUps',
            'conversionRate',
            'monthlyLeads',
            'leadStatus',
            'performanceTrend',
            'meter'
        ));
    }
}
