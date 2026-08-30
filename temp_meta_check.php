<?php
$waba = App\Modules\Whatsapp\Models\WhatsappBusinessAccount::find(1);
$tok = App\Modules\Integrations\Services\CredentialResolver::forWorkspace(3)->meta()->accessToken();

// Step 1: List all templates on Meta (including hello_world)
$r = \Illuminate\Support\Facades\Http::withToken($tok)
    ->get("https://graph.facebook.com/v20.0/" . $waba->waba_id . "/message_templates?fields=name,status,category&limit=50");

echo "=== All Templates on Meta ===\n";
foreach ($r->json('data', []) as $t) {
    echo $t['name'] . " => " . $t['status'] . " (" . $t['category'] . ")\n";
}

// Step 2: Submit a very simple demo template for fast approval
// Simple UTILITY templates with no variables approve in minutes
$payload = [
    'name'       => 'demo_class_notification',
    'language'   => 'en',
    'category'   => 'UTILITY',
    'components' => [
        [
            'type' => 'BODY',
            'text' => 'Hello! This is a demo notification from Learmy Academy. Your class details will be shared shortly. Thank you for joining us!',
        ],
    ],
];

echo "\n=== Submitting demo template ===\n";
$resp = \Illuminate\Support\Facades\Http::withToken($tok)
    ->post("https://graph.facebook.com/v20.0/" . $waba->waba_id . "/message_templates", $payload);

if ($resp->successful()) {
    $id = $resp->json('id');
    echo "SUCCESS! Template ID: $id\n";
    
    // Save locally
    App\Modules\Whatsapp\Models\WhatsappTemplate::updateOrCreate(
        ['workspace_id' => 3, 'waba_id' => $waba->waba_id, 'name' => 'demo_class_notification', 'language' => 'en'],
        [
            'category'         => 'UTILITY',
            'status'           => 'PENDING',
            'components'       => $payload['components'],
            'meta_template_id' => $id,
        ]
    );
    echo "Saved to local DB\n";
} else {
    echo "FAILED: " . json_encode($resp->json()) . "\n";
}
