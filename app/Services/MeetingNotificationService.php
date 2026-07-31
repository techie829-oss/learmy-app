<?php

namespace App\Services;

use App\Models\Meeting;
use App\Modules\Shared\Models\Contact;
use App\Modules\Shared\Models\ContactTag;
use App\Modules\Shared\Models\Segment;
use App\Modules\Whatsapp\Services\CloudApiClient;
use Illuminate\Support\Facades\Log;

class MeetingNotificationService
{
    /**
     * Resolves the smart mapping targets and sends WhatsApp notifications.
     */
    public function dispatchNotifications(Meeting $meeting, string $templateName = 'class_scheduled_notification'): int
    {
        $workspaceId = $meeting->workspace_id;
        $client = CloudApiClient::forWorkspace($workspaceId);

        if (!$client) {
            Log::warning('MeetingNotificationService: No active WhatsApp client for workspace', ['workspace_id' => $workspaceId]);
            return 0;
        }

        $contacts = $this->resolveContacts($meeting);
        
        if ($contacts->isEmpty()) {
            return 0;
        }

        $sentCount = 0;
        foreach ($contacts as $contact) {
            if (empty($contact->phone_e164)) {
                continue;
            }

            try {
                // You can pass template components if your template has variables
                // For example: {{1}} for Title, {{2}} for Start Time, {{3}} for Link
                $components = [
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => $meeting->title],
                            ['type' => 'text', 'text' => $meeting->start_time->format('Y-m-d H:i')],
                            ['type' => 'text', 'text' => $meeting->meet_link ?? 'TBD'],
                        ],
                    ]
                ];

                $response = $client->sendTemplate($contact->phone_e164, $templateName, 'en', $components);

                if ($response->successful()) {
                    $sentCount++;
                } else {
                    Log::error('Meeting notification failed', ['phone' => $contact->phone_e164, 'error' => $response->body()]);
                }
            } catch (\Exception $e) {
                Log::error('Meeting notification exception', ['phone' => $contact->phone_e164, 'error' => $e->getMessage()]);
            }
        }

        return $sentCount;
    }

    /**
     * Flattens all targets (Tags, Segments, Individual Contacts) into a unique Collection of Contacts.
     */
    private function resolveContacts(Meeting $meeting)
    {
        $meeting->load('targets.target');
        
        $allContacts = collect();

        foreach ($meeting->targets as $target) {
            $entity = $target->target;

            if ($entity instanceof Contact) {
                $allContacts->push($entity);
            } elseif ($entity instanceof ContactTag) {
                // Load contacts for the tag
                $allContacts = $allContacts->merge($entity->contacts);
            } elseif ($entity instanceof Segment) {
                // Load contacts for the segment
                $allContacts = $allContacts->merge($entity->contacts);
            }
        }

        // Return uniquely by contact ID
        return $allContacts->unique('id')->values();
    }
}
