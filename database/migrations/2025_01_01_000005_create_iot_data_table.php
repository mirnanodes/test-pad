<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iot_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained('farms', 'farm_id')->onDelete('cascade');
            $table->datetime('timestamp');
            $table->decimal('temperature', 5, 2)->nullable();
            $table->decimal('humidity', 5, 2)->nullable();
            $table->decimal('ammonia', 5, 2)->nullable();
            $table->string('data_source', 10)->default('IOT');
            $table->index('timestamp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iot_data');
    }
};
