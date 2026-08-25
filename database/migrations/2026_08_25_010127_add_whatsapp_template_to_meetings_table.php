<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            // Which WhatsApp template to use when sending meeting notifications
            // null = use default 'class_scheduled_notification'
            $table->string('whatsapp_template')->nullable()->after('meet_link');
            // Whether to send WhatsApp notification on creation
            $table->boolean('send_whatsapp_notification')->default(true)->after('whatsapp_template');
        });
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_template', 'send_whatsapp_notification']);
        });
    }
};
