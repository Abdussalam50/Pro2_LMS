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
        Schema::table('jawabans_ujian_mahasiswa', function (Blueprint $table) {
            $table->boolean('is_benar')->default(false)->after('jawaban_pilihan_ganda');
            $table->integer('skor')->default(0)->after('is_benar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jawabans_ujian_mahasiswa', function (Blueprint $table) {
            $table->dropColumn(['is_benar', 'skor']);
        });
    }
};
