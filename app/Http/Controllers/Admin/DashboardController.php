<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Shared\Models\Contact;
use App\Modules\Shared\Models\Conversation;
use App\Modules\Shared\Models\Message;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /** Allowed date-range windows (in days). */
    private const RANGES = [7, 30, 90];

    public function __invoke(Request $request, AnalyticsService $analytics): Response
    {
        $range = (int) $request->integer('range', 30);
        if (! in_array($range, self::RANGES, true)) {
            $range = 30;
        }

        $now = Carbon::now();
        $from = $now->copy()->subDays($range - 1)->startOfDay();
        $to = $now->copy()->endOfDay();
        $prevTo = $from->copy()->subDay()->endOfDay();
        $prevFrom = $prevTo->copy()->subDays($range - 1)->startOfDay();

        // ── Headline metrics ─────────────────────────────────────────────────────
        $usersCount = User::count();
        $newUsers = User::whereBetween('created_at', [$from, $to])->count();

        $messagesPeriod = Message::whereBetween('created_at', [$from, $to])->count();
        $messagesPrev = Message::whereBetween('created_at', [$prevFrom, $prevTo])->count();

        return Inertia::render('Admin/Dashboard', [
            'range' => $range,
            'stats' => [
                'users_count' => $usersCount,
                'new_users' => $newUsers,
                'messages_period' => $messagesPeriod,
                'messages_delta' => $this->pctDelta($messagesPeriod, $messagesPrev),
                'contacts_total' => Contact::count(),
                'conversations_total' => Conversation::count(),
            ],
            'charts' => [
                'messages_by_day' => $analytics->platformMessageVolumeByChannel($from, $to),
                'channel_mix' => $analytics->platformChannelMix($from, $to),
                'top_ai_workspaces' => $analytics->topWorkspacesByAiCost(10),
            ],
            'warnings' => array_filter([
                (config('mail.default') === 'log' && app()->isProduction())
                    ? 'MAIL_MAILER is set to "log" – emails will NOT be delivered to users in production.'
                    : null,
            ]),
        ]);
    }

    /**
     * Percentage change between two periods. Null when there is no comparable baseline.
     */
    private function pctDelta(float|int $current, float|int $previous): ?float
    {
        $current = (float) $current;
        $previous = (float) $previous;

        if ($previous <= 0.0) {
            return $current > 0.0 ? 100.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
