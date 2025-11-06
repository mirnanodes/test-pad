<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained('farms', 'farm_id')->onDelete('cascade');
            $table->foreignId('user_id_input')->nullable()->constrained('users', 'user_id')->onDelete('set null');
            $table->date('report_date');
            $table->decimal('konsumsi_pakan', 10, 2)->nullable();
            $table->decimal('konsumsi_air', 10, 2)->nullable();
            $table->integer('jumlah_kematian')->nullable();
            $table->timestamps();
            $table->unique(['farm_id', 'report_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_data');
    }
};
