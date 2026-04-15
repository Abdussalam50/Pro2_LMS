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
        Schema::rename('grading_weights', 'grading_components');
        
        Schema::table('grading_components', function (Blueprint $table) {
            $table->string('name')->after('category')->nullable();
            $table->boolean('is_default')->default(false)->after('name');
            $table->string('mapping_type')->default('exam')->after('is_default'); // assignment, exam, attendance, manual
        });

        // Migrate existing category to name
        DB::statement("UPDATE grading_components SET name = category");
        DB::statement("UPDATE grading_components SET is_default = 1 WHERE category IN ('tugas', 'kuis', 'uts', 'uas', 'presensi')");
        DB::statement("UPDATE grading_components SET mapping_type = 'assignment' WHERE category = 'tugas'");
        DB::statement("UPDATE grading_components SET mapping_type = 'exam' WHERE category IN ('kuis', 'uts', 'uas')");
        DB::statement("UPDATE grading_components SET mapping_type = 'attendance' WHERE category = 'presensi'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grading_components', function (Blueprint $table) {
            $table->dropColumn(['name', 'is_default', 'mapping_type']);
        });

        Schema::rename('grading_components', 'grading_weights');
    }
};
