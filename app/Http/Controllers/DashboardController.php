<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = request()->user();
        $leads = $user->constrainToAssignedRecords(Lead::query());
        $customers = $user->constrainToAssignedRecords(Customer::query());
        $followUps = $user->constrainToAssignedRecords(FollowUp::query());
        $activities = $user->constrainToAssignedRecords(Activity::query());

        $totalLeads = (clone $leads)->count();
        $wonLeads = (clone $leads)->where('status', 'won')->count();
        $totalCustomers = (clone $customers)->count();
        $totalFollowUps = (clone $followUps)->count();
        $pendingFollowUps = (clone $followUps)->where('status', 'scheduled')->count();
        $totalActivities = (clone $activities)->count();
        $completedActivities = (clone $activities)->where('status', 'completed')->count();
        $completedFollowUps = (clone $followUps)->where('status', 'completed')->count();
        $resolvedFollowUps = $completedFollowUps + $pendingFollowUps;
        $monthStart = now()->startOfMonth();
        $monthlyLeadGrowth = collect(range(5, 0))->mapWithKeys(function (int $monthsAgo) use ($leads): array {
            $month = now()->startOfMonth()->subMonths($monthsAgo);

            return [
                $month->format('M Y') => (clone $leads)
                    ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                    ->count(),
            ];
        });
        $recentBusinessActivity = (clone $activities)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->count()
            + (clone $followUps)->where('created_at', '>=', Carbon::now()->subDays(30))->count()
            + (clone $leads)->where('created_at', '>=', Carbon::now()->subDays(30))->count()
            + (clone $customers)->where('created_at', '>=', Carbon::now()->subDays(30))->count();

        return view('dashboard.index', [
            'totalLeads' => $totalLeads,
            'wonLeads' => $wonLeads,
            'totalCustomers' => $totalCustomers,
            'totalFollowUps' => $totalFollowUps,
            'pendingFollowUps' => $pendingFollowUps,
            'totalActivities' => $totalActivities,
            'completedActivities' => $completedActivities,
            'conversionRate' => $totalLeads > 0
                ? round(($wonLeads / $totalLeads) * 100)
                : 0,
            'newLeads' => (clone $leads)->where('status', 'new')->count(),
            'proposalLeads' => (clone $leads)->where('status', 'proposal')->count(),
            'convertedLeads' => $wonLeads,
            'monthlyLeadGrowth' => $monthlyLeadGrowth,
            'followUpCompletionRate' => $resolvedFollowUps > 0
                ? round(($completedFollowUps / $resolvedFollowUps) * 100)
                : 0,
            'recentBusinessActivity' => $recentBusinessActivity,
            'leadsAddedThisMonth' => (clone $leads)->where('created_at', '>=', $monthStart)->count(),
            'recentLeads' => (clone $leads)->latest()->take(5)->get(),
            'recentCustomers' => (clone $customers)->latest()->take(5)->get(),
            'upcomingFollowUps' => (clone $followUps)
                ->with('lead:id,name')
                ->where('status', 'scheduled')
                ->where('date', '>', now())
                ->orderBy('date')
                ->take(5)
                ->get(),
            'recentActivities' => (clone $activities)->latest()->take(5)->get(),
        ]);
    }
}
