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

        // Check if QR or Meta WhatsApp is connected
        $whatsappConnected = ChannelAccount::where('workspace_id', $workspaceId)
            ->where('channel', 'whatsapp')
            ->where('status', 'active')
            ->exists();

        return Inertia::render('client/Meetings/Create', [
            'tags'               => $tags,
            'segments'           => $segments,
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
            'timezone'                   => 'nullable|string|timezone',
            'custom_meet_link'           => 'nullable|url',
            'whatsapp_template'          => 'nullable|string|max:128',
            'send_whatsapp_notification' => 'nullable|boolean',
            'targets'                    => 'nullable|array',
            'targets.*.type'             => 'required_with:targets|string',
            'targets.*.id'               => 'required_with:targets|integer',
        ]);

        // Always use the authenticated user's workspace — never trust client-supplied workspace_id.
        $workspaceId = $request->user()->current_workspace_id ?? $request->user()->workspace_id;

        $googleWarning = null;

        return DB::transaction(function () use ($validated, $workspaceId, &$googleWarning) {
            $meetLink     = !empty($validated['custom_meet_link']) ? $validated['custom_meet_link'] : null;
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
                        [],    // attendeeEmails (we notify via WhatsApp instead)
                        true,  // withMeet
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
            ]);

            // Save Smart Mapping Targets
            foreach ($validated['targets'] ?? [] as $target) {
                MeetingTarget::create([
                    'meeting_id'  => $meeting->id,
                    'target_type' => $target['type'],
                    'target_id'   => $target['id'],
                ]);
            }

            // Trigger Notification Service
            $notifReport = $this->notificationService->dispatchNotifications($meeting);

            if ($googleWarning) {
                $successMsg = $googleWarning;
            } elseif (! $notifReport['whatsapp_connected']) {
                $successMsg = 'Class scheduled! Meet Link: ' . ($meetLink ?? 'None') . '. Note: Connect WhatsApp in Channel Setup to send automated WhatsApp reminders to students.';
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

        $tags     = ContactTag::where('workspace_id', $workspaceId)->get(['id', 'name']);
        $segments = Segment::where('workspace_id', $workspaceId)->get(['id', 'name']);

        return Inertia::render('client/Meetings/Create', [
            'meeting'      => $meeting->load('targets'),
            'tags'         => $tags,
            'segments'     => $segments,
            'workspace_id' => $workspaceId,
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
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'start_time'       => 'required|date',
            'end_time'         => 'required|date|after:start_time',
            'timezone'         => 'nullable|string|timezone',
            'custom_meet_link' => 'nullable|url',
            'targets'          => 'nullable|array',
            'targets.*.type'   => 'required_with:targets|string',
            'targets.*.id'     => 'required_with:targets|integer',
        ]);

        return DB::transaction(function () use ($meeting, $validated) {
            $meeting->update([
                'title'       => $validated['title'],
                'description' => $validated['description'] ?? null,
                'start_time'  => $validated['start_time'],
                'end_time'    => $validated['end_time'],
                'timezone'    => $validated['timezone'] ?? 'UTC',
                'meet_link'   => !empty($validated['custom_meet_link']) ? $validated['custom_meet_link'] : $meeting->meet_link,
            ]);

            // Replace targets
            $meeting->targets()->delete();
            foreach ($validated['targets'] ?? [] as $target) {
                MeetingTarget::create([
                    'meeting_id'  => $meeting->id,
                    'target_type' => $target['type'],
                    'target_id'   => $target['id'],
                ]);
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
     * Fetch WhatsApp templates for this workspace (QR free-text or Meta approved templates).
     */
    private function getWhatsappTemplates(int $workspaceId): array
    {
        // Try to load from WhatsappTemplate model (Meta approved templates)
        try {
            $templates = WhatsappTemplate::where('workspace_id', $workspaceId)
                ->where('status', 'APPROVED')
                ->get(['id', 'name', 'display_name'])
                ->map(fn($t) => [
                    'value' => $t->name,
                    'label' => $t->display_name ?? $t->name,
                ])
                ->toArray();

            // Always prepend the default class reminder template
            array_unshift($templates, [
                'value' => 'class_scheduled_notification',
                'label' => 'Default Class Reminder (built-in)',
            ]);

            return $templates;
        } catch (\Throwable $e) {
            return [
                ['value' => 'class_scheduled_notification', 'label' => 'Default Class Reminder (built-in)'],
            ];
        }
    }
}
