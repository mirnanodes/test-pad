<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_log', function (Blueprint $table) {
            $table->id('request_id');
            $table->bigInteger('user_id')->default(0);
            $table->string('sender_name', 50)->nullable();
            $table->string('request_type', 50)->nullable();
            $table->string('request_content', 500)->nullable();
            $table->string('status', 20)->nullable();
            $table->datetime('sent_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_log');
    }
};
