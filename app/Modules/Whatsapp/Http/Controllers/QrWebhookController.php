<?php

namespace App\Modules\Whatsapp\Http\Controllers;

use App\Events\MessageReceived;
use App\Http\Controllers\Controller;
use App\Modules\Shared\Models\ChannelAccount;
use App\Modules\Shared\Models\Conversation;
use App\Modules\Shared\Models\Message;
use App\Modules\Shared\Services\ContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QrWebhookController extends Controller
{
    public function __construct(
        private readonly ContactService $contactService
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $expectedSecret = env('WA_QR_SECRET', 'learmy_qr_sec_99812');
        $incomingSecret = $request->header('X-Learmy-QR-Secret');

        if ($expectedSecret && $incomingSecret !== $expectedSecret) {
            return response()->json(['error' => 'Unauthorized secret token'], 403);
        }

        $payload = $request->all();
        $sessionId = $payload['session_id'] ?? null;
        $event = $payload['event'] ?? null;
        $data = $payload['data'] ?? [];

        if (! $sessionId || ! $event) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        // Locate corresponding ChannelAccount by session_id in meta_json
        $channelAccount = ChannelAccount::where('channel', 'whatsapp')
            ->where('meta_json->session_id', $sessionId)
            ->first();

        if (! $channelAccount) {
            Log::info('QR Webhook received for unlinked session', ['session_id' => $sessionId, 'event' => $event]);

            return response()->json(['status' => 'ignored']);
        }

        match ($event) {
            'connected' => $this->handleConnected($channelAccount, $data),
            'qr_updated' => $this->handleQrUpdated($channelAccount, $data),
            'disconnected' => $this->handleDisconnected($channelAccount, $data),
            'inbound_message' => $this->handleInboundMessage($channelAccount, $data),
            'lid_resolved' => $this->handleLidResolved($channelAccount, $data),
            default => null,
        };

        return response()->json(['status' => 'success']);
    }

    private function handleConnected(ChannelAccount $account, array $data): void
    {
        $meta = $account->meta_json ?? [];
        $meta['qr_code'] = null;
        $meta['connected_phone'] = $data['phone'] ?? null;
        $meta['user_info'] = $data['user'] ?? null;

        $account->update([
            'status' => 'active',
            'display_name' => $data['phone'] ? ("WhatsApp (+{$data['phone']})") : $account->display_name,
            'meta_json' => $meta,
        ]);
    }

    private function handleQrUpdated(ChannelAccount $account, array $data): void
    {
        $meta = $account->meta_json ?? [];
        $meta['qr_code'] = $data['qr'] ?? null;

        $account->update([
            'status' => 'inactive',
            'meta_json' => $meta,
        ]);
    }

    private function handleDisconnected(ChannelAccount $account, array $data): void
    {
        $meta = $account->meta_json ?? [];
        $meta['qr_code'] = null;

        $account->update([
            'status' => 'inactive',
            'meta_json' => $meta,
        ]);
    }

    private function handleInboundMessage(ChannelAccount $account, array $data): void
    {
        $baileysMsg = $data['message'] ?? [];
        $msgKey = $baileysMsg['key'] ?? [];

        $fromJid = $msgKey['remoteJid'] ?? '';
        $cleanPhone = !empty($data['phone']) ? $data['phone'] : (explode('@', $fromJid)[0] ?? '');
        if (empty($cleanPhone)) {
            return;
        }

        $workspaceId = $account->workspace_id;

        // Check if $cleanPhone is a WhatsApp LID (Linked Identity ID e.g., 14+ digits or starting with 3249/2683)
        $isLid = strlen($cleanPhone) >= 14 || str_starts_with($cleanPhone, '3249') || str_ends_with($fromJid, '@lid');

        if ($isLid) {
            // Never create a new fake contact for LID! Map to existing real phone contact in workspace
            $contact = \App\Modules\Shared\Models\Contact::where('workspace_id', $workspaceId)
                ->where('phone_e164', 'not like', '+3249%')
                ->whereRaw('LENGTH(phone_e164) <= 14')
                ->orderByDesc('updated_at')
                ->first();

            if (! $contact) {
                $connPhone = $account->meta_json['connected_phone'] ?? null;
                $phoneE164 = $connPhone ? (str_starts_with($connPhone, '+') ? $connPhone : '+'.$connPhone) : (str_starts_with($cleanPhone, '+') ? $cleanPhone : '+'.$cleanPhone);
                $contact = $this->contactService->upsert($workspaceId, [
                    'phone_e164' => $phoneE164,
                    'opt_in_whatsapp' => true,
                    'source' => 'whatsapp_qr_inbound',
                ]);
            }
        } else {
            $phoneE164 = str_starts_with($cleanPhone, '+') ? $cleanPhone : '+'.$cleanPhone;
            $contact = $this->contactService->upsert($workspaceId, [
                'phone_e164' => $phoneE164,
                'opt_in_whatsapp' => true,
                'source' => 'whatsapp_qr_inbound',
            ]);
        }

        // Single unified conversation per contact & channel_account
        $conversation = Conversation::where('workspace_id', $workspaceId)
            ->where('contact_id', $contact->id)
            ->where('channel_account_id', $account->id)
            ->first();

        if (! $conversation) {
            $conversation = Conversation::create([
                'workspace_id' => $workspaceId,
                'contact_id' => $contact->id,
                'channel_account_id' => $account->id,
                'status' => 'open',
                'external_thread_id' => $contact->phone_e164,
            ]);
        }

        // Extract body text
        $msgContent = $baileysMsg['message'] ?? [];
        $body = $msgContent['conversation']
            ?? $msgContent['extendedTextMessage']['text']
            ?? $msgContent['imageMessage']['caption']
            ?? $msgContent['videoMessage']['caption']
            ?? '[Media Message]';

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'direction' => 'in',
            'channel' => 'whatsapp',
            'type' => 'text',
            'payload' => $baileysMsg,
            'body' => $body,
            'status' => 'delivered',
            'provider_message_id' => $msgKey['id'] ?? null,
            'sent_by' => 'human',
            'sent_at' => now(),
        ]);

        $conversation->update([
            'last_message_at' => $message->sent_at,
            'status' => 'open',
            'unread_count' => $conversation->unread_count + 1,
            'last_inbound_at' => $message->sent_at,
        ]);

        // Dispatch event for Automations & AI Chatbot
        MessageReceived::dispatch($message);
    }

    /**
     * When WhatsApp resolves a LID → real phone number,
     * find contacts stored under the LID and update them to the real phone.
     */
    private function handleLidResolved(ChannelAccount $account, array $data): void
    {
        $lid = $data['lid'] ?? null;
        $phone = $data['phone'] ?? null;
        $name = $data['name'] ?? null;

        if (! $lid || ! $phone) {
            return;
        }

        $workspaceId = $account->workspace_id;
        $realPhone = str_starts_with($phone, '+') ? $phone : '+'.$phone;
        $lidPhone = str_starts_with($lid, '+') ? $lid : '+'.$lid;

        Log::info('[LID Resolved] Merging contact', [
            'lid' => $lidPhone,
            'real_phone' => $realPhone,
            'workspace' => $workspaceId,
        ]);

        // Find contact stored under LID
        $lidContact = Contact::where('workspace_id', $workspaceId)
            ->where('phone_e164', $lidPhone)
            ->first();

        // Upsert real phone contact
        $realContact = $this->contactService->upsert($workspaceId, [
            'phone_e164' => $realPhone,
            'opt_in_whatsapp' => true,
            'source' => 'whatsapp_qr_inbound',
            'first_name' => $name ?? null,
        ]);

        if ($lidContact && $realContact && $lidContact->id !== $realContact->id) {
            $primaryConv = Conversation::where('workspace_id', $workspaceId)
                ->where('contact_id', $realContact->id)
                ->where('channel_account_id', $account->id)
                ->first();

            $lidConvs = Conversation::where('workspace_id', $workspaceId)
                ->where('contact_id', $lidContact->id)
                ->get();

            foreach ($lidConvs as $lidConv) {
                if ($primaryConv && $lidConv->id !== $primaryConv->id) {
                    Message::where('conversation_id', $lidConv->id)
                        ->update(['conversation_id' => $primaryConv->id]);
                    $lidConv->delete();
                } else {
                    $lidConv->update(['contact_id' => $realContact->id]);
                }
            }

            // Delete the LID contact
            $lidContact->delete();

            Log::info('[LID Resolved] Merged conversations into primary', [
                'from_contact' => $lidContact->id,
                'to_contact' => $realContact->id,
                'primary_conv' => $primaryConv?->id,
            ]);
        }
    }
}
