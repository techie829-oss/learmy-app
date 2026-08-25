<?php

namespace App\Services;

use App\Models\Meeting;
use App\Modules\Shared\Models\ChannelAccount;
use App\Modules\Shared\Models\Contact;
use App\Modules\Shared\Models\ContactTag;
use App\Modules\Shared\Models\Segment;
use App\Modules\Whatsapp\Services\CloudApiClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MeetingNotificationService
{
    private string $qrServiceUrl;

    public function __construct()
    {
        $this->qrServiceUrl = config('services.whatsapp_qr.url', 'http://127.0.0.1:3001');
    }

    /**
     * Dispatch WhatsApp notifications for a meeting to all resolved contacts.
     * Uses meeting->whatsapp_template if set, otherwise falls back to 'class_scheduled_notification'.
     *
     * Returns: ['whatsapp_connected' => bool, 'contacts_count' => int, 'sent_count' => int]
     */
    public function dispatchNotifications(Meeting $meeting): array
    {
        // Skip if user disabled notification
        if ($meeting->send_whatsapp_notification === false) {
            return ['whatsapp_connected' => true, 'contacts_count' => 0, 'sent_count' => 0];
        }

        $workspaceId  = $meeting->workspace_id;
        $templateName = $meeting->whatsapp_template ?: 'class_scheduled_notification';

        $client = CloudApiClient::forWorkspace($workspaceId);

        $qrAccount = ChannelAccount::where('workspace_id', $workspaceId)
            ->where('provider', 'qr_baileys')
            ->where('status', 'active')
            ->first();

        if (! $client && ! $qrAccount) {
            Log::warning('[MeetingNotification] No WhatsApp client or QR session.', compact('workspaceId'));
            return ['whatsapp_connected' => false, 'contacts_count' => 0, 'sent_count' => 0];
        }

        $contacts = $this->resolveContacts($meeting);

        if ($contacts->isEmpty()) {
            return ['whatsapp_connected' => true, 'contacts_count' => 0, 'sent_count' => 0];
        }

        $sentCount = 0;

        foreach ($contacts as $contact) {
            if (empty($contact->phone_e164)) {
                continue;
            }

            try {
                if ($client) {
                    // Meta Cloud API — send approved template
                    $components = [[
                        'type'       => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => $contact->first_name ?? $contact->full_name],
                            ['type' => 'text', 'text' => $meeting->title],
                            ['type' => 'text', 'text' => $meeting->start_time->format('d M Y, h:i A')],
                            ['type' => 'text', 'text' => $meeting->meet_link ?? 'TBD'],
                        ],
                    ]];

                    $response = $client->sendTemplate($contact->phone_e164, $templateName, 'en', $components);

                    if ($response->successful()) {
                        $sentCount++;
                    } else {
                        Log::warning('[MeetingNotification] Meta template failed, falling back to QR.', [
                            'phone' => $contact->phone_e164,
                            'error' => $response->body(),
                        ]);
                        if ($qrAccount) {
                            $sentCount += $this->sendViaQr($qrAccount, $contact, $meeting);
                        }
                    }
                } elseif ($qrAccount) {
                    $sentCount += $this->sendViaQr($qrAccount, $contact, $meeting);
                }
            } catch (\Throwable $e) {
                Log::error('[MeetingNotification] Exception sending to contact.', [
                    'phone' => $contact->phone_e164,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'whatsapp_connected' => true,
            'contacts_count'     => $contacts->count(),
            'sent_count'         => $sentCount,
        ];
    }

    /**
     * Flatten all meeting targets (Tags, Segments, individual Contacts) into unique Contact collection.
     */
    public function resolveContacts(Meeting $meeting)
    {
        $meeting->load('targets.target');

        $allContacts = collect();

        foreach ($meeting->targets as $target) {
            $entity = $target->target;

            if ($entity instanceof Contact) {
                $allContacts->push($entity);
            } elseif ($entity instanceof ContactTag) {
                $allContacts = $allContacts->merge($entity->contacts);
            } elseif ($entity instanceof Segment) {
                $allContacts = $allContacts->merge($entity->contacts);
            }
        }

        return $allContacts->unique('id')->values();
    }

    /**
     * Send a rich WhatsApp reminder via the QR / Baileys node service.
     * Uses the /api/messages/send-template endpoint for header + body + footer + buttons.
     */
    private function sendViaQr(ChannelAccount $qrAccount, Contact $contact, Meeting $meeting): int
    {
        try {
            $sessionId = $qrAccount->meta_json['session_id'] ?? 'workspace_' . $qrAccount->workspace_id . '_qr';

            $firstName   = $contact->first_name ?? $contact->full_name ?? 'Student';
            $title       = $meeting->title;
            $dateTime    = $meeting->start_time->format('d M Y, h:i A');
            $meetLink    = $meeting->meet_link ?? null;

            $body = "Namaste *{$firstName}* ji! 👋\n\n"
                  . "Aapki upcoming class ka reminder:\n\n"
                  . "📚 *{$title}*\n"
                  . "🕒 *Time:* {$dateTime}\n"
                  . ($meetLink ? "🔗 *Join:* {$meetLink}\n" : '')
                  . "\nPlease time par join karein! ✅";

            $buttons = [];
            if ($meetLink) {
                $buttons[] = ['type' => 'url', 'text' => '🔗 Join Class Now', 'value' => $meetLink];
            }
            $buttons[] = ['type' => 'quickReply', 'text' => '✅ Will Join'];
            $buttons[] = ['type' => 'quickReply', 'text' => '❌ Cannot Attend'];

            $payload = [
                'sessionId' => $sessionId,
                'to'        => $contact->phone_e164,
                'template'  => [
                    'header'  => ['type' => 'text', 'text' => "📅 Class Reminder — Learmy"],
                    'body'    => $body,
                    'footer'  => 'Learmy Education Platform | Reply STOP to unsubscribe',
                    'buttons' => $buttons,
                ],
            ];

            $response = Http::timeout(10)->post("{$this->qrServiceUrl}/api/messages/send-template", $payload);

            if ($response->successful() && $response->json('success')) {
                Log::info('[MeetingNotification] QR message sent.', [
                    'phone'     => $contact->phone_e164,
                    'messageId' => $response->json('messageId'),
                ]);
                return 1;
            }

            Log::warning('[MeetingNotification] QR send failed.', [
                'phone' => $contact->phone_e164,
                'error' => $response->body(),
            ]);
            return 0;

        } catch (\Throwable $e) {
            Log::error('[MeetingNotification] QR exception.', ['error' => $e->getMessage()]);
            return 0;
        }
    }
}
