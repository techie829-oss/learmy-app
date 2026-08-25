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
    protected $description = 'Check upcoming meetings and dispatch automated WhatsApp reminders for morning, 15-min before, and class start triggers';

    public function handle(MeetingNotificationService $notificationService): int
    {
        $now = Carbon::now();

        // ─────────────────────────────────────────────────────────────────────
        // 1. Morning Reminders (Sent on the day of the class, at or after 08:00 AM)
        // ─────────────────────────────────────────────────────────────────────
        if ($now->hour >= 8) {
            $todayStart = $now->copy()->startOfDay();
            $todayEnd   = $now->copy()->endOfDay();

            $morningMeetings = Meeting::where('status', 'scheduled')
                ->whereNull('reminded_morning_at')
                ->whereBetween('start_time', [$todayStart, $todayEnd])
                ->get();

            foreach ($morningMeetings as $meeting) {
                if ($meeting->isReminderEnabled('morning')) {
                    $report = $notificationService->dispatchNotifications($meeting, 'morning');
                    $meeting->update(['reminded_morning_at' => now()]);
                    $this->info("Dispatched MORNING reminder for Meeting #{$meeting->id} ('{$meeting->title}'): {$report['sent_count']} sent.");
                } else {
                    // Mark as processed if trigger disabled so we don't query it repeatedly
                    $meeting->update(['reminded_morning_at' => now()]);
                }
            }
        }

        // ─────────────────────────────────────────────────────────────────────
        // 2. 15 Minutes Before Class Reminders (Classes starting in 5 to 20 minutes)
        // ─────────────────────────────────────────────────────────────────────
        $fifteenMinStart = $now->copy()->addMinutes(5);
        $fifteenMinEnd   = $now->copy()->addMinutes(20);

        $fifteenMinMeetings = Meeting::where('status', 'scheduled')
            ->whereNull('reminded_15m_at')
            ->whereBetween('start_time', [$fifteenMinStart, $fifteenMinEnd])
            ->get();

        foreach ($fifteenMinMeetings as $meeting) {
            if ($meeting->isReminderEnabled('before_15m')) {
                $report = $notificationService->dispatchNotifications($meeting, 'before_15m');
                $meeting->update(['reminded_15m_at' => now()]);
                $this->info("Dispatched 15-MIN reminder for Meeting #{$meeting->id} ('{$meeting->title}'): {$report['sent_count']} sent.");
            } else {
                $meeting->update(['reminded_15m_at' => now()]);
            }
        }

        // ─────────────────────────────────────────────────────────────────────
        // 3. Class Starts / LIVE NOW Reminders (Classes starting now: -10m to +3m window)
        // ─────────────────────────────────────────────────────────────────────
        $liveStart = $now->copy()->subMinutes(10);
        $liveEnd   = $now->copy()->addMinutes(3);

        $liveMeetings = Meeting::where('status', 'scheduled')
            ->whereNull('reminded_start_at')
            ->whereBetween('start_time', [$liveStart, $liveEnd])
            ->get();

        foreach ($liveMeetings as $meeting) {
            if ($meeting->isReminderEnabled('on_start')) {
                $report = $notificationService->dispatchNotifications($meeting, 'on_start');
                $meeting->update(['reminded_start_at' => now()]);
                $this->info("Dispatched LIVE START reminder for Meeting #{$meeting->id} ('{$meeting->title}'): {$report['sent_count']} sent.");
            } else {
                $meeting->update(['reminded_start_at' => now()]);
            }
        }

        return Command::SUCCESS;
    }
}
