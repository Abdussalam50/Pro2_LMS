<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\GradingService;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Let's find the class and student from the context or first available
$class = Kelas::first();
$student = User::where('role', 'mahasiswa')->first();

if (!$class || !$student) {
    die("No data found to debug.\n");
}

echo "Debugging Grading for Class: {$class->kelas} (ID: {$class->kelas_id})\n";
echo "Student: {$student->name} (ID: {$student->id})\n";

$service = new GradingService();
$averages = $service->getCategoryAverages($class->kelas_id, $student->id);

echo "\n--- Category Averages ---\n";
foreach ($averages as $avg) {
    echo "Category: {$avg['name']} (Weight: {$avg['weight']}%)\n";
    echo "  Score: {$avg['score']}\n";
}

$finalGrade = $service->calculateFinalGrade($class->kelas_id, $student->id);
echo "\nFinal Weighted Grade: {$finalGrade}\n";

// Deep dive into UTS
$utsComponent = DB::table('grading_components')
    ->where('kelas_id', $class->kelas_id)
    ->where('category', 'uts')
    ->first();

if ($utsComponent) {
    echo "\n--- UTS Component Deep Dive ---\n";
    $ujianIds = DB::table('ujians')
        ->where('kelas_id', $class->kelas_id)
        ->where('grading_component_id', $utsComponent->id)
        ->pluck('ujian_id');
    
    echo "Ujian IDs: ".json_encode($ujianIds)."\n";
    
    foreach ($ujianIds as $id) {
        $ujian = DB::table('ujians')->where('ujian_id', $id)->first();
        $maxPoints = DB::table('soal_ujians')->where('ujian_id', $id)->sum('bobot');
        $userRawScore = DB::table('nilai_ujians_mahasiswa')
            ->join('mahasiswas', 'nilai_ujians_mahasiswa.mahasiswa_id', '=', 'mahasiswas.mahasiswa_id')
            ->where('nilai_ujians_mahasiswa.ujian_id', $id)
            ->where('mahasiswas.user_id', $student->id)
            ->value('nilai') ?? 0;
            
        echo "Ujian: {$ujian->nama_ujian}\n";
        echo "  Max Points (sum bobot): {$maxPoints}\n";
        echo "  User Raw Score: {$userRawScore}\n";
        $calc = ($maxPoints > 0) ? ($userRawScore / $maxPoints) * 100 : 0;
        echo "  Calculated Normalized (Score/Max * 100): {$calc}\n";
    }
}
