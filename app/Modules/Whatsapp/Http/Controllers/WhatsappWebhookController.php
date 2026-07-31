<?php

namespace App\Modules\Whatsapp\Http\Controllers;

use App\Http\Controllers\Concerns\FlushesWebhookResponse;
use App\Http\Controllers\Controller;
use App\Modules\Integrations\Services\CredentialResolver;
use App\Modules\Whatsapp\Jobs\ProcessInboundMessageJob;
use App\Modules\Whatsapp\Models\WhatsappBusinessAccount;
use App\Services\WebhookIdempotencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WhatsappWebhookController extends Controller
{
    use FlushesWebhookResponse;

    private const HASH_ALGO               = 'sha256';
    private const GLOBAL_VERIFY_SALT      = 'wh_global_verify';
    private const QUEUE_NAME              = 'whatsapp';
    private const IDEMPOTENCY_NS_PER_WABA = 'whatsapp';
    private const IDEMPOTENCY_NS_GLOBAL   = 'whatsapp_global';

    // ── GET /webhooks/whatsapp/global ────────────────────────────────────────

    public function verifyGlobal(Request $request): Response
    {
        $expectedToken = $this->globalVerifyToken();
        if (! $expectedToken) {
            abort(403, 'Meta credentials not configured');
        }
        if ($request->input('hub_mode') === 'subscribe'
            && hash_equals($expectedToken, $request->string('hub_verify_token')->toString())) {
            return response($request->input('hub_challenge', ''), 200);
        }
        abort(400);
    }

    // ── GET /webhooks/whatsapp/{token} ───────────────────────────────────────

    public function verify(Request $request, string $token): Response
    {
        $waba = WhatsappBusinessAccount::findByWebhookToken($token);
        if (! $waba) {
            abort(403, 'Invalid verify token');
        }
        if ($request->input('hub_mode') === 'subscribe'
            && hash_equals($token, $request->string('hub_verify_token')->toString())) {
            return response($request->input('hub_challenge', ''), 200);
        }
        abort(400);
    }

    // ── POST /webhooks/whatsapp/global ───────────────────────────────────────

    public function receiveGlobal(Request $request): JsonResponse
    {
        $meta      = CredentialResolver::system()->meta();
        $appSecret = $meta?->appSecret();

        if ($appSecret) {
            $this->verifyHmacSignature($request, $appSecret);
        } elseif (app()->environment('production')) {
            Log::critical('whatsapp.webhook.global.no_secret', ['ip' => $request->ip()]);
            abort(401, 'App secret not configured');
        } else {
            Log::warning('whatsapp.webhook.global.unsigned', ['ip' => $request->ip()]);
        }

        $newEntries = $this->deduplicateEntries($request, self::IDEMPOTENCY_NS_GLOBAL);
        if (empty($newEntries)) {
            return response()->json(['status' => 'ok']);
        }

        $payload          = $request->all();
        $payload['entry'] = $newEntries;

        Log::info('whatsapp.webhook.global.received', [
            'entry_count'  => count($newEntries),
            'waba_ids'     => collect($newEntries)->pluck('id')->all(),
            'has_messages' => $this->entriesHaveField($newEntries, 'messages'),
            'has_statuses' => $this->entriesHaveField($newEntries, 'statuses'),
        ]);

        return $this->flushWebhookOkThen(
            fn () => ProcessInboundMessageJob::dispatch($payload, '')->onQueue(self::QUEUE_NAME)
        );
    }

    // ── POST /webhooks/whatsapp/{token} ──────────────────────────────────────

    public function receive(Request $request, string $token): JsonResponse
    {
        $waba = WhatsappBusinessAccount::findByWebhookToken($token);
        if (! $waba) {
            Log::warning('whatsapp.webhook.unknown_token', [
                'ip'             => $request->ip(),
                'received_token' => substr($token, 0, 12) . '…',
                'token_hash'     => hash(self::HASH_ALGO, $token),
                'hint'           => 'Token hash does not match any webhook_verify_token_hash in whatsapp_business_accounts.',
            ]);
            abort(403, 'Invalid verify token');
        }

        $appSecret = ($waba->credentials ?? [])['app_secret_override'] ?? null;
        if (! $appSecret) {
            $appSecret = CredentialResolver::system()->meta()?->appSecret();
        }

        if ($appSecret) {
            $this->verifyHmacSignature($request, $appSecret);
        } elseif (app()->environment('production')) {
            Log::critical('whatsapp.webhook.no_secret', ['workspace_id' => $waba->workspace_id]);
            abort(401, 'App secret not configured');
        } else {
            Log::warning('whatsapp.webhook.unsigned', ['workspace_id' => $waba->workspace_id]);
        }

        $newEntries = $this->deduplicateEntries($request, self::IDEMPOTENCY_NS_PER_WABA);
        if (empty($newEntries)) {
            return response()->json(['status' => 'ok']);
        }

        $payload          = $request->all();
        $payload['entry'] = $newEntries;

        Log::info('whatsapp.webhook.received', [
            'workspace_id' => $waba->workspace_id,
            'waba_id'      => $waba->waba_id,
            'entry_count'  => count($newEntries),
            'has_messages' => $this->entriesHaveField($newEntries, 'messages'),
            'has_statuses' => $this->entriesHaveField($newEntries, 'statuses'),
        ]);

        return $this->flushWebhookOkThen(
            fn () => ProcessInboundMessageJob::dispatch($payload, $token)->onQueue(self::QUEUE_NAME)
        );
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function globalVerifyToken(): ?string
    {
        $meta = CredentialResolver::system()->meta();
        if (! $meta?->appId() || ! $meta->appSecret()) {
            return null;
        }
        return hash(self::HASH_ALGO, $meta->appId() . $meta->appSecret() . self::GLOBAL_VERIFY_SALT);
    }

    /**
     * Parse `entry` from request safely (guards against `"entry":"string"` fatal
     * foreach error) and apply idempotency deduplication.
     *
     * @return array<int, array<string, mixed>>
     */
    private function deduplicateEntries(Request $request, string $namespace): array
    {
        $rawEntries = $request->input('entry');
        $entries    = is_array($rawEntries) ? $rawEntries : [];

        $idempotency = app(WebhookIdempotencyService::class);
        $newEntries  = [];

        foreach ($entries as $entry) {
            if (! is_array($entry)) {
                continue;
            }
            $eventKey = $this->entryEventKey($entry);
            if ($eventKey === null || $idempotency->isNewEvent($namespace, $eventKey)) {
                $newEntries[] = $entry;
            }
        }

        return $newEntries;
    }

    /**
     * Check whether any entry — across ALL changes, not just index 0 — carries
     * the given field (e.g. 'messages' or 'statuses').
     *
     * @param  array<int, array<string, mixed>>  $entries
     */
    private function entriesHaveField(array $entries, string $field): bool
    {
        foreach ($entries as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                if (! empty($change['value'][$field] ?? [])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Build a stable idempotency key for a webhook entry from the actual events
     * it carries (message ids, status transitions).
     *
     * WhatsApp sets `entry.id` to the WABA id — identical for every webhook
     * from that account — so it must NEVER be used as the dedup key.
     *
     * Returns null when the entry carries no identifiable event (caller
     * processes it fail-open rather than dropping it).
     *
     * @param  array<string, mixed>  $entry
     */
    private function entryEventKey(array $entry): ?string
    {
        $parts = [];

        foreach ($entry['changes'] ?? [] as $change) {
            $value = $change['value'] ?? [];

            foreach ($value['messages'] ?? [] as $message) {
                if (! empty($message['id'])) {
                    $parts[] = 'm:' . $message['id'];
                }
            }

            foreach ($value['statuses'] ?? [] as $status) {
                if (! empty($status['id'])) {
                    $parts[] = 's:' . $status['id'] . ':' . ($status['status'] ?? '');
                }
            }
        }

        if ($parts !== []) {
            sort($parts);
            return hash(self::HASH_ALGO, implode('|', $parts));
        }

        $blob = json_encode($entry['changes'] ?? [], JSON_UNESCAPED_UNICODE);
        if ($blob === false || $blob === '[]' || $blob === 'null') {
            return null;
        }

        return ($entry['id'] ?? 'waba') . ':' . hash(self::HASH_ALGO, $blob);
    }

    /**
     * Verify the X-Hub-Signature-256 header using timing-safe comparison.
     * Aborts with 401 on mismatch.
     */
    private function verifyHmacSignature(Request $request, string $appSecret): void
    {
        $expected = 'sha256=' . hash_hmac(self::HASH_ALGO, $request->getContent(), $appSecret);
        $received = $request->header('X-Hub-Signature-256', '');

        if (! hash_equals($expected, $received)) {
            Log::warning('whatsapp.webhook.signature_mismatch', [
                'ip'       => $request->ip(),
                'path'     => $request->path(),
                'object'   => $request->input('object'),
                'body_len' => strlen($request->getContent()),
                'expected' => substr($expected, 0, 20) . '…',
                'received' => substr($received, 0, 20) . '…',
            ]);
            abort(401, 'Invalid signature');
        }
    }
}
