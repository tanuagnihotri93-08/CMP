<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use App\Models\FollowUp;
use Illuminate\Support\Facades\DB;

class PerformanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user->role) {
            abort(403);
        }


        if ($user->role->name === 'employee') {
            abort(403, 'Performance access denied');
        }


        /*
        |--------------------------------------------------------------------------
        | Visible Users
        |--------------------------------------------------------------------------
        */


        if ($user->role->name === 'super_admin') {


            $users = User::where('is_active', true)
                ->whereHas('role', function ($q) {

                    $q->whereIn('name', [
                        'manager',
                        'employee'
                    ]);

                })
                ->with('role')
                ->get();


        } elseif ($user->role->name === 'manager') {


            $users = User::where('is_active', true)
                ->whereHas('role', function ($q) {

                    $q->where('name', 'employee');

                })
                ->with('role')
                ->get();


        } else {

            abort(403);

        }



        $visibleUserIds = $users->pluck('id');



        /*
        |--------------------------------------------------------------------------
        | Team Performance
        |--------------------------------------------------------------------------
        */


        $performance = $users->map(function ($employee) {


            $leads = Lead::where(
                'assigned_to_user_id',
                $employee->id
            )->count();


            $customers = Customer::where(
                'assigned_to_user_id',
                $employee->id
            )->count();



            $activities = Activity::where(
                'assigned_to_user_id',
                $employee->id
            )->count();



            $followUps = FollowUp::where(
                'assigned_to_user_id',
                $employee->id
            )
            ->where('status','Pending')
            ->count();



            return [

                'user' => $employee,

                'leads' => $leads,

                'customers' => $customers,

                'activities' => $activities,

                'followUps' => $followUps,


                'score' => $leads > 0
                    ? round(($customers / $leads) * 100)
                    : 0

            ];


        });




        /*
        |--------------------------------------------------------------------------
        | Dashboard Cards
        |--------------------------------------------------------------------------
        */


        $totalLeads = Lead::whereIn(
            'assigned_to_user_id',
            $visibleUserIds
        )->count();



        $totalCustomers = Customer::whereIn(
            'assigned_to_user_id',
            $visibleUserIds
        )->count();



        $totalActivities = Activity::whereIn(
            'assigned_to_user_id',
            $visibleUserIds
        )->count();



        $totalPendingFollowUps = FollowUp::whereIn(
            'assigned_to_user_id',
            $visibleUserIds
        )
        ->where('status','Pending')
        ->count();



        $upcomingFollowUps = FollowUp::whereIn(
            'assigned_to_user_id',
            $visibleUserIds
        )
        ->whereDate('date','>=',now())
        ->orderBy('date')
        ->limit(5)
        ->get();



        $conversionRate = $totalLeads > 0
            ? round(($totalCustomers / $totalLeads) * 100)
            : 0;




        /*
        |--------------------------------------------------------------------------
        | Monthly Leads
        |--------------------------------------------------------------------------
        */


        $monthlyLeads = Lead::whereIn(
            'assigned_to_user_id',
            $visibleUserIds
        )
        ->select(

            DB::raw('MONTH(created_at) as month'),

            DB::raw('COUNT(*) as total')

        )
        ->groupBy('month')
        ->pluck('total','month')
        ->toArray();




        /*
        |--------------------------------------------------------------------------
        | Lead Status
        |--------------------------------------------------------------------------
        */


        $leadStatus = [


            'New' => Lead::whereIn(
                'assigned_to_user_id',
                $visibleUserIds
            )
            ->where('status','New')
            ->count(),



            'Proposal' => Lead::whereIn(
                'assigned_to_user_id',
                $visibleUserIds
            )
            ->where('status','Proposal')
            ->count(),



            'Won' => Lead::whereIn(
                'assigned_to_user_id',
                $visibleUserIds
            )
            ->where('status','Won')
            ->count(),



            'Lost' => Lead::whereIn(
                'assigned_to_user_id',
                $visibleUserIds
            )
            ->where('status','Lost')
            ->count(),

        ];





        return view('performance.index', compact(

            'performance',

            'totalLeads',

            'totalCustomers',

            'totalActivities',

            'totalPendingFollowUps',

            'upcomingFollowUps',

            'conversionRate',

            'monthlyLeads',

            'leadStatus'

        ));

    }
}