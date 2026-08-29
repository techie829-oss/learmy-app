<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meeting_targets', function (Blueprint $table) {
            // 'group'      → send one message to the WhatsApp group chat
            // 'individual' → send personal message to each group member individually
            $table->string('notify_mode', 20)->default('group')->after('target_id');
        });
    }

    public function down(): void
    {
        Schema::table('meeting_targets', function (Blueprint $table) {
            $table->dropColumn('notify_mode');
        });
    }
};
