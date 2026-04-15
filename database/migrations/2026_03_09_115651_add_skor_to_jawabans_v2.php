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
        if (Schema::hasTable('jawabans_ujian_mahasiswa')) {
            Schema::table('jawabans_ujian_mahasiswa', function (Blueprint $table) {
                if (!Schema::hasColumn('jawabans_ujian_mahasiswa', 'is_benar')) {
                    $table->boolean('is_benar')->default(false)->after('jawaban_pilihan_ganda');
                }
                if (!Schema::hasColumn('jawabans_ujian_mahasiswa', 'skor')) {
                    $table->integer('skor')->default(0)->after('is_benar');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('jawabans_ujian_mahasiswa')) {
            Schema::table('jawabans_ujian_mahasiswa', function (Blueprint $table) {
                if (Schema::hasColumn('jawabans_ujian_mahasiswa', 'is_benar')) {
                    $table->dropColumn('is_benar');
                }
                if (Schema::hasColumn('jawabans_ujian_mahasiswa', 'skor')) {
                    $table->dropColumn('skor');
                }
            });
        }
    }
};
