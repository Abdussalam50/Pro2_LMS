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
        Schema::table('diskusi_kelas', function (Blueprint $table) {
            $table->uuid('tahapan_sintaks_id')->nullable()->after('pertemuan_id');
            // Assuming tahapan_sintaks table exists, if we want foreign key constraints we can add:
            // $table->foreign('tahapan_sintaks_id')->references('tahapan_sintaks_id')->on('tahapan_sintaks')->onDelete('cascade');
        });

        Schema::table('diskusi_kelompok', function (Blueprint $table) {
            $table->uuid('tahapan_sintaks_id')->nullable()->after('pertemuan_id');
        });

        Schema::table('diskusi_dosen', function (Blueprint $table) {
            $table->uuid('tahapan_sintaks_id')->nullable()->after('pertemuan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diskusi_kelas', function (Blueprint $table) {
            $table->dropColumn('tahapan_sintaks_id');
        });

        Schema::table('diskusi_kelompok', function (Blueprint $table) {
            $table->dropColumn('tahapan_sintaks_id');
        });

        Schema::table('diskusi_dosen', function (Blueprint $table) {
            $table->dropColumn('tahapan_sintaks_id');
        });
    }
};
