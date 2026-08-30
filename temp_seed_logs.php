<?php
// Parse laravel.log for today's MeetingNotification entries and seed notification_logs table

$logFile = '/var/www/learmy-app/storage/logs/laravel.log';
if (!file_exists($logFile)) {
    echo "Log file not found.\n";
    exit;
}

$lines = file($logFile);
$inserted = 0;

foreach ($lines as $line) {
    if (strpos($line, '[MeetingNotification] Meta template failed') !== false) {
        // Extract JSON payload
        $pos = strpos($line, '{');
        if ($pos !== false) {
            $jsonStr = substr($line, $pos);
            $data = json_decode($jsonStr, true);
            if ($data && isset($data['phone'])) {
                $phone = $data['phone'];
                $trigger = $data['trigger'] ?? 'on_create';
                $error = $data['error'] ?? 'Unknown error';
                $workspaceId = $data['workspace_id'] ?? 3;

                $templateMap = [
                    'on_create'  => 'class_on_create_reminder',
                    'morning'    => 'class_morning_reminder',
                    'before_15m' => 'class_before_15m_reminder',
                    'on_start'   => 'class_on_start_reminder',
                ];

                // Check if already exists to prevent duplicate seeding
                $exists = DB::table('notification_logs')
                    ->where('workspace_id', $workspaceId)
                    ->where('phone', $phone)
                    ->where('trigger', $trigger)
                    ->exists();

                if (!$exists) {
                    $contact = DB::table('contacts')->where('phone_e164', $phone)->first();

                    DB::table('notification_logs')->insert([
                        'workspace_id'  => $workspaceId,
                        'meeting_id'    => null,
                        'contact_id'    => $contact ? $contact->id : null,
                        'phone'         => $phone,
                        'trigger'       => $trigger,
                        'channel'       => 'whatsapp',
                        'provider'      => 'meta',
                        'template_name' => $templateMap[$trigger] ?? 'class_notification',
                        'status'        => 'failed',
                        'error_message' => $error,
                        'sent_at'       => now(),
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                    $inserted++;
                }
            }
        }
    }
}

echo "Successfully seeded $inserted historical notification log entries from laravel.log!\n";
