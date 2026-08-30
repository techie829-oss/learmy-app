<?php
$waba = App\Modules\Whatsapp\Models\WhatsappBusinessAccount::find(1);
$client = App\Modules\Whatsapp\Services\CloudApiClient::forWorkspace(3);

if (!$client) { echo "No client!\n"; return; }

// Submit hello_world template (Meta's standard sample template - approves instantly)
$payload = [
    'name'       => 'hello_world',
    'language'   => 'en_US',
    'category'   => 'UTILITY',
    'components' => [
        [
            'type'   => 'HEADER',
            'format' => 'TEXT',
            'text'   => 'Hello World',
        ],
        [
            'type' => 'BODY',
            'text' => 'Welcome and congratulations!! This message demonstrates your ability to send a WhatsApp message notification from the Cloud API. Thank you for taking the time to test with us.',
        ],
    ],
];

echo "Submitting hello_world...\n";
$resp = $client->submitTemplate($waba->waba_id, $payload);
if ($resp->successful()) {
    $id = $resp->json('id');
    echo "SUCCESS! ID: $id, Status: " . $resp->json('status') . "\n";
    App\Modules\Whatsapp\Models\WhatsappTemplate::updateOrCreate(
        ['workspace_id' => 3, 'waba_id' => $waba->waba_id, 'name' => 'hello_world', 'language' => 'en_US'],
        ['category' => 'UTILITY', 'status' => $resp->json('status','PENDING'), 'components' => $payload['components'], 'meta_template_id' => $id]
    );
    echo "Saved!\n";
} else {
    echo "FAILED: " . json_encode($resp->json()) . "\n";
}
