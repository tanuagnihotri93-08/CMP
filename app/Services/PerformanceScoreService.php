<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\User;

class PerformanceScoreService
{
    /**
     * Calculate employee performance score
     */
    public function calculate(User $user): array
    {
        /*
        |--------------------------------------------------------------------------
        | CRM Data Count
        |--------------------------------------------------------------------------
        */

        $totalLeads = Lead::where('assigned_to_user_id', $user->id)->count();

        $totalCustomers = Customer::where('assigned_to_user_id', $user->id)->count();

        $totalActivities = Activity::where('assigned_to_user_id', $user->id)->count();

        $totalFollowUps = FollowUp::where('assigned_to_user_id', $user->id)->count();

        $completedFollowUps = FollowUp::where('assigned_to_user_id', $user->id)
            ->where('status', 'Completed')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Score Calculation (har score 0-100 ke beech capped)
        |--------------------------------------------------------------------------
        */

        // Lead handling score (25%)
        $leadScore = $totalLeads > 0
            ? min($totalLeads * 10, 100)
            : 0;

        // Follow up completion score (25%)
        $followUpScore = $totalFollowUps > 0
            ? min((int) round(($completedFollowUps / $totalFollowUps) * 100), 100)
            : 0;

        // Activity score (25%)
        $activityScore = min($totalActivities * 5, 100);

        // Conversion score (25%)
        $conversionScore = $totalLeads > 0
            ? min((int) round(($totalCustomers / $totalLeads) * 100), 100)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Final Score
        |--------------------------------------------------------------------------
        */

        $totalScore = (int) round(
            (
                $leadScore +
                $followUpScore +
                $activityScore +
                $conversionScore
            ) / 4
        );

        return [
            'user_id'          => $user->id,

            'lead_score'       => $leadScore,
            'followup_score'   => $followUpScore,
            'activity_score'   => $activityScore,
            'conversion_score' => $conversionScore,
            'total_score'      => $totalScore,

            'total_leads'      => $totalLeads,
            'total_customers'  => $totalCustomers,
            'total_activities' => $totalActivities,
            'total_followups'  => $totalFollowUps,
        ];
    }
}