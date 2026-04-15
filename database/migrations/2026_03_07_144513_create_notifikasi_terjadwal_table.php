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
        Schema::create('notifikasi_terjadwal', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('dosen_id');
            $table->foreignUuid('kelas_id')->constrained('kelas', 'kelas_id')->onDelete('cascade');
            $table->string('judul');
            $table->text('isi');
            $table->dateTime('waktu_kirim');
            $table->enum('perulangan', ['none', 'daily', 'weekly'])->default('none');
            $table->enum('status', ['active', 'inactive', 'completed'])->default('active');
            $table->dateTime('terakhir_dikirim')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi_terjadwal');
    }
};
