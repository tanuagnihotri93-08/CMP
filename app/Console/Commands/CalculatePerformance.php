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

        /* Counts: 4 grouped queries total, per-user query nahi */
        $leads = $this->countsBy(Lead::query(), $ids);
        $customers = $this->countsBy(Customer::query(), $ids);
        $activities = $this->countsBy(Activity::query(), $ids);
        $pendingFollowUps = $this->countsBy(
            FollowUp::query()->where('status', 'Pending'),
            $ids
        );

        $done = 0;
        $failed = 0;

        foreach ($users as $user) {
            try {
                $score = $scoreService->calculate($user);

                $data = [
                    'lead_score'              => $score['lead_score'],
                    'followup_score'          => $score['followup_score'],
                    'activity_score'          => $score['activity_score'],
                    'conversion_score'        => $score['conversion_score'],
                    'total_score'             => $score['total_score'],
                    'leads_count'             => $leads[$user->id] ?? 0,
                    'customers_count'         => $customers[$user->id] ?? 0,
                    'activities_count'        => $activities[$user->id] ?? 0,
                    'pending_followups_count' => $pendingFollowUps[$user->id] ?? 0,
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