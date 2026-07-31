<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\MeetingTarget;
use App\Modules\Integrations\Services\Clients\GoogleClient;
use App\Services\MeetingNotificationService;
use App\Modules\Shared\Models\ContactTag;
use App\Modules\Shared\Models\Segment;
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
        $workspaceId = $request->user()->workspace_id;
        $meetings = Meeting::with('targets')->where('workspace_id', $workspaceId)->get();

        return Inertia::render('client/Meetings/Index', [
            'meetings' => $meetings,
        ]);
    }

    public function create(Request $request)
    {
        $workspaceId = $request->user()->workspace_id;
        $tags = ContactTag::where('workspace_id', $workspaceId)->get(['id', 'name']);
        $segments = Segment::where('workspace_id', $workspaceId)->get(['id', 'name']);

        return Inertia::render('client/Meetings/Create', [
            'tags' => $tags,
            'segments' => $segments,
            'workspace_id' => $workspaceId,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time'  => 'required|date',
            'end_time'    => 'required|date|after:start_time',
            'timezone'    => 'nullable|string|timezone',
            'targets'     => 'required|array|min:1',
            'targets.*.type' => 'required|string',
            'targets.*.id'   => 'required|integer',
        ]);

        // Always use the authenticated user's workspace — never trust client-supplied workspace_id.
        $workspaceId = $request->user()->current_workspace_id ?? $request->user()->workspace_id;

        return DB::transaction(function () use ($validated, $workspaceId) {
            $meetLink = null;
            $googleEventId = null;

            // Generate Google Meet Link if Google Integration is connected
            $googleClient = GoogleClient::resolve();
            if ($googleClient) {
                // Fetch calendar ID from settings or use primary
                $calendarId = 'primary'; // We can make this configurable later
                $event = $googleClient->createCalendarEvent(
                    $calendarId,
                    $validated['title'],
                    Carbon::parse($validated['start_time'])->toAtomString(),
                    Carbon::parse($validated['end_time'])->toAtomString(),
                    [], // attendeeEmails (we notify via WhatsApp instead)
                    true, // withMeet
                    $validated['description'] ?? null,
                    $validated['timezone'] ?? 'UTC'
                );

                $meetLink = $event['meet_url'] ?? null;
                $googleEventId = $event['event_id'] ?? null;
            }

            // Create Meeting
            $meeting = Meeting::create([
                'workspace_id'   => $workspaceId,
                'title'          => $validated['title'],
                'description'    => $validated['description'] ?? null,
                'start_time'     => $validated['start_time'],
                'end_time'       => $validated['end_time'],
                'timezone'       => $validated['timezone'] ?? 'UTC',
                'google_event_id'=> $googleEventId,
                'meet_link'      => $meetLink,
                'status'         => 'scheduled',
            ]);

            // Save Smart Mapping Targets
            foreach ($validated['targets'] as $target) {
                MeetingTarget::create([
                    'meeting_id' => $meeting->id,
                    'target_type' => $target['type'], // e.g., 'App\\Modules\\Shared\\Models\\ContactTag'
                    'target_id' => $target['id'],
                ]);
            }

            // Trigger Notification Service immediately (this can be deferred to a Job in a real production environment)
            $sentCount = $this->notificationService->dispatchNotifications($meeting);

            return redirect()->route('meetings.index')->with('success', 'Meeting scheduled successfully. Notifications sent: ' . $sentCount);
        });
    }
}
