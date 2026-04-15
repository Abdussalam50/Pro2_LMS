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
        Schema::create('sintaks_tampil', function (Blueprint $table) {
            $table->uuid('sintaks_tampil_id')->primary();
            $table->foreignUuid('kelas_id')->constrained('kelas', 'kelas_id')->onDelete('cascade');
            $table->foreignUuid('pertemuan_id')->constrained('pertemuans', 'pertemuan_id')->onDelete('cascade');
            $table->foreignUuid('sintaks_belajar_id')->constrained('sintaks_belajar', 'sintaks_belajar_id')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sintaks_tampil');
    }
};
