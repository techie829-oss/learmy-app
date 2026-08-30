<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('meeting_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('phone')->index();
            $table->string('trigger', 50)->default('on_create');
            $table->string('channel', 30)->default('whatsapp');
            $table->string('provider', 30)->default('meta');
            $table->string('template_name')->nullable();
            $table->enum('status', ['sent', 'failed', 'delivered'])->default('sent')->index();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
