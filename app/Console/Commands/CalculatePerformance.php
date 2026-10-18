<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Performance;
use App\Models\PerformanceHistory;
use App\Models\User;
use App\Services\PerformanceScoreService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CalculatePerformance extends Command
{
    protected $signature = 'performance:calculate';

    protected $description = 'Calculate performance scores and store latest snapshot + history';

    public function handle(PerformanceScoreService $scoreService): int
    {
        $today = now()->toDateString();

        $users = User::where('is_active', true)
            ->whereHas('role', fn ($q) => $q->whereIn('name', ['manager', 'employee']))
            ->get();

        if ($users->isEmpty()) {
            $this->warn('No active users found.');

            return self::SUCCESS;
        }

        $ids = $users->pluck('id');

        /* Reuse grouped counts for score calculation and stored snapshots. */
        $leads = Lead::query()
            ->whereIn('assigned_to_user_id', $ids)
            ->select('assigned_to_user_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as won', ['won'])
            ->groupBy('assigned_to_user_id')
            ->get()
            ->keyBy('assigned_to_user_id');
        $customers = $this->countsBy(Customer::query(), $ids);
        $activities = $this->countsBy(Activity::query(), $ids);
        $followUps = FollowUp::query()
            ->whereIn('assigned_to_user_id', $ids)
            ->select('assigned_to_user_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed', ['completed'])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as scheduled', ['scheduled'])
            ->groupBy('assigned_to_user_id')
            ->get()
            ->keyBy('assigned_to_user_id');

        $done = 0;
        $failed = 0;

        foreach ($users as $user) {
            try {
                $followUpCounts = $followUps->get($user->id);
                $leadCounts = $leads->get($user->id);
                $leadCount = (int) ($leadCounts?->total ?? 0);
                $wonLeadCount = (int) ($leadCounts?->won ?? 0);
                $customerCount = (int) ($customers[$user->id] ?? 0);
                $activityCount = (int) ($activities[$user->id] ?? 0);
                $totalFollowUpCount = (int) ($followUpCounts?->total ?? 0);
                $completedFollowUpCount = (int) ($followUpCounts?->completed ?? 0);

                $score = $scoreService->calculate($user, [
                    'total_leads' => $leadCount,
                    'won_leads' => $wonLeadCount,
                    'total_customers' => $customerCount,
                    'total_activities' => $activityCount,
                    'total_followups' => $totalFollowUpCount,
                    'completed_followups' => $completedFollowUpCount,
                ]);

                $data = [
                    'lead_score'              => $score['lead_score'],
                    'followup_score'          => $score['followup_score'],
                    'activity_score'          => $score['activity_score'],
                    'conversion_score'        => $score['conversion_score'],
                    'total_score'             => $score['total_score'],
                    'leads_count'             => $leadCount,
                    'customers_count'         => $customerCount,
                    'activities_count'        => $activityCount,
                    'pending_followups_count' => (int) ($followUpCounts?->scheduled ?? 0),
                ];

                DB::transaction(function () use ($user, $today, $data) {
                    // Latest snapshot
                    Performance::updateOrCreate(['user_id' => $user->id], $data);

                    // History (ek din = ek row)
                    PerformanceHistory::updateOrCreate(
                        ['user_id' => $user->id, 'snapshot_date' => $today],
                        $data
                    );
                });

                $done++;
            } catch (\Throwable $e) {
                $failed++;

                Log::error('performance:calculate failed for user ' . $user->id, [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Performance calculated: {$done} ok, {$failed} failed.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function countsBy($query, $ids): array
    {
        return $query
            ->whereIn('assigned_to_user_id', $ids)
            ->select('assigned_to_user_id', DB::raw('COUNT(*) as total'))
            ->groupBy('assigned_to_user_id')
            ->pluck('total', 'assigned_to_user_id')
            ->all();
    }
}
