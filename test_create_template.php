<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Modules\Whatsapp\Models\WhatsappTemplate;

$t = WhatsappTemplate::firstOrCreate(
    ['workspace_id' => 3, 'name' => 'class_reminder_custom'],
    [
        'waba_id'    => 'workspace_3_qr',
        'language'   => 'hi',
        'category'   => 'UTILITY',
        'status'     => 'APPROVED',
        'components' => [
            ['type' => 'HEADER', 'format' => 'TEXT', 'text' => '🎓 Custom Class Alert — Learmy'],
            ['type' => 'BODY', 'text' => 'Namaste {{1}} ji!\n\nAapki class {{2}} schedule ho gayi hai.\nDate & Time: {{3}}\nMeet Link: {{4}}'],
            ['type' => 'FOOTER', 'text' => 'Learmy Education'],
            ['type' => 'BUTTONS', 'buttons' => [ ['type' => 'URL', 'text' => 'Join Class Now', 'url' => '{{4}}'] ]]
        ]
    ]
);

echo "Template ID: " . $t->id . " | Name: " . $t->name . " | Status: " . $t->status . PHP_EOL;
