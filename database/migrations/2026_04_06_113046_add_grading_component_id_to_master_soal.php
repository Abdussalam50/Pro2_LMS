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
            $table->uuid('grading_component_id')->nullable()->after('tahapan_sintaks_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_soal', function (Blueprint $table) {
            $table->dropColumn('grading_component_id');
        });
    }
};
