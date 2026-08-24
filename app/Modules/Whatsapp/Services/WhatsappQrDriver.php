<?php

namespace App\Modules\Whatsapp\Services;

use App\Modules\Shared\Contracts\ChannelDriverInterface;
use App\Modules\Shared\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappQrDriver implements ChannelDriverInterface
{
    private string $serviceUrl;

    public function __construct()
    {
        $this->serviceUrl = config('services.whatsapp_qr.url', 'http://127.0.0.1:3001');
    }

    public function send(Message $message): string
    {
        $conversation = $message->conversation;
        $contact = $conversation->contact;
        $phone = $contact->phone_e164;
        $channelAccount = $conversation->channelAccount;

        $sessionId = $channelAccount?->meta_json['session_id'] ?? ('workspace_'.$conversation->workspace_id.'_acc_'.$channelAccount?->id);

        $payload = [
            'sessionId' => $sessionId,
            'to' => $phone,
            'text' => $message->body ?? '',
        ];

        $messagePayload = $message->payload ?? [];
        if (! empty($messagePayload['link']) || ! empty($messagePayload['url'])) {
            $payload['mediaUrl'] = $messagePayload['link'] ?? $messagePayload['url'];
            $payload['mediaType'] = $message->type;
            $payload['caption'] = $messagePayload['caption'] ?? $message->body;
        }

        $response = Http::post("{$this->serviceUrl}/api/messages/send", $payload);

        if (! $response->successful()) {
            Log::error('WhatsApp QR Send Failed', ['response' => $response->body()]);
            throw new \RuntimeException('WhatsApp QR Send Failed: '.$response->body());
        }

        return $response->json('messageId', 'qr_msg_'.time());
    }

    public function startSession(string $sessionId): array
    {
        $response = Http::post("{$this->serviceUrl}/api/sessions/start", [
            'sessionId' => $sessionId,
        ]);

        return $response->json() ?? [];
    }

    public function getSessionStatus(string $sessionId): array
    {
        $response = Http::get("{$this->serviceUrl}/api/sessions/status/{$sessionId}");

        return $response->json() ?? [];
    }

    public function logoutSession(string $sessionId): bool
    {
        $response = Http::post("{$this->serviceUrl}/api/sessions/logout", [
            'sessionId' => $sessionId,
        ]);

        return $response->successful();
    }

    public function receiveWebhook(Request $request): array
    {
        return [];
    }

    public function verifyCreds(): bool
    {
        return true;
    }
}
