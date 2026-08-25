<?php

namespace App\Modules\Whatsapp\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Integrations\Services\CredentialResolver;
use App\Modules\Whatsapp\Jobs\TemplateSyncJob;
use App\Modules\Whatsapp\Models\WhatsappBusinessAccount;
use App\Modules\Whatsapp\Models\WhatsappPhoneNumber;
use App\Modules\Whatsapp\Models\WhatsappTemplate;
use App\Modules\Whatsapp\Services\CloudApiClient;
use Illuminate\Http\Client\ConnectionException as HttpConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class WhatsappTemplateController extends Controller
{
    public function index(Request $request): Response
    {
        $workspaceId = $request->user()->current_workspace_id ?? $request->user()->workspace_id;

        // Collect phone numbers for the workspace to power the phone-number filter
        $wabaIds   = WhatsappBusinessAccount::where('workspace_id', $workspaceId)->pluck('waba_id');
        $wabaIdMap = WhatsappBusinessAccount::where('workspace_id', $workspaceId)->pluck('waba_id', 'id');

        $phoneNumbers = WhatsappPhoneNumber::whereIn('waba_id_fk', $wabaIdMap->keys())
            ->get()
            ->map(fn ($p) => [
                'phone_number_id' => $p->phone_number_id,
                'display_phone'   => $p->display_phone,
                'verified_name'   => $p->verified_name,
                'waba_id'         => $wabaIdMap[$p->waba_id_fk] ?? null,
            ]);

        $templates = WhatsappTemplate::where('workspace_id', $workspaceId)
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q) => $q->where('name', 'like', '%'.$request->search.'%'))
            ->when($request->phone_number_id, function ($q) use ($request, $wabaIdMap, $phoneNumbers) {
                $phone = $phoneNumbers->firstWhere('phone_number_id', $request->phone_number_id);
                if ($phone) {
                    $q->where('waba_id', $phone['waba_id']);
                }
            })
            ->latest()->get();

        return Inertia::render('Whatsapp/Templates/Index', [
            'templates'     => $templates,
            'phoneNumbers'  => $phoneNumbers,
            'filters'       => $request->only('status', 'search', 'phone_number_id'),
            'metaConnected' => $wabaIds->isNotEmpty(),
        ]);
    }

    public function create(Request $request): Response
    {
        $workspaceId = $request->user()->current_workspace_id ?? $request->user()->workspace_id;

        $wabaIdMap = WhatsappBusinessAccount::where('workspace_id', $workspaceId)->pluck('waba_id', 'id');

        $phoneNumbers = WhatsappPhoneNumber::whereIn('waba_id_fk', $wabaIdMap->keys())
            ->get()
            ->map(fn ($p) => [
                'phone_number_id' => $p->phone_number_id,
                'display_phone'   => $p->display_phone,
                'verified_name'   => $p->verified_name,
                'waba_id'         => $wabaIdMap[$p->waba_id_fk] ?? null,
            ]);

        return Inertia::render('Whatsapp/Templates/Editor', [
            'template'     => null,
            'phoneNumbers' => $phoneNumbers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $workspaceId = $request->user()->current_workspace_id ?? $request->user()->workspace_id;

        // Resolve Meta WABA if available; otherwise use QR session mode
        $waba = null;
        if ($request->filled('phone_number_id')) {
            $wabaIdMap = WhatsappBusinessAccount::where('workspace_id', $workspaceId)->pluck('waba_id', 'id');
            $phone     = WhatsappPhoneNumber::whereIn('waba_id_fk', $wabaIdMap->keys())
                ->where('phone_number_id', $request->phone_number_id)
                ->first();
            if ($phone) {
                $waba = WhatsappBusinessAccount::find($phone->waba_id_fk);
            }
        }
        if (!$waba) {
            $waba = WhatsappBusinessAccount::where('workspace_id', $workspaceId)->first();
        }

        $validated = $request->validate($this->templateRules());

        $this->assertComponentMultiplicity($validated['components']);

        $wabaId = $waba ? $waba->waba_id : "workspace_{$workspaceId}_qr";
        $initialStatus = $waba ? 'PENDING' : 'APPROVED';

        $template = WhatsappTemplate::create([
            'workspace_id' => $workspaceId,
            'waba_id'      => $wabaId,
            'name'         => $validated['name'],
            'language'     => $validated['language'],
            'category'     => $validated['category'],
            'components'   => $validated['components'],
            'status'       => $initialStatus,
        ]);

        $client = CloudApiClient::forWorkspace($workspaceId);
        if ($client && $waba) {
            $metaPayload = $this->buildMetaPayload($validated);
            $resp        = $client->submitTemplate($waba->waba_id, $metaPayload);

            if ($resp->successful()) {
                $template->update(['meta_template_id' => $resp->json('id')]);
            } else {
                $metaError = $resp->json('error.error_user_msg')
                    ?? $resp->json('error.message')
                    ?? 'Meta rejected the template (HTTP '.$resp->status().')';

                Log::warning('WhatsApp template submission failed', [
                    'workspace_id' => $workspaceId,
                    'template_id'   => $template->id,
                    'meta_error'    => $metaError,
                    'payload'       => $metaPayload,
                ]);

                $template->update(['status' => 'REJECTED', 'rejection_reason' => $metaError]);

                return redirect()->route('client.whatsapp.templates.index')
                    ->with('error', 'Template saved locally but Meta rejected it: '.$metaError);
            }
        }

        $msg = $waba ? 'Template submitted to Meta for approval.' : 'Template created and ready to use!';

        return redirect()->route('client.whatsapp.templates.index')->with('success', $msg);
    }

    public function edit(Request $request, WhatsappTemplate $template): Response
    {
        $workspaceId = $request->user()->current_workspace_id ?? $request->user()->workspace_id;
        abort_unless($template->workspace_id === $workspaceId, 403);

        $wabaIdMap = WhatsappBusinessAccount::where('workspace_id', $workspaceId)->pluck('waba_id', 'id');

        $phoneNumbers = WhatsappPhoneNumber::whereIn('waba_id_fk', $wabaIdMap->keys())
            ->get()
            ->map(fn ($p) => [
                'phone_number_id' => $p->phone_number_id,
                'display_phone'   => $p->display_phone,
                'verified_name'   => $p->verified_name,
                'waba_id'         => $wabaIdMap[$p->waba_id_fk] ?? null,
            ]);

        return Inertia::render('Whatsapp/Templates/Editor', [
            'template'     => $template->only('id', 'name', 'language', 'category', 'status', 'components'),
            'phoneNumbers' => $phoneNumbers,
        ]);
    }

    public function update(Request $request, WhatsappTemplate $template): RedirectResponse
    {
        $workspaceId = $request->user()->current_workspace_id ?? $request->user()->workspace_id;
        abort_unless($template->workspace_id === $workspaceId, 403);

        $validated             = $request->validate($this->templateRules(nameRequired: false));
        $validated['name']     = $template->name;
        $validated['language'] = $template->language;

        $this->assertComponentMultiplicity($validated['components']);

        $template->update([
            'category'   => $validated['category'],
            'components' => $validated['components'],
            'status'     => 'APPROVED',
        ]);

        $client = CloudApiClient::forWorkspace($workspaceId);
        if ($client && $template->waba_id !== "workspace_{$workspaceId}_qr") {
            $metaPayload = $this->buildMetaPayload($validated);
            $resp        = $template->meta_template_id
                ? $client->editTemplate($template->meta_template_id, $metaPayload)
                : $client->submitTemplate($template->waba_id, $metaPayload);

            if ($resp->successful()) {
                $template->update([
                    'status'           => 'PENDING',
                    'rejection_reason' => null,
                    'meta_template_id' => $template->meta_template_id ?? $resp->json('id'),
                ]);
            } else {
                $metaError = $resp->json('error.error_user_msg')
                    ?? $resp->json('error.message')
                    ?? 'Meta rejected the template (HTTP '.$resp->status().')';

                Log::warning('WhatsApp template edit failed', [
                    'workspace_id' => $workspaceId,
                    'template_id'   => $template->id,
                    'meta_error'    => $metaError,
                    'payload'       => $metaPayload,
                ]);

                $template->update(['status' => 'REJECTED', 'rejection_reason' => $metaError]);

                return redirect()->route('client.whatsapp.templates.index')
                    ->with('error', 'Template saved locally but Meta rejected change: '.$metaError);
            }
        }

        return redirect()->route('client.whatsapp.templates.index')
            ->with('success', 'Template updated successfully!');
    }

    public function destroy(Request $request, WhatsappTemplate $template): RedirectResponse
    {
        $workspaceId = $request->user()->current_workspace_id ?? $request->user()->workspace_id;
        abort_unless($template->workspace_id === $workspaceId, 403);

        $metaWarning = null;
        $client      = CloudApiClient::forWorkspace($workspaceId);
        if ($client && $template->waba_id !== "workspace_{$workspaceId}_qr") {
            try {
                $resp = $client->deleteTemplate($template->waba_id, $template->name);
                if (! $resp->successful()) {
                    $metaWarning = $resp->json('error.error_user_msg')
                        ?? $resp->json('error.message')
                        ?? 'Meta returned HTTP '.$resp->status();
                }
            } catch (\Throwable $e) {
                $metaWarning = $e->getMessage();
            }
        }

        $name = $template->name;
        $template->delete();

        if ($metaWarning) {
            return back()->with('error', "Deleted “{$name}” locally, but Meta reported: {$metaWarning}");
        }

        return back()->with('success', "Template “{$name}” deleted.");
    }

    /**
     * Upload a header media file and return handle or public URL.
     */
    public function uploadMedia(Request $request): JsonResponse
    {
        $workspaceId = $request->user()->current_workspace_id ?? $request->user()->workspace_id;
        $waba        = WhatsappBusinessAccount::where('workspace_id', $workspaceId)->first();

        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:jpeg,jpg,png,mp4,pdf',
                'max:102400',
            ],
        ]);

        $file   = $request->file('file');
        $mime   = $file->getMimeType() ?? 'application/octet-stream';
        $format = match (true) {
            str_starts_with($mime, 'image/') => 'IMAGE',
            str_starts_with($mime, 'video/') => 'VIDEO',
            $mime === 'application/pdf'      => 'DOCUMENT',
            default                          => 'IMAGE',
        };

        if (!$waba) {
            $path = $file->store('whatsapp-templates', 'public');
            $url  = asset("storage/{$path}");
            return response()->json(['handle' => $url, 'url' => $url, 'format' => $format]);
        }

        $creds = $waba->credentials ?? [];
        $token = $creds['system_user_token'] ?? '';
        if (empty($token)) {
            $token = CredentialResolver::system()->meta()?->systemUserToken() ?? '';
        }

        $appId = CredentialResolver::system()->meta()?->appId() ?? '';

        if (empty($token) || empty($appId)) {
            $path = $file->store('whatsapp-templates', 'public');
            $url  = asset("storage/{$path}");
            return response()->json(['handle' => $url, 'url' => $url, 'format' => $format]);
        }

        try {
            $handle = CloudApiClient::resumableUpload($appId, $token, $file->getRealPath(), $mime);
        } catch (\Throwable $e) {
            $path = $file->store('whatsapp-templates', 'public');
            $url  = asset("storage/{$path}");
            return response()->json(['handle' => $url, 'url' => $url, 'format' => $format]);
        }

        return response()->json(['handle' => $handle, 'format' => $format]);
    }

    public function sync(Request $request): RedirectResponse
    {
        $workspaceId = $request->user()->current_workspace_id ?? $request->user()->workspace_id;
        $waba        = WhatsappBusinessAccount::where('workspace_id', $workspaceId)->first();

        if (! $waba) {
            return back()->with('success', 'WhatsApp QR Engine active. Templates created here are auto-approved and ready for use!');
        }

        try {
            (new TemplateSyncJob($waba->id))->handle();
        } catch (\Throwable $e) {
            Log::warning('WhatsApp template sync failed', [
                'workspace_id' => $workspaceId,
                'waba_id'      => $waba->waba_id,
                'exception'    => $e->getMessage(),
            ]);

            return back()->withErrors(['sync' => 'Could not sync templates from Meta: '.$e->getMessage()]);
        }

        $count = WhatsappTemplate::where('workspace_id', $workspaceId)->count();

        return back()->with('success', "Synced templates from Meta ({$count} in your workspace).");
    }

    private function templateRules(bool $nameRequired = true): array
    {
        return [
            'name'                  => [$nameRequired ? 'required' : 'nullable', 'string', 'max:512', 'regex:/^[a-z0-9_]+$/'],
            'language'              => ['required', 'string', 'max:16'],
            'category'              => ['required', 'string', 'in:MARKETING,UTILITY,AUTHENTICATION'],
            'components'            => ['required', 'array', 'min:1'],
            'components.*.type'     => ['required', 'string', 'in:HEADER,BODY,FOOTER,BUTTONS'],
            'components.*.format'   => ['nullable', 'string', 'in:TEXT,IMAGE,VIDEO,DOCUMENT'],
            'components.*.text'     => ['nullable', 'string'],
            'components.*.buttons'  => ['nullable', 'array'],
            'components.*.example'  => ['nullable', 'array'],
        ];
    }

    private function assertComponentMultiplicity(array $components): void
    {
        $counts = [];
        foreach ($components as $comp) {
            $type          = $comp['type'] ?? '';
            $counts[$type] = ($counts[$type] ?? 0) + 1;
            if ($counts[$type] > 1) {
                throw ValidationException::withMessages([
                    'components' => "Template can have at most one {$type} component.",
                ]);
            }
        }
    }

    private function buildMetaPayload(array $validated): array
    {
        $components = [];

        foreach ($validated['components'] as $comp) {
            $type = $comp['type'];

            if ($type === 'BUTTONS') {
                $buttons = [];
                foreach ($comp['buttons'] ?? [] as $btn) {
                    $b = ['type' => $btn['type'], 'text' => $btn['text']];
                    if ($btn['type'] === 'URL') {
                        $b['url'] = $btn['url'] ?? '';
                        if (! empty($btn['example'])) {
                            $b['example'] = $btn['example'];
                        }
                    } elseif ($btn['type'] === 'PHONE_NUMBER') {
                        $b['phone_number'] = $btn['phone_number'] ?? '';
                    }
                    $buttons[] = $b;
                }
                if (! empty($buttons)) {
                    $components[] = ['type' => 'BUTTONS', 'buttons' => $buttons];
                }
                continue;
            }

            $built = ['type' => $type];

            if ($type === 'HEADER') {
                $format          = $comp['format'] ?? 'TEXT';
                $built['format'] = $format;

                if ($format === 'TEXT') {
                    $built['text']  = $comp['text'] ?? '';
                    $headerExamples = $comp['example']['header_text'] ?? [];
                    if (! empty($headerExamples)) {
                        $built['example'] = ['header_text' => array_values($headerExamples)];
                    }
                } else {
                    $handle = $comp['example']['header_handle'][0] ?? null;
                    if ($handle) {
                        $built['example'] = ['header_handle' => [$handle]];
                    }
                }
            } elseif ($type === 'BODY') {
                $built['text'] = $comp['text'] ?? '';
                $bodyExamples  = $comp['example']['body_text'][0] ?? [];
                if (! empty($bodyExamples)) {
                    $built['example'] = ['body_text' => [array_values($bodyExamples)]];
                }
            } elseif ($type === 'FOOTER') {
                $built['text'] = $comp['text'] ?? '';
            }

            $components[] = $built;
        }

        return [
            'name'       => $validated['name'],
            'language'   => $validated['language'],
            'category'   => $validated['category'],
            'components' => $components,
        ];
    }
}
