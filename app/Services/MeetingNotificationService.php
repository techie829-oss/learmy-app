<?php

namespace App\Services;

use App\Models\Meeting;
use App\Modules\Shared\Models\ChannelAccount;
use App\Modules\Shared\Models\Contact;
use App\Modules\Shared\Models\ContactTag;
use App\Modules\Shared\Models\Segment;
use App\Modules\Whatsapp\Models\WhatsappTemplate;
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
        $sentCount = 0;

        // ── Individual contacts (Tags, Segments, direct) ─────────────────────
        foreach ($contacts as $contact) {
            if (empty($contact->phone_e164)) {
                continue;
            }

            try {
                if ($client) {
                    // Meta Cloud API — send template with parameters
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

        // ── Direct broadcast into WhatsApp Group chats ────────────────────────
        // This runs REGARDLESS of whether individual contacts exist, so groups
        // always receive the notification even when targets are wa_group only.
        if ($qrAccount) {
            $meeting->load('targets.target');
            foreach ($meeting->targets as $target) {
                $targetId = (string) $target->target_id;
                if ($target->target_type === 'wa_group' || str_ends_with($targetId, '@g.us')) {
                    // Target stored directly as wa_group JID
                    $this->sendToGroupViaQr($qrAccount, $targetId, $meeting, $trigger, $templateName);
                } else {
                    // Tag/Segment target — check if it has a linked WA group JID
                    $entity = $target->target;
                    if ($entity instanceof ContactTag && !empty($entity->wa_group_jid)) {
                        $this->sendToGroupViaQr($qrAccount, $entity->wa_group_jid, $meeting, $trigger, $templateName);
                    }
                }
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
     * Send a dynamic WhatsApp reminder via the QR / Baileys node service.
     * Looks up custom template from DB if configured, otherwise generates trigger-tailored rich template.
     */
    private function sendViaQr(ChannelAccount $qrAccount, Contact $contact, Meeting $meeting, string $trigger, string $templateName): int
    {
        try {
            $sessionId = $qrAccount->meta_json['session_id'] ?? 'workspace_' . $qrAccount->workspace_id . '_qr';

            $firstName = $contact->first_name ?? $contact->full_name ?? 'Student';
            $title     = $meeting->title;
            $dateTime  = $meeting->start_time->format('d M Y, h:i A');
            $meetLink  = $meeting->meet_link ?? null;

            // Check if user selected a custom template from DB
            $dbTemplate = WhatsappTemplate::where('workspace_id', $qrAccount->workspace_id)
                ->where('name', $templateName)
                ->first();

            if ($dbTemplate && !empty($dbTemplate->components)) {
                $templateData = $this->renderCustomDbTemplate($dbTemplate->components, $contact, $meeting);
            } else {
                $templateData = $this->buildDefaultQrTemplateData($trigger, $firstName, $title, $dateTime, $meetLink, $qrAccount->workspace_id);
            }

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
                    'template'  => $templateName,
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
     * Post class announcement directly into a WhatsApp Group chat.
     */
    private function sendToGroupViaQr(ChannelAccount $qrAccount, string $groupJid, Meeting $meeting, string $trigger, string $templateName): int
    {
        try {
            $sessionId = $qrAccount->meta_json['session_id'] ?? 'workspace_' . $qrAccount->workspace_id . '_qr';
            $title     = $meeting->title;
            $dateTime  = $meeting->start_time->format('d M Y, h:i A');
            $meetLink  = $meeting->meet_link ?? 'TBD';

            $dbTemplate = WhatsappTemplate::where('workspace_id', $qrAccount->workspace_id)
                ->where('name', $templateName)
                ->first();

            if ($dbTemplate && !empty($dbTemplate->components)) {
                $dummyContact = new Contact(['first_name' => 'Students', 'full_name' => 'Students']);
                $templateData = $this->renderCustomDbTemplate($dbTemplate->components, $dummyContact, $meeting);
            } else {
                $templateData = $this->buildDefaultQrTemplateData($trigger, 'Students', $title, $dateTime, $meetLink, $qrAccount->workspace_id);
            }

            $payload = [
                'sessionId' => $sessionId,
                'to'        => $groupJid,
                'template'  => $templateData,
            ];

            $response = Http::timeout(10)->post("{$this->qrServiceUrl}/api/messages/send-template", $payload);

            if ($response->successful() && $response->json('success')) {
                Log::info('[MeetingNotification] Direct group message sent to WhatsApp Group Chat.', [
                    'group_jid' => $groupJid,
                    'trigger'   => $trigger,
                    'template'  => $templateName,
                ]);
                return 1;
            }

            Log::warning('[MeetingNotification] Direct group message failed.', [
                'group_jid' => $groupJid,
                'error'     => $response->body(),
            ]);
            return 0;
        } catch (\Throwable $e) {
            Log::error('[MeetingNotification] Direct group message exception.', [
                'group_jid' => $groupJid,
                'error'     => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
    /**
     * Dynamically resolve Organization / Company Name for a workspace from DB settings or Client model.
     */
    private function getOrganizationName(int $workspaceId): string
    {
        try {
            $workspace = \App\Models\Workspace::with('client')->find($workspaceId);
            if ($workspace) {
                if ($workspace->client_id) {
                    $settingName = \App\Models\ClientSetting::get($workspace->client_id, 'organization_name')
                                ?? \App\Models\ClientSetting::get($workspace->client_id, 'company_name');
                    if (!empty($settingName)) {
                        return (string) $settingName;
                    }
                    if ($workspace->client && !empty($workspace->client->name)) {
                        return (string) $workspace->client->name;
                    }
                }
                if (!empty($workspace->name)) {
                    return (string) $workspace->name;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('[MeetingNotification] Error resolving organization name', ['error' => $e->getMessage()]);
        }

        return config('app.name', 'Learmy');
    }

    /**
     * Render a custom WhatsappTemplate from DB by replacing dynamic variables:
     * - {{1}} / {{first_name}} / {{name}} -> Student Name
     * - {{2}} / {{title}} / {{class_title}} -> Class Title
     * - {{3}} / {{start_time}} / {{date_time}} -> Class Time
     * - {{4}} / {{meet_link}} / {{link}} -> Google Meet URL
     * - {{5}} / {{description}} -> Class Description
     * - {{organization}} / {{organization_name}} / {{company}} / {{brand}} -> Organization Name from DB
     * - Any contact field or custom attribute (e.g. {{email}}, {{phone}}, {{city}}, {{course}})
     */
    private function renderCustomDbTemplate(array $components, Contact $contact, Meeting $meeting): array
    {
        $orgName     = $this->getOrganizationName($meeting->workspace_id);
        $firstName   = $contact->first_name ?? $contact->full_name ?? 'Student';
        $title       = $meeting->title;
        $dateTime    = $meeting->start_time->format('d M Y, h:i A');
        $meetLink    = $meeting->meet_link ?? 'TBD';
        $description = $meeting->description ?? '';

        $replaceVars = function (string $text) use ($contact, $firstName, $title, $dateTime, $meetLink, $description, $orgName): string {
            $replacements = [
                '{{1}} font'             => $firstName,
                '{{1}}'                  => $firstName,
                '{{first_name}}'         => $firstName,
                '{{name}}'               => $firstName,
                '{{student_name}}'       => $firstName,
                '{{2}}'                  => $title,
                '{{title}}'              => $title,
                '{{class_title}}'        => $title,
                '{{3}}'                  => $dateTime,
                '{{start_time}}'         => $dateTime,
                '{{date_time}}'          => $dateTime,
                '{{time}}'               => $dateTime,
                '{{4}}'                  => $meetLink,
                '{{meet_link}}'          => $meetLink,
                '{{link}}'               => $meetLink,
                '{{5}}'                  => $description,
                '{{description}}'        => $description,
                '{{organization}}'       => $orgName,
                '{{organization_name}}'  => $orgName,
                '{{company}}'            => $orgName,
                '{{brand}}'              => $orgName,
                '{{app_name}}'           => $orgName,
                '{{email}}'              => $contact->email ?? '',
                '{{phone}}'              => $contact->phone_e164 ?? '',
                '{{last_name}}'          => $contact->last_name ?? '',
            ];

            // Merge any custom attributes attached to contact (e.g. city, course, fee, roll_no)
            if (!empty($contact->custom_attributes) && is_array($contact->custom_attributes)) {
                foreach ($contact->custom_attributes as $key => $val) {
                    $replacements["{{{$key}}}"] = is_scalar($val) ? (string)$val : '';
                }
            }

            return strtr($text, $replacements);
        };

        $header  = null;
        $body    = '';
        $footer  = "{$orgName} Education Platform";
        $buttons = [];

        foreach ($components as $comp) {
            $type = $comp['type'] ?? '';

            if ($type === 'HEADER') {
                $format = $comp['format'] ?? 'TEXT';
                if ($format === 'TEXT') {
                    $header = ['type' => 'text', 'text' => $replaceVars($comp['text'] ?? '')];
                } elseif (in_array($format, ['IMAGE', 'VIDEO']) && !empty($comp['url'])) {
                    $header = ['type' => strtolower($format), 'url' => $comp['url']];
                }
            } elseif ($type === 'BODY') {
                $body = $replaceVars($comp['text'] ?? '');
            } elseif ($type === 'FOOTER') {
                $footer = $replaceVars($comp['text'] ?? $footer);
            } elseif ($type === 'BUTTONS') {
                foreach ($comp['buttons'] ?? [] as $btn) {
                    $btnType = $btn['type'] ?? '';
                    if ($btnType === 'URL') {
                        $url = $replaceVars($btn['url'] ?? $meetLink);
                        $buttons[] = ['type' => 'url', 'text' => $btn['text'] ?? 'Open Link', 'value' => $url];
                    } elseif ($btnType === 'PHONE_NUMBER') {
                        $phone = $btn['phone_number'] ?? '';
                        $buttons[] = ['type' => 'call', 'text' => $btn['text'] ?? 'Call', 'value' => $phone];
                    } else {
                        $buttons[] = ['type' => 'quickReply', 'text' => $btn['text'] ?? 'Reply'];
                    }
                }
            }
        }

        // Default header if none defined in DB template
        if (!$header) {
            $header = ['type' => 'text', 'text' => "📅 Class Reminder — {$orgName}"];
        }

        // Add Meet Link button if not present in custom template buttons
        if ($meetLink && $meetLink !== 'TBD' && empty(array_filter($buttons, fn($b) => $b['type'] === 'url'))) {
            array_unshift($buttons, ['type' => 'url', 'text' => '🔗 Join Class Now', 'value' => $meetLink]);
        }

        return [
            'header'  => $header,
            'body'    => $body,
            'footer'  => $footer,
            'buttons' => $buttons,
        ];
    }

    /**
     * Built-in dynamic fallback templates for each trigger timing.
     */
    private function buildDefaultQrTemplateData(string $trigger, string $firstName, string $title, string $dateTime, ?string $meetLink, int $workspaceId = 0): array
    {
        $orgName = $workspaceId ? $this->getOrganizationName($workspaceId) : config('app.name', 'Learmy');
        $footer  = "{$orgName} Education Platform";

        switch ($trigger) {
            case 'morning':
                $header = ['type' => 'text', 'text' => "🌅 Today's Class Reminder — {$orgName}"];
                $body   = "Namaste *{$firstName}* ji! ☀️\n\n"
                        . "Aaj aapki *{$orgName}* class scheduled hai:\n\n"
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
                $header = ['type' => 'text', 'text' => "⏰ Class Starting in 15 Minutes — {$orgName}"];
                $body   = "Namaste *{$firstName}* ji! ⏳\n\n"
                        . "Aapki *{$orgName}* class bas *15 minute* mein shuru hone wali hai:\n\n"
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
                $header = ['type' => 'text', 'text' => "🔴 Class is LIVE Now — {$orgName}"];
                $body   = "Namaste *{$firstName}* ji! 🔴 LIVE\n\n"
                        . "Aapki *{$orgName}* class *LIVE* shuru ho chuki hai!\n\n"
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
                $header = ['type' => 'text', 'text' => "📅 New Class Scheduled — {$orgName}"];
                $body   = "Namaste *{$firstName}* ji! 👋\n\n"
                        . "Aapki new *{$orgName}* class schedule ho gayi hai:\n\n"
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
