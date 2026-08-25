<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Meeting;
use App\Models\MeetingTarget;
use App\Modules\Shared\Models\Contact;
use App\Modules\Shared\Models\ContactTag;
use App\Services\MeetingNotificationService;

// Create tag for workspace 3
$tag = ContactTag::firstOrCreate(
    ['workspace_id' => 3, 'name' => 'Web Dev Batch 2026'],
    ['color' => '#10b981']
);

$contact = Contact::find(9); // Test user +917007420572
if ($contact) {
    $contact->tags()->syncWithoutDetaching([$tag->id]);
}

$meeting = Meeting::create([
    'workspace_id'               => 3,
    'title'                      => 'Full-Stack Web Dev - Live Batch',
    'start_time'                 => now()->addMinutes(15),
    'end_time'                   => now()->addMinutes(75),
    'timezone'                   => 'Asia/Kolkata',
    'meet_link'                  => 'https://meet.google.com/xyz-uvwx-rst',
    'send_whatsapp_notification' => true,
    'reminder_settings'          => [
        'on_create'  => ['enabled' => true,  'template' => 'class_scheduled_notification'],
        'morning'    => ['enabled' => true,  'template' => 'class_morning_reminder'],
        'before_15m' => ['enabled' => true,  'template' => 'class_15m_reminder'],
        'on_start'   => ['enabled' => true,  'template' => 'class_started_now'],
    ]
]);

MeetingTarget::create([
    'meeting_id'  => $meeting->id,
    'target_type' => 'App\Modules\Shared\Models\ContactTag',
    'target_id'   => $tag->id,
]);

$service = app(MeetingNotificationService::class);

echo "1. Testing 'on_create' trigger..." . PHP_EOL;
$res1 = $service->dispatchNotifications($meeting, 'on_create');
echo "on_create result: " . json_encode($res1) . PHP_EOL;

sleep(3);

echo "2. Testing 'before_15m' trigger..." . PHP_EOL;
$res2 = $service->dispatchNotifications($meeting, 'before_15m');
echo "before_15m result: " . json_encode($res2) . PHP_EOL;
