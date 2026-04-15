<?php

use App\Services\ClassroomService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$uuid = Str::uuid()->toString();
$dosenId = Str::uuid()->toString();
$mkId = Str::uuid()->toString();
$kelasId = Str::uuid()->toString();

DB::table('users')->insert([
    'uuid' => $uuid,
    'name' => 'Test',
    'email' => 'test@test.com',
    'password' => 'test',
    'role' => 'dosen'
]);

DB::table('dosens')->insert([
    'dosen_id' => $dosenId,
    'user_id' => $uuid,
    'nip' => '123',
    'name' => 'Dr Test'
]);

DB::table('mata_kuliah')->insert([
    'mata_kuliah_id' => $mkId,
    'kode' => 'MK1',
    'mata_kuliah' => 'Test',
    'sks' => 3,
    'dosen_id' => $dosenId
]);

DB::table('kelas')->insert([
    'kelas_id' => $kelasId,
    'mata_kuliah_id' => $mkId,
    'kode' => 'K1',
    'kelas' => 'Class'
]);

$service = new ClassroomService();
$meetingForm = [
    'title' => 'Test Meeting',
    'date' => date('Y-m-d'),
    'learning_model' => 'pbl'
];

echo "Saving meeting...\n";
$meeting = $service->saveMeeting($kelasId, $meetingForm);
echo "Meeting saved: " . $meeting->pertemuan_id . "\n";

$steps = [
    [
        'title' => 'Step 1',
        'sub_steps' => ['Read the book'],
        'materials' => [
            ['title' => 'Chapter 1', 'content' => 'Introduction']
        ],
        'soals' => [
            ['title' => 'Quiz 1', 'instruction' => 'Answer questions']
        ]
    ]
];

echo "Saving syntax...\n";
try {
    $result = $service->saveSyntax($meeting->pertemuan_id, 'pbl', $steps);
    echo "Syntax saved: " . ($result ? "Yes" : "No") . "\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
