<?php

$workspaceId = 3;
$waba = \App\Modules\Whatsapp\Models\WhatsappBusinessAccount::where('workspace_id', $workspaceId)->first();
if (!$waba) {
    echo "No WABA found for workspace {$workspaceId}!\n";
    exit;
}

$client = \App\Modules\Whatsapp\Services\CloudApiClient::forWorkspace($workspaceId);
$templates = \App\Modules\Whatsapp\Models\WhatsappTemplate::where('workspace_id', $workspaceId)
    ->where('waba_id', 'workspace_3_qr')
    ->get();

echo "Found " . $templates->count() . " templates to migrate.\n";

foreach ($templates as $t) {
    // Build payload
    $components = [];
    foreach ($t->components as $comp) {
        $type = $comp['type'];
        if ($type === 'BUTTONS') {
            $buttons = [];
            foreach ($comp['buttons'] ?? [] as $btn) {
                $b = ['type' => $btn['type'], 'text' => $btn['text']];
                if ($btn['type'] === 'URL') {
                    $b['url'] = $btn['url'] ?? '';
                    if (!empty($btn['example'])) {
                        $b['example'] = $btn['example'];
                    }
                } elseif ($btn['type'] === 'PHONE_NUMBER') {
                    $b['phone_number'] = $btn['phone_number'] ?? '';
                }
                $buttons[] = $b;
            }
            if (!empty($buttons)) {
                $components[] = ['type' => 'BUTTONS', 'buttons' => $buttons];
            }
            continue;
        }

        $built = ['type' => $type];
        if ($type === 'HEADER') {
            $format = $comp['format'] ?? 'TEXT';
            $built['format'] = $format;
            if ($format === 'TEXT') {
                $built['text'] = $comp['text'] ?? '';
                $headerExamples = $comp['example']['header_text'] ?? [];
                if (!empty($headerExamples)) {
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
            $bodyExamples = $comp['example']['body_text'][0] ?? ($comp['example']['body_text'] ?? []);
            if (!empty($bodyExamples)) {
                $built['example'] = ['body_text' => [array_values($bodyExamples)]];
            }
        } elseif ($type === 'FOOTER') {
            $built['text'] = $comp['text'] ?? '';
        }
        $components[] = $built;
    }

    $payload = [
        'name' => $t->name,
        'language' => $t->language,
        'category' => $t->category,
        'components' => $components,
    ];

    echo "Submitting {$t->name} to Meta WABA {$waba->waba_id}...\n";
    $resp = $client->submitTemplate($waba->waba_id, $payload);
    if ($resp->successful()) {
        $metaTemplateId = $resp->json('id');
        $t->update([
            'waba_id' => $waba->waba_id,
            'meta_template_id' => $metaTemplateId,
            'status' => 'PENDING',
        ]);
        echo "Successfully submitted {$t->name}! Meta ID: {$metaTemplateId}\n";
    } else {
        echo "Failed to submit {$t->name}: " . json_encode($resp->json()) . "\n";
    }
}
echo "Migration complete!\n";
