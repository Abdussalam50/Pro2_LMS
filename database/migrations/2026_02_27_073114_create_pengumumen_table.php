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
        Schema::create('pengumuman', function (Blueprint $table) {
            $table->uuid('pengumuman_id')->primary();
            $table->uuid('dosen_id');
            $table->string('judul');
            $table->text('konten');
            $table->timestamps();

            $table->foreign('dosen_id')->references('dosen_id')->on('dosens')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengumuman');
    }
};
