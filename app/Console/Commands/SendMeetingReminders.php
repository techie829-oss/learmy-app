<?php

namespace App\Console\Commands;

use App\Models\Meeting;
use App\Services\MeetingNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendMeetingReminders extends Command
{
    protected $signature = 'meetings:send-reminders';
    protected $description = 'Check upcoming meetings and dispatch automated WhatsApp reminders (1 hour and 15 mins before start time)';

    public function handle(MeetingNotificationService $notificationService): int
    {
        $now = Carbon::now();

        // 1. Reminders for meetings starting in 55-65 minutes (1 Hour Reminder)
        $oneHourWindowStart = $now->copy()->addMinutes(55);
        $oneHourWindowEnd = $now->copy()->addMinutes(65);

        $oneHourMeetings = Meeting::where('status', 'scheduled')
            ->whereNull('reminded_1h_at')
            ->whereBetween('start_time', [$oneHourWindowStart, $oneHourWindowEnd])
            ->get();

        foreach ($oneHourMeetings as $meeting) {
            $count = $notificationService->dispatchNotifications($meeting, 'class_reminder_1h');
            $meeting->update(['reminded_1h_at' => now()]);
            $this->info("Dispatched 1-hour reminders for Meeting ID {$meeting->id}: {$count} sent.");
        }

        // 2. Reminders for meetings starting in 10-20 minutes (15 Min Reminder)
        $fifteenMinWindowStart = $now->copy()->addMinutes(10);
        $fifteenMinWindowEnd = $now->copy()->addMinutes(20);

        $fifteenMinMeetings = Meeting::where('status', 'scheduled')
            ->whereNull('reminded_15m_at')
            ->whereBetween('start_time', [$fifteenMinWindowStart, $fifteenMinWindowEnd])
            ->get();

        foreach ($fifteenMinMeetings as $meeting) {
            $count = $notificationService->dispatchNotifications($meeting, 'class_reminder_15m');
            $meeting->update(['reminded_15m_at' => now()]);
            $this->info("Dispatched 15-min reminders for Meeting ID {$meeting->id}: {$count} sent.");
        }

        return Command::SUCCESS;
    }
}
