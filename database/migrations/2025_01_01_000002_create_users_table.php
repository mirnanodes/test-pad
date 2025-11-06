<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('set null');
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->string('name', 100);
            $table->string('phone_number', 20)->nullable();
            $table->string('profile_pic', 255)->nullable();
            $table->string('status', 20)->nullable();
            $table->date('date_joined')->nullable();
            $table->datetime('last_login')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
