<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->foreignId('academic_period_id')->nullable()->constrained('academic_periods')->onDelete('set null');
        });

        // Create default period for existing data
        $periodId = DB::table('academic_periods')->insertGetId([
            'name' => 'Internal/Default Period',
            'tahun' => date('Y') . '/' . (date('Y') + 1),
            'semester' => 'ganjil',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Link all existing classes to this default period
        DB::table('kelas')->whereNull('academic_period_id')->update(['academic_period_id' => $periodId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropForeign(['academic_period_id']);
            $table->dropColumn('academic_period_id');
        });
    }
};
