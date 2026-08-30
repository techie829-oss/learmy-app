<?php
$waba = App\Modules\Whatsapp\Models\WhatsappBusinessAccount::find(1);
$tok = $waba->accessToken();

// Fetch hello_world specifically
$r = \Illuminate\Support\Facades\Http::withToken($tok)
    ->get("https://graph.facebook.com/v20.0/" . $waba->waba_id . "/message_templates", [
        'name'   => 'hello_world',
        'fields' => 'name,status,category,language,components',
        'limit'  => 10,
    ]);

echo "hello_world search result:\n";
print_r($r->json());

// Also sync it locally if found
foreach ($r->json('data', []) as $t) {
    echo "\nFound: " . $t['name'] . " => " . $t['status'] . " (" . ($t['language'] ?? '') . ")\n";
    App\Modules\Whatsapp\Models\WhatsappTemplate::updateOrCreate(
        ['workspace_id' => 3, 'waba_id' => $waba->waba_id, 'name' => $t['name'], 'language' => $t['language'] ?? 'en_US'],
        [
            'category'         => $t['category'] ?? 'UTILITY',
            'status'           => $t['status'] ?? 'APPROVED',
            'components'       => $t['components'] ?? [],
            'meta_template_id' => $t['id'] ?? null,
        ]
    );
    echo "Saved to DB!\n";
}
