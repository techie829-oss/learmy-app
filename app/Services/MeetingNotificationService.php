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
     * Dispatch WhatsApp notifications for a meeting for a specific trigger.
     * Trigger types: 'on_create', 'morning', 'before_15m', 'on_start'
     *
     * Returns: ['whatsapp_connected' => bool, 'contacts_count' => int, 'sent_count' => int]
     */
    public function dispatchNotifications(Meeting $meeting, string $trigger = 'on_create'): array
    {
        // Check if master toggle or specific trigger is disabled
        if (!$meeting->isReminderEnabled($trigger)) {
            return ['whatsapp_connected' => true, 'contacts_count' => 0, 'sent_count' => 0];
        }

        $workspaceId  = $meeting->workspace_id;
        $templateName = $meeting->getReminderTemplate($trigger);

        $client = CloudApiClient::forWorkspace($workspaceId);

        $qrAccount = ChannelAccount::where('workspace_id', $workspaceId)
            ->where('provider', 'qr_baileys')
            ->where('status', 'active')
            ->first();

        if (!$client && !$qrAccount) {
            Log::warning('[MeetingNotification] No WhatsApp client or QR session.', compact('workspaceId', 'trigger'));
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
                    // Meta Cloud API — send template
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
                        Log::warning('[MeetingNotification] Meta template failed, trying QR fallback.', [
                            'phone'   => $contact->phone_e164,
                            'trigger' => $trigger,
                            'error'   => $response->body(),
                        ]);
                        if ($qrAccount) {
                            $sentCount += $this->sendViaQr($qrAccount, $contact, $meeting, $trigger, $templateName);
                        }
                    }
                } elseif ($qrAccount) {
                    $sentCount += $this->sendViaQr($qrAccount, $contact, $meeting, $trigger, $templateName);
                }
            } catch (\Throwable $e) {
                Log::error('[MeetingNotification] Exception sending to contact.', [
                    'phone'   => $contact->phone_e164,
                    'trigger' => $trigger,
                    'error'   => $e->getMessage(),
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
     * Send a dynamic WhatsApp reminder via the QR / Baileys node service tailored to the trigger.
     */
    private function sendViaQr(ChannelAccount $qrAccount, Contact $contact, Meeting $meeting, string $trigger, string $templateName): int
    {
        try {
            $sessionId = $qrAccount->meta_json['session_id'] ?? 'workspace_' . $qrAccount->workspace_id . '_qr';

            $firstName = $contact->first_name ?? $contact->full_name ?? 'Student';
            $title     = $meeting->title;
            $dateTime  = $meeting->start_time->format('d M Y, h:i A');
            $meetLink  = $meeting->meet_link ?? null;

            // Generate Trigger-Specific Message Content
            $templateData = $this->buildQrTemplateData($trigger, $firstName, $title, $dateTime, $meetLink, $templateName);

            $payload = [
                'sessionId' => $sessionId,
                'to'        => $contact->phone_e164,
                'template'  => $templateData,
            ];

            $response = Http::timeout(10)->post("{$this->qrServiceUrl}/api/messages/send-template", $payload);

            if ($response->successful() && $response->json('success')) {
                Log::info('[MeetingNotification] QR message sent.', [
                    'phone'     => $contact->phone_e164,
                    'trigger'   => $trigger,
                    'messageId' => $response->json('messageId'),
                ]);
                return 1;
            }

            Log::warning('[MeetingNotification] QR send failed.', [
                'phone'   => $contact->phone_e164,
                'trigger' => $trigger,
                'error'   => $response->body(),
            ]);
            return 0;

        } catch (\Throwable $e) {
            Log::error('[MeetingNotification] QR exception.', ['trigger' => $trigger, 'error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Build rich template structure (Header, Body, Footer, Buttons) tailored to trigger.
     */
    private function buildQrTemplateData(string $trigger, string $firstName, string $title, string $dateTime, ?string $meetLink, string $templateName): array
    {
        $footer = 'Learmy Education Platform | learmy.solidrix.com';

        switch ($trigger) {
            case 'morning':
                $header = ['type' => 'text', 'text' => "🌅 Today's Class Reminder — Learmy"];
                $body   = "Namaste *{$firstName}* ji! ☀️\n\n"
                        . "Aaj aapki *Learmy* class scheduled hai:\n\n"
                        . "📚 *{$title}*\n"
                        . "🕒 *Time:* {$dateTime}\n"
                        . ($meetLink ? "🔗 *Join:* {$meetLink}\n" : '')
                        . "\nTime par tayyar rahein! 🎯";
                $buttons = [];
                if ($meetLink) {
                    $buttons[] = ['type' => 'url', 'text' => '🔗 Join Link', 'value' => $meetLink];
                }
                $buttons[] = ['type' => 'quickReply', 'text' => '👍 Ready Hoon'];
                break;

            case 'before_15m':
                $header = ['type' => 'text', 'text' => "⏰ Class Starting in 15 Minutes!"];
                $body   = "Namaste *{$firstName}* ji! ⏳\n\n"
                        . "Aapki class bas *15 minute* mein shuru hone wali hai:\n\n"
                        . "📚 *{$title}*\n"
                        . "🕒 *Time:* {$dateTime}\n"
                        . ($meetLink ? "🔗 *Join:* {$meetLink}\n" : '')
                        . "\nAbhi ready ho jaayein aur join karein! ⚡";
                $buttons = [];
                if ($meetLink) {
                    $buttons[] = ['type' => 'url', 'text' => '🔗 Join Class Now', 'value' => $meetLink];
                }
                $buttons[] = ['type' => 'quickReply', 'text' => '🚀 Joining Now'];
                break;

            case 'on_start':
                $header = ['type' => 'text', 'text' => "🔴 Class is LIVE Now!"];
                $body   = "Namaste *{$firstName}* ji! 🔴 LIVE\n\n"
                        . "Aapki class *LIVE* shuru ho chuki hai!\n\n"
                        . "📚 *{$title}*\n"
                        . ($meetLink ? "🔗 *Join Immediately:* {$meetLink}\n" : '')
                        . "\nDelay mat karein — abhi join karein! 🚀";
                $buttons = [];
                if ($meetLink) {
                    $buttons[] = ['type' => 'url', 'text' => '🚀 Join LIVE Class', 'value' => $meetLink];
                }
                $buttons[] = ['type' => 'quickReply', 'text' => '✅ Joined'];
                break;

            case 'on_create':
            default:
                $header = ['type' => 'text', 'text' => "📅 New Class Scheduled — Learmy"];
                $body   = "Namaste *{$firstName}* ji! 👋\n\n"
                        . "Aapki new class schedule ho gayi hai:\n\n"
                        . "📚 *{$title}*\n"
                        . "🕒 *Time:* {$dateTime}\n"
                        . ($meetLink ? "🔗 *Join:* {$meetLink}\n" : '')
                        . "\nPlease calendar mein date mark kar lein! 🗓️";
                $buttons = [];
                if ($meetLink) {
                    $buttons[] = ['type' => 'url', 'text' => '🔗 Class Link', 'value' => $meetLink];
                }
                $buttons[] = ['type' => 'quickReply', 'text' => '✅ Attend Karunga'];
                break;
        }

        return [
            'header'  => $header,
            'body'    => $body,
            'footer'  => $footer,
            'buttons' => $buttons,
        ];
    }
}
