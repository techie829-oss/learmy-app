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
    protected $description = 'Check active meetings and dispatch daily automated WhatsApp reminders (Morning, 15-min before, and Class Start) from Start Date to End Date';

    public function handle(MeetingNotificationService $notificationService): int
    {
        $now        = Carbon::now();
        $todayStart = $now->copy()->startOfDay();
        $todayEnd   = $now->copy()->endOfDay();

        // ─────────────────────────────────────────────────────────────────────
        // Fetch all active scheduled meetings where today's date is between
        // the class Start Date and End Date.
        // ─────────────────────────────────────────────────────────────────────
        $activeMeetings = Meeting::where('status', 'scheduled')
            ->where('start_time', '<=', $todayEnd)
            ->where('end_time', '>=', $todayStart)
            ->get();

        foreach ($activeMeetings as $meeting) {
            $startTime = Carbon::parse($meeting->start_time);

            // Compute today's class start time using today's date + class time of start_time
            $todayClassStart = $now->copy()->setTime(
                $startTime->hour,
                $startTime->minute,
                $startTime->second
            );

            // ─────────────────────────────────────────────────────────────────
            // 1. Morning Reminders (Sent on or after 08:00 AM if not sent today)
            // ─────────────────────────────────────────────────────────────────
            if ($now->hour >= 8) {
                $remindedMorningToday = $meeting->reminded_morning_at
                    && Carbon::parse($meeting->reminded_morning_at)->isSameDay($todayStart);

                if (! $remindedMorningToday) {
                    if ($meeting->isReminderEnabled('morning')) {
                        $report = $notificationService->dispatchNotifications($meeting, 'morning');
                        $meeting->update(['reminded_morning_at' => now()]);
                        $this->info("Dispatched MORNING reminder for Meeting #{$meeting->id} ('{$meeting->title}'): {$report['sent_count']} sent.");
                    } else {
                        // Mark as processed today if trigger disabled so we don't query it repeatedly
                        $meeting->update(['reminded_morning_at' => now()]);
                    }
                }
            }

            // ─────────────────────────────────────────────────────────────────
            // 2. 15 Minutes Before Class Reminders (Class starting in 5 to 20 minutes window)
            // ─────────────────────────────────────────────────────────────────
            $reminded15mToday = $meeting->reminded_15m_at
                && Carbon::parse($meeting->reminded_15m_at)->isSameDay($todayStart);

            if (! $reminded15mToday) {
                $windowStart = $todayClassStart->copy()->subMinutes(20);
                $windowEnd   = $todayClassStart->copy()->subMinutes(5);

                if ($now->between($windowStart, $windowEnd)) {
                    if ($meeting->isReminderEnabled('before_15m')) {
                        $report = $notificationService->dispatchNotifications($meeting, 'before_15m');
                        $meeting->update(['reminded_15m_at' => now()]);
                        $this->info("Dispatched 15-MIN reminder for Meeting #{$meeting->id} ('{$meeting->title}'): {$report['sent_count']} sent.");
                    } else {
                        $meeting->update(['reminded_15m_at' => now()]);
                    }
                }
            }

            // ─────────────────────────────────────────────────────────────────
            // 3. Class Starts / LIVE NOW Reminders (Class starting now: -5m to +10m window)
            // ─────────────────────────────────────────────────────────────────
            $remindedStartToday = $meeting->reminded_start_at
                && Carbon::parse($meeting->reminded_start_at)->isSameDay($todayStart);

            if (! $remindedStartToday) {
                $liveWindowStart = $todayClassStart->copy()->subMinutes(5);
                $liveWindowEnd   = $todayClassStart->copy()->addMinutes(10);

                if ($now->between($liveWindowStart, $liveWindowEnd)) {
                    if ($meeting->isReminderEnabled('on_start')) {
                        $report = $notificationService->dispatchNotifications($meeting, 'on_start');
                        $meeting->update(['reminded_start_at' => now()]);
                        $this->info("Dispatched LIVE START reminder for Meeting #{$meeting->id} ('{$meeting->title}'): {$report['sent_count']} sent.");
                    } else {
                        $meeting->update(['reminded_start_at' => now()]);
                    }
                }
            }
        }

        return Command::SUCCESS;
    }
}
