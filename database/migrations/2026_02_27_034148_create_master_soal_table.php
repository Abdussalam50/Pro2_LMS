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
        Schema::create('master_soal', function (Blueprint $table) {
            $table->uuid('master_soal_id')->primary();
            $table->string('master_soal');
            $table->foreignUuid('tahapan_sintaks_id')->constrained('tahapan_sintaks', 'tahapan_sintaks_id')->onDelete('cascade');
            $table->boolean('is_diskusi')->default(false);
            $table->boolean('is_show_jawaban')->default(false);
            $table->boolean('is_show_kunci_jawaban')->default(false);
            $table->boolean('is_show_master_soal')->default(true);
            $table->boolean('is_shared')->default(false);
            $table->timestamp('tenggat_waktu')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_soal');
    }
};
