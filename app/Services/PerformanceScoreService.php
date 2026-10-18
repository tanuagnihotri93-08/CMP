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
    public function calculate(User $user, ?array $counts = null): array
    {
        if ($counts === null) {
            $counts = [
                'total_leads' => Lead::where('assigned_to_user_id', $user->id)->count(),
                'won_leads' => Lead::where('assigned_to_user_id', $user->id)
                    ->where('status', 'won')
                    ->count(),
                'total_customers' => Customer::where('assigned_to_user_id', $user->id)->count(),
                'total_activities' => Activity::where('assigned_to_user_id', $user->id)->count(),
                'total_followups' => FollowUp::where('assigned_to_user_id', $user->id)->count(),
                'completed_followups' => FollowUp::where('assigned_to_user_id', $user->id)
                    ->where('status', 'completed')
                    ->count(),
            ];
        }

        $totalLeads = (int) ($counts['total_leads'] ?? 0);
        $wonLeads = (int) ($counts['won_leads'] ?? 0);
        $totalCustomers = (int) ($counts['total_customers'] ?? 0);
        $totalActivities = (int) ($counts['total_activities'] ?? 0);
        $totalFollowUps = (int) ($counts['total_followups'] ?? 0);
        $completedFollowUps = (int) ($counts['completed_followups'] ?? 0);

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
            ? min((int) round(($wonLeads / $totalLeads) * 100), 100)
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
