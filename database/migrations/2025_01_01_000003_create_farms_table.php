<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farms', function (Blueprint $table) {
            $table->id('farm_id');
            $table->foreignId('owner_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('peternak_id')->nullable()->constrained('users', 'user_id')->onDelete('set null');
            $table->string('farm_name', 100);
            $table->string('location', 255)->nullable();
            $table->integer('initial_population')->nullable();
            $table->decimal('initial_weight', 10, 2)->nullable();
            $table->integer('farm_area')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farms');
    }
};
