<?php
// Fix sent_at timestamps that were stored as UTC instead of IST
// Only fix inbound messages from today onwards that have wrong (UTC) timestamps
$updated = DB::table('messages')
    ->where('direction', 'in')
    ->where('created_at', '>=', '2026-08-30')
    ->update(['sent_at' => DB::raw("DATE_ADD(sent_at, INTERVAL 330 MINUTE)")]);

echo "Fixed $updated inbound message timestamps.\n";

// Also fix conversation last_message_at and last_inbound_at for today
$fixedConvs = DB::table('conversations')
    ->where('last_inbound_at', '>=', '2026-08-30 00:00:00')
    ->where('last_inbound_at', '<=', '2026-08-30 18:30:00')
    ->update([
        'last_message_at' => DB::raw("DATE_ADD(last_message_at, INTERVAL 330 MINUTE)"),
        'last_inbound_at' => DB::raw("DATE_ADD(last_inbound_at, INTERVAL 330 MINUTE)"),
    ]);
echo "Fixed $fixedConvs conversation timestamps.\n";

// Show results
print_r(DB::table('messages')->where('conversation_id', 22)->orderBy('id')->get(['id','body','direction','sent_at'])->toArray());
