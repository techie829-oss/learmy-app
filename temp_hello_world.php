<?php
$waba = App\Modules\Whatsapp\Models\WhatsappBusinessAccount::find(1);
$client = App\Modules\Whatsapp\Services\CloudApiClient::forWorkspace(3);

if (!$client) { echo "No client!\n"; return; }

// Check demo_class_notification status on Meta directly
$tok = $waba->accessToken();
$r = \Illuminate\Support\Facades\Http::withToken($tok)
    ->get("https://graph.facebook.com/v20.0/" . $waba->waba_id . "/message_templates", [
        'fields' => 'name,status,category,language',
        'limit'  => 50,
    ]);

echo "=== Current Meta Template Statuses ===\n";
$approved = [];
foreach ($r->json('data', []) as $t) {
    echo $t['name'] . " [" . ($t['language'] ?? '') . "] => " . $t['status'] . "\n";
    if ($t['status'] === 'APPROVED') {
        $approved[] = $t['name'];
    }
}
echo "\nAPPROVED: " . (count($approved) ? implode(', ', $approved) : 'NONE') . "\n";
