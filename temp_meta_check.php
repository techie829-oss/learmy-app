<?php
$waba = App\Modules\Whatsapp\Models\WhatsappBusinessAccount::find(1);
$client = App\Modules\Whatsapp\Services\CloudApiClient::forWorkspace(3);

if (!$client) { echo "ERROR: No CloudApiClient found!\n"; return; }

echo "=== All Templates on Meta ===\n";
$templates = $client->fetchTemplates($waba->waba_id);
foreach ($templates as $t) {
    echo $t['name'] . " => " . $t['status'] . " (" . $t['category'] . ")\n";
}
echo "Total: " . count($templates) . "\n";

echo "\n=== Submitting demo template ===\n";
$payload = ['name' => 'demo_class_notification', 'language' => 'en', 'category' => 'UTILITY', 'components' => [['type' => 'BODY', 'text' => 'Hello! This is a demo notification from Learmy Academy. Your class details will be shared shortly. Thank you for joining us!']]];

$resp = $client->submitTemplate($waba->waba_id, $payload);
if ($resp->successful()) {
    $id = $resp->json('id');
    echo "SUCCESS! Template ID: $id\n";
    App\Modules\Whatsapp\Models\WhatsappTemplate::updateOrCreate(
        ['workspace_id' => 3, 'waba_id' => $waba->waba_id, 'name' => 'demo_class_notification', 'language' => 'en'],
        ['category' => 'UTILITY', 'status' => 'PENDING', 'components' => $payload['components'], 'meta_template_id' => $id]
    );
    echo "Saved locally!\n";
} else {
    echo "FAILED: " . json_encode($resp->json()) . "\n";
}
