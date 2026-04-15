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
        Schema::create('enrollment_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('mahasiswa_id');
            $table->uuid('kelas_id');
            $table->enum('status', ['pending', 'rejected'])->default('pending');
            $table->timestamps();

            $table->foreign('mahasiswa_id')->references('mahasiswa_id')->on('mahasiswas')->onDelete('cascade');
            $table->foreign('kelas_id')->references('kelas_id')->on('kelas')->onDelete('cascade');
            // Ensure unique request per student per class
            $table->unique(['mahasiswa_id', 'kelas_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_requests');
    }
};
