<?php

namespace App\Console\Commands;

use App\Models\FollowUp;
use App\Notifications\FollowUpReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendFollowUpReminders extends Command
{
    protected $signature = 'followups:send-reminders';

    protected $description = 'Send reminder notifications for follow-ups scheduled within the next 5 minutes';

    public function handle(): int
    {
        $now = Carbon::now();
        $fiveMinutesFromNow = $now->copy()->addMinutes(5);

        $followUps = FollowUp::query()
            ->where('status', 'scheduled')
            ->whereNull('reminder_sent_at')
            ->whereBetween('date', [$now, $fiveMinutesFromNow])
            ->whereHas('assignedTo', function ($query): void {
                $query->where('is_active', true);
            })
            ->with('assignedTo')
            ->get();

        if ($followUps->isEmpty()) {
            $this->output?->writeln('No follow-up reminders to send.');

            return self::SUCCESS;
        }

        $sent = 0;

        foreach ($followUps as $followUp) {
            $user = $followUp->assignedTo;

            $user->notify(new FollowUpReminderNotification($followUp));

            $followUp->update(['reminder_sent_at' => now()]);

            $sent++;
        }

        $this->output?->writeln("Follow-up reminders sent: {$sent}.");

        return self::SUCCESS;
    }
}