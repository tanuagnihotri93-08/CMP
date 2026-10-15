<?php
namespace App\Http\Controllers;
use App\Models\Lead;
use App\Models\Customer;
use App\Models\Activity;
use App\Models\FollowUp;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user->role) {
            abort(403);
        }

        // Employee restricted
        if ($user->role->name === 'employee') {
            abort(403, 'Analytics access denied');
        }


        $totalLeads = Lead::count();

        $totalCustomers = Customer::count();

        $totalActivities = Activity::count();

        $pendingFollowUps = FollowUp::where('status', 'pending')
            ->count();


        $conversionRate = $totalLeads > 0
            ? round(($totalCustomers / $totalLeads) * 100, 2)
            : 0;


        $leadStatus = Lead::select(
                'status',
                DB::raw('count(*) as total')
            )
            ->groupBy('status')
            ->pluck('total', 'status');


        $monthlyLeads = Lead::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');


        $teamPerformance = User::whereHas('role', function ($query) {
                $query->where('name', 'employee');
            })
            ->withCount([
                'assignedLeads'
            ])
            ->get();


        return view('analytics.index', compact(
            'totalLeads',
            'totalCustomers',
            'totalActivities',
            'pendingFollowUps',
            'conversionRate',
            'leadStatus',
            'monthlyLeads',
            'teamPerformance'
        ));
    }
}