<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_log', function (Blueprint $table) {
            $table->id('notif_id');
            $table->foreignId('sender_user_id')->nullable()->constrained('users', 'user_id')->onDelete('set null');
            $table->foreignId('recipient_user_id')->nullable()->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('farm_id')->nullable()->constrained('farms', 'farm_id')->onDelete('cascade');
            $table->string('notification_type', 20)->nullable();
            $table->string('message_content', 500)->nullable();
            $table->datetime('sent_at')->nullable();
            $table->string('status', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_log');
    }
};
