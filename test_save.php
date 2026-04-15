<?php

use App\Services\ClassroomService;
use App\Models\Kelas;

$kelas = Kelas::first();
if (!$kelas) {
    echo "No class found\n";
    exit;
}

$service = new ClassroomService();

$meetingForm = [
    'title' => 'Test Meeting',
    'date' => date('Y-m-d'),
    'learning_model' => 'pbl'
];

echo "Saving meeting...\n";
$meeting = $service->saveMeeting($kelas->kelas_id, $meetingForm);

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

