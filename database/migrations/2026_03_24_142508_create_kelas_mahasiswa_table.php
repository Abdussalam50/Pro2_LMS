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
        Schema::create('kelas_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->string('mahasiswa_id');
            $table->string('kelas_id');
            $table->timestamps();

            $table->foreign('mahasiswa_id')->references('mahasiswa_id')->on('mahasiswas')->onDelete('cascade');
            $table->foreign('kelas_id')->references('kelas_id')->on('kelas')->onDelete('cascade');
            $table->unique(['mahasiswa_id', 'kelas_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas_mahasiswa');
    }
};
