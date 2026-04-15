<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('main_soal', function (Blueprint $table) {
            $table->uuid('main_soal_id')->primary();
            $table->string('main_soal');
            $table->foreignUuid('master_soal_id')->constrained('master_soal', 'master_soal_id')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('main_soal');
    }
};
