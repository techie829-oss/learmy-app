<?php

namespace App\Modules\Whatsapp\Http\Controllers;

use App\Modules\Shared\Models\ChannelAccount;
use App\Modules\Shared\Models\Contact;
use App\Modules\Shared\Models\ContactTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;

class WhatsappGroupController extends Controller
{
    private function serviceUrl(): string
    {
        return config('services.whatsapp_qr.url', 'http://127.0.0.1:3001');
    }

    /**
     * GET /app/whatsapp/groups
     * List all WhatsApp groups available on the QR-connected session.
     */
    public function index(Request $request): Response
    {
        $workspaceId = $request->user()->current_workspace_id ?? $request->user()->workspace_id;

        $account = ChannelAccount::where('workspace_id', $workspaceId)
            ->where('provider', 'qr_baileys')
            ->where('status', 'active')
            ->first();

        $groups  = [];
        $error   = null;

        if ($account) {
            $sessionId = $account->meta_json['session_id'] ?? "workspace_{$workspaceId}_qr";

            $response = Http::timeout(15)->get("{$this->serviceUrl()}/api/groups", [
                'sessionId' => $sessionId,
            ]);

            if ($response->successful()) {
                $groups = $response->json('groups', []);
            } else {
                $error = $response->json('error', 'Failed to fetch groups');
            }
        } else {
            $error = 'No active WhatsApp QR account connected.';
        }

        // Existing tags for tag selector
        $tags = ContactTag::where('workspace_id', $workspaceId)->get(['id', 'name']);

        return Inertia::render('client/Whatsapp/Groups', [
            'groups'    => $groups,
            'tags'      => $tags,
            'error'     => $error,
            'connected' => (bool) $account,
        ]);
    }

    /**
     * GET /app/whatsapp/groups/{groupId}/participants
     * Get participants of a specific WhatsApp group (JSON).
     */
    public function participants(Request $request, string $groupId): JsonResponse
    {
        $workspaceId = $request->user()->current_workspace_id ?? $request->user()->workspace_id;

        $account = ChannelAccount::where('workspace_id', $workspaceId)
            ->where('provider', 'qr_baileys')
            ->where('status', 'active')
            ->firstOrFail();

        $sessionId = $account->meta_json['session_id'] ?? "workspace_{$workspaceId}_qr";

        $response = Http::timeout(15)->get(
            "{$this->serviceUrl()}/api/groups/" . urlencode($groupId) . '/participants',
            ['sessionId' => $sessionId]
        );

        if (! $response->successful()) {
            return response()->json(['error' => $response->json('error', 'Failed')], 422);
        }

        return response()->json($response->json());
    }

    /**
     * POST /app/whatsapp/groups/import
     * Import participants of a WhatsApp group as Contacts and assign a tag.
     *
     * Body: { group_id, group_name, tag_id?: int, new_tag_name?: string }
     */
    public function import(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'group_id'     => ['required', 'string'],
            'group_name'   => ['required', 'string', 'max:128'],
            'tag_id'       => ['nullable', 'integer'],
            'new_tag_name' => ['nullable', 'string', 'max:64'],
        ]);

        $workspaceId = $request->user()->current_workspace_id ?? $request->user()->workspace_id;

        $account = ChannelAccount::where('workspace_id', $workspaceId)
            ->where('provider', 'qr_baileys')
            ->where('status', 'active')
            ->firstOrFail();

        $sessionId = $account->meta_json['session_id'] ?? "workspace_{$workspaceId}_qr";

        // Fetch participants from node service
        $response = Http::timeout(15)->get(
            "{$this->serviceUrl()}/api/groups/" . urlencode($validated['group_id']) . '/participants',
            ['sessionId' => $sessionId]
        );

        if (! $response->successful()) {
            return response()->json(['error' => 'Could not fetch group participants: ' . $response->json('error')], 422);
        }

        $participants = $response->json('group.participants', []);

        if (empty($participants)) {
            return response()->json(['error' => 'No participants found in this group.'], 422);
        }

        return DB::transaction(function () use ($validated, $workspaceId, $participants) {
            // Resolve or create tag
            $tag = null;
            if (! empty($validated['tag_id'])) {
                $tag = ContactTag::where('workspace_id', $workspaceId)->findOrFail($validated['tag_id']);
            } elseif (! empty($validated['new_tag_name'])) {
                $tag = ContactTag::firstOrCreate(
                    ['workspace_id' => $workspaceId, 'name' => trim($validated['new_tag_name'])],
                    ['color' => $this->randomTagColor()]
                );
            }

            $imported = 0;
            $skipped  = 0;

            foreach ($participants as $participant) {
                $phone = $participant['phone'] ?? null;
                if (! $phone) {
                    $skipped++;
                    continue;
                }

                // Normalise: ensure leading +
                if (! str_starts_with($phone, '+')) {
                    $phone = '+' . $phone;
                }

                // Skip own number (the connected QR number)
                // Upsert contact by phone_e164
                $contact = Contact::withTrashed()
                    ->where('workspace_id', $workspaceId)
                    ->where('phone_e164', $phone)
                    ->first();

                if (! $contact) {
                    $contact = Contact::create([
                        'workspace_id'      => $workspaceId,
                        'phone_e164'        => $phone,
                        'first_name'        => 'WA Contact',
                        'last_name'         => ltrim($phone, '+'),
                        'opt_in_whatsapp'   => true,
                        'source'            => 'whatsapp_group',
                    ]);
                } elseif ($contact->trashed()) {
                    $contact->restore();
                }

                // Assign tag if provided
                if ($tag && ! $contact->tags()->where('contact_tags.id', $tag->id)->exists()) {
                    $contact->tags()->attach($tag->id);
                }

                $imported++;
            }

            return response()->json([
                'success'  => true,
                'imported' => $imported,
                'skipped'  => $skipped,
                'tag'      => $tag ? ['id' => $tag->id, 'name' => $tag->name] : null,
            ]);
        });
    }

    private function randomTagColor(): string
    {
        $colors = ['#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#3b82f6', '#ef4444'];
        return $colors[array_rand($colors)];
    }
}
