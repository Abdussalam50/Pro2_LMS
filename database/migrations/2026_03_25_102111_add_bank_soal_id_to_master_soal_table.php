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
        Schema::table('master_soal', function (Blueprint $table) {
            $table->uuid('bank_soal_id')->nullable()->after('master_soal');
            $table->foreign('bank_soal_id')->references('bank_soal_id')->on('bank_soals')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_soal', function (Blueprint $table) {
            $table->dropForeign(['bank_soal_id']);
            $table->dropColumn('bank_soal_id');
        });
    }
};
