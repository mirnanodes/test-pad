<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farm_config', function (Blueprint $table) {
            $table->id('config_id');
            $table->foreignId('farm_id')->constrained('farms', 'farm_id')->onDelete('cascade');
            $table->string('parameter_name', 50);
            $table->decimal('value', 10, 2)->nullable();
            $table->unique(['farm_id', 'parameter_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farm_config');
    }
};
