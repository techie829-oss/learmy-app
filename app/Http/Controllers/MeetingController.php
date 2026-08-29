<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\MeetingTarget;
use App\Modules\Integrations\Services\Clients\GoogleClient;
use App\Services\MeetingNotificationService;
use App\Modules\Shared\Models\ChannelAccount;
use App\Modules\Shared\Models\ContactTag;
use App\Modules\Shared\Models\Segment;
use App\Modules\Whatsapp\Models\WhatsappTemplate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MeetingController extends Controller
{
    public function __construct(
        private readonly MeetingNotificationService $notificationService
    ) {}

    public function index(Request $request)
    {
        $workspaceId = $request->user()->current_workspace_id ?? $request->user()->workspace_id;
        $meetings = Meeting::with('targets')->where('workspace_id', $workspaceId)->get();

        return Inertia::render('client/Meetings/Index', [
            'meetings' => $meetings,
        ]);
    }

    public function create(Request $request)
    {
        $workspaceId = $request->user()->current_workspace_id ?? $request->user()->workspace_id;
        $tags        = ContactTag::where('workspace_id', $workspaceId)->get(['id', 'name']);
        $segments    = Segment::where('workspace_id', $workspaceId)->get(['id', 'name']);

        // WhatsApp templates for notification selector
        $waTemplates = $this->getWhatsappTemplates($workspaceId);
        $waGroups    = $this->fetchWhatsappGroups($workspaceId);

        // Check if QR or Meta WhatsApp is connected
        $whatsappConnected = ChannelAccount::where('workspace_id', $workspaceId)
            ->where('channel', 'whatsapp')
            ->where('status', 'active')
            ->exists();

        return Inertia::render('client/Meetings/Create', [
            'tags'               => $tags,
            'segments'           => $segments,
            'waGroups'           => $waGroups,
            'workspace_id'       => $workspaceId,
            'waTemplates'        => $waTemplates,
            'whatsappConnected'  => $whatsappConnected,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'                      => 'required|string|max:255',
            'description'                => 'nullable|string',
            'start_time'                 => 'required|date',
            'end_time'                   => 'required|date|after:start_time',
'timezone'                   => 'nullable|string|max:64',
            'custom_meet_link'           => 'nullable|url',
            'whatsapp_template'          => 'nullable|string|max:128',
            'send_whatsapp_notification' => 'nullable|boolean',
            'reminder_settings'          => 'nullable|array',
            'targets'                    => 'nullable|array',
            'targets.*.type'             => 'required_with:targets|string',
            'targets.*.id'               => 'nullable',
        ]);

        $workspaceId = $request->user()->current_workspace_id ?? $request->user()->workspace_id;

        $googleWarning = null;

        return DB::transaction(function () use ($validated, $workspaceId, &$googleWarning) {
            $meetLink      = !empty($validated['custom_meet_link']) ? $validated['custom_meet_link'] : null;
            $googleEventId = null;

            // Generate Google Meet Link if Google Integration is connected and no manual link was provided
            $googleClient = GoogleClient::resolveForWorkspace($workspaceId);
            if ($googleClient && empty($meetLink)) {
                try {
                    $event = $googleClient->createCalendarEvent(
                        'primary',
                        $validated['title'],
                        Carbon::parse($validated['start_time'])->toAtomString(),
                        Carbon::parse($validated['end_time'])->toAtomString(),
                        [],
                        true,
                        $validated['description'] ?? null,
                        $validated['timezone'] ?? 'UTC'
                    );

                    $meetLink      = $event['meet_url'] ?? null;
                    $googleEventId = $event['event_id'] ?? null;

                    if (empty($meetLink)) {
                        \Log::warning('Google Calendar event created but no Meet link returned', ['event' => $event]);
                        $googleWarning = 'Class scheduled, but Google Meet link could not be generated.';
                    }
                } catch (\Throwable $e) {
                    \Log::error('Google Calendar API failed during meeting creation', [
                        'error'        => $e->getMessage(),
                        'workspace_id' => $workspaceId,
                    ]);
                    $googleWarning = 'Class scheduled, but Google Meet link failed: ' . $e->getMessage();
                }
            }

            // Create Meeting
            $meeting = Meeting::create([
                'workspace_id'               => $workspaceId,
                'title'                      => $validated['title'],
                'description'                => $validated['description'] ?? null,
                'start_time'                 => $validated['start_time'],
                'end_time'                   => $validated['end_time'],
                'timezone'                   => $validated['timezone'] ?? 'UTC',
                'google_event_id'            => $googleEventId,
                'meet_link'                  => $meetLink,
                'status'                     => 'scheduled',
                'whatsapp_template'          => $validated['whatsapp_template'] ?? null,
                'send_whatsapp_notification' => $validated['send_whatsapp_notification'] ?? true,
                'reminder_settings'          => $validated['reminder_settings'] ?? null,
            ]);

            // Process and Save Smart Mapping Targets (auto-sync WA Groups to ContactTag if selected)
            foreach ($validated['targets'] ?? [] as $target) {
                $targetType  = $target['type'] ?? '';
                $targetId    = $target['id'] ?? null;
                $notifyMode  = $target['notify_mode'] ?? 'group'; // 'group' | 'individual'

                if ($targetType === 'wa_group') {
                    $groupName = $target['name'] ?? 'WhatsApp Group';
                    $tagId     = $this->syncWhatsappGroupAsTag($workspaceId, (string)$targetId, (string)$groupName);
                    if ($tagId) {
                        MeetingTarget::create([
                            'meeting_id'  => $meeting->id,
                            'target_type' => ContactTag::class,
                            'target_id'   => $tagId,
                            'notify_mode' => $notifyMode,
                        ]);
                    }
                } else {
                    MeetingTarget::create([
                        'meeting_id'  => $meeting->id,
                        'target_type' => $targetType,
                        'target_id'   => (int) $targetId,
                        'notify_mode' => 'individual', // Tags/Segments → always individual
                    ]);
                }
            }

            // Trigger Instant Notification for 'on_create' trigger
            $notifReport = $this->notificationService->dispatchNotifications($meeting, 'on_create');

            if ($googleWarning) {
                $successMsg = $googleWarning;
            } elseif (! $notifReport['whatsapp_connected']) {
                $successMsg = 'Class scheduled! Meet Link: ' . ($meetLink ?? 'None') . '. Note: Connect WhatsApp in Channel Setup to send automated WhatsApp reminders.';
            } elseif ($notifReport['contacts_count'] > 0) {
                $successMsg = 'Class scheduled & WhatsApp notifications sent to ' . $notifReport['sent_count'] . ' of ' . $notifReport['contacts_count'] . ' students!';
            } else {
                $successMsg = 'Class scheduled successfully! Meet Link: ' . ($meetLink ?? 'None');
            }

            return redirect()->route('client.meetings.index')->with('success', $successMsg);
        });
    }

    public function show(Request $request, Meeting $meeting)
    {
        $workspaceId = $request->user()->current_workspace_id ?? $request->user()->workspace_id;
        abort_if($meeting->workspace_id !== $workspaceId, 403);

        $tags        = ContactTag::where('workspace_id', $workspaceId)->get(['id', 'name']);
        $segments    = Segment::where('workspace_id', $workspaceId)->get(['id', 'name']);
        $waTemplates = $this->getWhatsappTemplates($workspaceId);
        $waGroups    = $this->fetchWhatsappGroups($workspaceId);

        $whatsappConnected = ChannelAccount::where('workspace_id', $workspaceId)
            ->where('channel', 'whatsapp')
            ->where('status', 'active')
            ->exists();

        return Inertia::render('client/Meetings/Create', [
            'meeting'           => $meeting->load('targets'),
            'tags'              => $tags,
            'segments'          => $segments,
            'waGroups'          => $waGroups,
            'workspace_id'      => $workspaceId,
            'waTemplates'       => $waTemplates,
            'whatsappConnected' => $whatsappConnected,
        ]);
    }

    public function edit(Request $request, Meeting $meeting)
    {
        return $this->show($request, $meeting);
    }

    public function update(Request $request, Meeting $meeting)
    {
        $workspaceId = $request->user()->current_workspace_id ?? $request->user()->workspace_id;
        abort_if($meeting->workspace_id !== $workspaceId, 403);

        $validated = $request->validate([
            'title'                      => 'required|string|max:255',
            'description'                => 'nullable|string',
            'start_time'                 => 'required|date',
            'end_time'                   => 'required|date|after:start_time',
'timezone'                   => 'nullable|string|max:64',
            'custom_meet_link'           => 'nullable|url',
            'whatsapp_template'          => 'nullable|string|max:128',
            'send_whatsapp_notification' => 'nullable|boolean',
            'reminder_settings'          => 'nullable|array',
            'targets'                    => 'nullable|array',
            'targets.*.type'             => 'required_with:targets|string',
            'targets.*.id'               => 'nullable',
        ]);

        return DB::transaction(function () use ($meeting, $validated, $workspaceId) {
            $meeting->update([
                'title'                      => $validated['title'],
                'description'                => $validated['description'] ?? null,
                'start_time'                 => $validated['start_time'],
                'end_time'                   => $validated['end_time'],
                'timezone'                   => $validated['timezone'] ?? 'UTC',
                'meet_link'                  => !empty($validated['custom_meet_link']) ? $validated['custom_meet_link'] : $meeting->meet_link,
                'whatsapp_template'          => $validated['whatsapp_template'] ?? null,
                'send_whatsapp_notification' => $validated['send_whatsapp_notification'] ?? true,
                'reminder_settings'          => $validated['reminder_settings'] ?? null,
            ]);

            // Replace targets
            $meeting->targets()->delete();
            foreach ($validated['targets'] ?? [] as $target) {
                $targetType  = $target['type'] ?? '';
                $targetId    = $target['id'] ?? null;
                $notifyMode  = $target['notify_mode'] ?? 'group';

                if ($targetType === 'wa_group') {
                    $groupName = $target['name'] ?? 'WhatsApp Group';
                    $tagId     = $this->syncWhatsappGroupAsTag($workspaceId, (string)$targetId, (string)$groupName);
                    if ($tagId) {
                        MeetingTarget::create([
                            'meeting_id'  => $meeting->id,
                            'target_type' => ContactTag::class,
                            'target_id'   => $tagId,
                            'notify_mode' => $notifyMode,
                        ]);
                    }
                } else {
                    MeetingTarget::create([
                        'meeting_id'  => $meeting->id,
                        'target_type' => $targetType,
                        'target_id'   => (int) $targetId,
                        'notify_mode' => 'individual',
                    ]);
                }
            }

            return redirect()->route('client.meetings.index')->with('success', 'Meeting updated successfully.');
        });
    }

    public function destroy(Request $request, Meeting $meeting)
    {
        $workspaceId = $request->user()->current_workspace_id ?? $request->user()->workspace_id;
        abort_if($meeting->workspace_id !== $workspaceId, 403);

        $meeting->targets()->delete();
        $meeting->delete();

        return redirect()->route('client.meetings.index')->with('success', 'Meeting deleted.');
    }

    /**
     * Fetch WhatsApp groups from connected QR Baileys session.
     */
    private function fetchWhatsappGroups(int $workspaceId): array
    {
        $qrAccount = ChannelAccount::where('workspace_id', $workspaceId)
            ->where('provider', 'qr_baileys')
            ->where('status', 'active')
            ->first();

        if (! $qrAccount) {
            return [];
        }

        $sessionId = $qrAccount->meta_json['session_id'] ?? "workspace_{$workspaceId}_qr";

        try {
            $qrUrl = config('services.whatsapp_qr.url', 'http://127.0.0.1:3001');
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get("{$qrUrl}/api/groups", [
                'sessionId' => $sessionId,
            ]);

            if ($response->successful() && $response->json('success')) {
                return $response->json('groups', []);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[MeetingController] Failed to fetch WA groups: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Sync WhatsApp Group participants as Contacts with a ContactTag.
     */
    private function syncWhatsappGroupAsTag(int $workspaceId, string $groupId, string $groupName): ?int
    {
        $qrAccount = ChannelAccount::where('workspace_id', $workspaceId)
            ->where('provider', 'qr_baileys')
            ->where('status', 'active')
            ->first();

        if (! $qrAccount) {
            return null;
        }

        $sessionId = $qrAccount->meta_json['session_id'] ?? "workspace_{$workspaceId}_qr";
        $qrUrl     = config('services.whatsapp_qr.url', 'http://127.0.0.1:3001');

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get(
                "{$qrUrl}/api/groups/" . urlencode($groupId) . '/participants',
                ['sessionId' => $sessionId]
            );

            if (! $response->successful()) {
                \Illuminate\Support\Facades\Log::warning('[MeetingController] Failed to fetch participants for group ' . $groupId);
                return null;
            }

            $participants = $response->json('group.participants', []);
            if (empty($participants)) {
                return null;
            }

            $tagName = 'Group: ' . trim($groupName);
            $tag     = ContactTag::firstOrCreate(
                ['workspace_id' => $workspaceId, 'name' => $tagName],
                ['color' => '#10B981', 'wa_group_jid' => $groupId]
            );
            if ($groupId && ($tag->wa_group_jid !== $groupId)) {
                $tag->update(['wa_group_jid' => $groupId]);
            }

            foreach ($participants as $p) {
                $phone = $p['phone'] ?? null;
                if (! $phone) {
                    continue;
                }

                if (! str_starts_with($phone, '+')) {
                    $phone = '+' . $phone;
                }

                $contact = \App\Modules\Shared\Models\Contact::withTrashed()
                    ->where('workspace_id', $workspaceId)
                    ->where('phone_e164', $phone)
                    ->first();

                if (! $contact) {
                        $contact = \App\Modules\Shared\Models\Contact::create([
                        'workspace_id'      => $workspaceId,
                        'phone_e164'        => $phone,
                        'first_name'        => $p['name'] ?? 'Student',
                        'last_name'         => null,
                        'opt_in_whatsapp'   => true,
                        'source'            => 'whatsapp_group',
                    ]);
                } elseif ($contact->trashed()) {
                    $contact->restore();
                }

                if (! $contact->tags()->where('contact_tags.id', $tag->id)->exists()) {
                    $contact->tags()->attach($tag->id);
                }
            }

            return $tag->id;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[MeetingController] Exception syncing group as tag: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch WhatsApp templates for this workspace (QR free-text or Meta approved templates).
     */
    private function getWhatsappTemplates(int $workspaceId): array
    {
        try {
            $templates = WhatsappTemplate::where('workspace_id', $workspaceId)
                ->where('status', 'APPROVED')
                ->get(['id', 'name', 'language', 'category'])
                ->map(fn($t) => [
                    'value' => $t->name,
                    'label' => "{$t->name} (" . ucfirst(strtolower($t->category)) . ' • ' . strtoupper($t->language) . ')',
                ])
                ->toArray();

            return $templates;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[MeetingController] Exception fetching whatsapp templates: ' . $e->getMessage());
            return [];
        }
    }
}
