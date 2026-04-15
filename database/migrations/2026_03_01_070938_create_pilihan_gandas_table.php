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
        Schema::create('pilihan_ganda', function (Blueprint $table) {
            $table->uuid('pilihan_ganda_id')->primary();
            $table->foreignUuid('soal_id')->constrained('soal', 'soal_id')->cascadeOnDelete();
            $table->string('pilihan_ganda');
            $table->boolean('status')->default(false); // true if correct answer
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pilihan_ganda');
    }
};
