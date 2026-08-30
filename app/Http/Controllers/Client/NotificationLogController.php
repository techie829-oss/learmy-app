<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationLogController extends Controller
{
    public function index(Request $request): Response
    {
        $workspaceId = $request->user()->current_workspace_id ?? $request->user()->workspace_id;

        $query = NotificationLog::where('workspace_id', $workspaceId)
            ->with(['contact:id,first_name,last_name,phone_e164', 'meeting:id,title']);

        if ($request->filled('status') && in_array($request->status, ['sent', 'failed', 'delivered'], true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('phone', 'like', "%{$search}%")
                  ->orWhere('template_name', 'like', "%{$search}%")
                  ->orWhere('trigger', 'like', "%{$search}%");
            });
        }

        $logs = $query->orderByDesc('id')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (NotificationLog $log) => [
                'id'            => $log->id,
                'phone'         => $log->phone,
                'contact_name'  => $log->contact ? (trim(($log->contact->first_name ?? '') . ' ' . ($log->contact->last_name ?? '')) ?: null) : null,
                'meeting_title' => $log->meeting ? $log->meeting->title : null,
                'trigger'       => $log->trigger,
                'channel'       => $log->channel,
                'provider'      => $log->provider,
                'template_name' => $log->template_name,
                'status'        => $log->status,
                'error_message' => $log->error_message,
                'sent_at'       => $log->sent_at ? $log->sent_at->format('d M Y, h:i A') : $log->created_at->format('d M Y, h:i A'),
            ]);

        $stats = [
            'total'  => NotificationLog::where('workspace_id', $workspaceId)->count(),
            'sent'   => NotificationLog::where('workspace_id', $workspaceId)->where('status', 'sent')->count(),
            'failed' => NotificationLog::where('workspace_id', $workspaceId)->where('status', 'failed')->count(),
        ];

        return Inertia::render('client/Notifications/Logs', [
            'logs'    => $logs,
            'stats'   => $stats,
            'filters' => $request->only(['status', 'search']),
        ]);
    }
}
