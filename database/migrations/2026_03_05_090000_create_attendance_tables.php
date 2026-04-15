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
        // Table for active attendance codes (QR or Text)
        Schema::create('presensi_codes', function (Blueprint $table) {
            $table->uuid('presensi_code_id')->primary();
            $table->foreignUuid('pertemuan_id')->constrained('pertemuans', 'pertemuan_id')->onDelete('cascade');
            $table->string('type')->default('text'); // 'text' or 'qr'
            $table->string('code');
            $table->dateTime('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Table for student attendance records
        Schema::create('presensi_mahasiswa', function (Blueprint $table) {
            $table->uuid('presensi_id')->primary();
            $table->foreignUuid('pertemuan_id')->constrained('pertemuans', 'pertemuan_id')->onDelete('cascade');
            $table->foreignUuid('mahasiswa_id')->constrained('mahasiswas', 'mahasiswa_id')->onDelete('cascade');
            $table->string('status')->default('hadir'); // hadir, sakit, izin, alpha
            $table->dateTime('waktu_presensi');
            $table->string('metode')->nullable(); // 'manual', 'code', 'qr'
            $table->text('catatan')->nullable();
            $table->timestamps();
            
            // Ensure a student can only have one record per meeting
            $table->unique(['pertemuan_id', 'mahasiswa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_mahasiswa');
        Schema::dropIfExists('presensi_codes');
    }
};
