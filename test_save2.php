<?php

use App\Services\ClassroomService;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\DosenData;
use App\Models\User;

$user = User::firstOrCreate(['email' => 'dosentest@test.com'], ['name' => 'Dosen Test', 'password' => bcrypt('password'), 'role' => 'dosen']);
$dosen = DosenData::firstOrCreate(['user_id' => $user->uuid], ['name' => 'Dr. Test', 'nip' => '12345']);
$mk = MataKuliah::firstOrCreate(['kode' => 'TC101'], ['mata_kuliah' => 'Test Course', 'dosen_id' => $dosen->dosen_id]);
$kelas = Kelas::firstOrCreate(['kode' => 'CA-101'], ['kelas' => 'Class A', 'mata_kuliah_id' => $mk->mata_kuliah_id]);

$service = new ClassroomService();

$meetingForm = [
    'title' => 'Test Meeting',
    'date' => date('Y-m-d'),
    'learning_model' => 'pbl'
];

echo "Saving meeting...\n";
$meeting = $service->saveMeeting($kelas->kelas_id, $meetingForm);
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
