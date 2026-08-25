<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->json('reminder_settings')->nullable()->after('whatsapp_template');
            $table->timestamp('reminded_morning_at')->nullable()->after('reminded_1h_at');
            $table->timestamp('reminded_start_at')->nullable()->after('reminded_15m_at');
        });
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn(['reminder_settings', 'reminded_morning_at', 'reminded_start_at']);
        });
    }
};
