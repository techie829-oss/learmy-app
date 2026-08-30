<?php

$workspaceId = 3;
$waba = \App\Modules\Whatsapp\Models\WhatsappBusinessAccount::where('workspace_id', $workspaceId)->first();
if (!$waba) { echo "No WABA found!\n"; exit; }

$client = \App\Modules\Whatsapp\Services\CloudApiClient::forWorkspace($workspaceId);

// ─────────────────────────────────────────────────────────────────
// 1. Fix class_on_start_reminder
//    Problem: variable {{4}} is at end of body — Meta doesn't allow
//    Fix: move link variable to middle, add fixed text after it
// ─────────────────────────────────────────────────────────────────
$t1 = \App\Modules\Whatsapp\Models\WhatsappTemplate::where('workspace_id', $workspaceId)
    ->where('name', 'class_on_start_reminder')->first();

if ($t1) {
    $newBody = "Hi {{1}},\n\nYour live class \"{{2}}\" scheduled for {{3}} has just started!\n\nJoin live now: {{4}}\n\nDo not miss it. See you in class!";
    $comps = $t1->components;
    foreach ($comps as &$c) {
        if ($c['type'] === 'BODY') {
            $c['text'] = $newBody;
            $c['example']['body_text'] = [['Rahul', 'Physics Ch-4', '26 Aug, 10:00 AM', 'https://meet.google.com/abc-defg-hij']];
        }
    }
    unset($c);

    $t1->update(['components' => $comps, 'waba_id' => $waba->waba_id]);

    $payload = [
        'name'       => $t1->name,
        'language'   => $t1->language,
        'category'   => $t1->category,
        'components' => $comps,
    ];

    echo "Submitting class_on_start_reminder...\n";
    $resp = $client->submitTemplate($waba->waba_id, $payload);
    if ($resp->successful()) {
        $t1->update(['meta_template_id' => $resp->json('id'), 'status' => 'PENDING']);
        echo "SUCCESS: class_on_start_reminder => " . $resp->json('id') . "\n";
    } else {
        echo "FAILED: " . json_encode($resp->json()) . "\n";
    }
}

// ─────────────────────────────────────────────────────────────────
// 2. Fix course_inquiry_followup
//    Problem: Header text has emoji 🎓 — Meta disallows emojis in TEXT headers
//    Fix: remove emoji from header
// ─────────────────────────────────────────────────────────────────
$t2 = \App\Modules\Whatsapp\Models\WhatsappTemplate::where('workspace_id', $workspaceId)
    ->where('name', 'course_inquiry_followup')->first();

if ($t2) {
    $comps = $t2->components;
    foreach ($comps as &$c) {
        if ($c['type'] === 'HEADER' && ($c['format'] ?? 'TEXT') === 'TEXT') {
            // Remove emoji and asterisks from header
            $c['text'] = 'Welcome to Learmy Academy';
        }
    }
    unset($c);

    $t2->update(['components' => $comps, 'waba_id' => $waba->waba_id]);

    $payload = [
        'name'       => $t2->name,
        'language'   => $t2->language,
        'category'   => $t2->category,
        'components' => $comps,
    ];

    echo "Submitting course_inquiry_followup...\n";
    $resp = $client->submitTemplate($waba->waba_id, $payload);
    if ($resp->successful()) {
        $t2->update(['meta_template_id' => $resp->json('id'), 'status' => 'PENDING']);
        echo "SUCCESS: course_inquiry_followup => " . $resp->json('id') . "\n";
    } else {
        echo "FAILED: " . json_encode($resp->json()) . "\n";
    }
}

echo "Done!\n";
