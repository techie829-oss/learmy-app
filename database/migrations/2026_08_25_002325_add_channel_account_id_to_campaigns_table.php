<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            // nullable FK to channel_accounts — used when the campaign is sent
            // via a QR/Baileys account instead of a Meta Cloud API phone number.
            $table->unsignedBigInteger('channel_account_id')->nullable()->after('whatsapp_phone_number_id');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('channel_account_id');
        });
    }
};
