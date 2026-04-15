<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\ClassroomService;
use App\Models\KelompokAnggota;
use App\Models\PresensiCode;
use App\Models\PresensiMahasiswa;
use Illuminate\Support\Facades\Auth;

class StudentClassDetail extends Component
{
    public $classId;
    public $activeTab = 'materials';
    public $attendanceCode = '';
    public $attendanceStatus = null;
    public $kelompokId = null; 
    public $classData = [];
    public $meetings = [];
    public $activePresensi = null;
    public $rekapData = [];
    public $attendanceRate = 0;
    public $assignmentProgress = 0;
    public $activeMeetingId = null;
    public $gradingWeights = [];
    public $categoryScores = [];
    public $finalGrade = 0;

    public function getIsReadonlyProperty()
    {
        return isset($this->classData['academic_period']) && !$this->classData['academic_period']['is_active'];
    }


    public function mount($id)
    {
        $this->classId = $id;

        // Validasi akses: Pastikan mahasiswa terdaftar di kelas ini (via pivot)
        $mahasiswa = Auth::user()->mahasiswa;
        if (!$mahasiswa || !$mahasiswa->isEnrolledIn($id)) {
            session()->flash('error', 'Anda tidak terdaftar di kelas ini.');
            return redirect('/mahasiswa/classes');
        }

        $this->loadData();
        $this->loadKelompok();
        $this->checkActivePresensi();
        $this->loadRekapitulasi();
    }

    private function checkActivePresensi()
    {
        $this->activePresensi = PresensiCode::where('is_active', true)
            ->whereHas('pertemuan', function($q) {
                $q->where('kelas_id', $this->classId);
            })
            ->with('pertemuan')
            ->latest()
            ->first();
    }

    private function loadKelompok(): void
    {
        $userId = Auth::id();
        $anggota = KelompokAnggota::whereHas('kelompok', fn($q) => $q->where('kelas_id', $this->classId))
            ->where('user_id', $userId)
            ->first();
        $this->kelompokId = $anggota?->kelompok_id;
    }

    public function loadData()
    {
        $service = new ClassroomService();
        $classData = $service->getClassData($this->classId);
        
        if ($classData) {
            $this->classData = $classData;
            $this->meetings = $service->getMeetings($this->classId);

            // Default to the LATEST meeting for the sidebar chat widget
            if (!empty($this->meetings) && !$this->activeMeetingId) {
                $latestMeeting = end($this->meetings);
                $this->activeMeetingId = $latestMeeting['id'];
            }
        } else {
             // Fallback Dummy Data
             $this->classData = [
                'id' => $this->classId,
                'name' => 'Kelas A (Mock)',
                'code' => 'PWA',
                'course_name' => 'Pemrograman Web',
                'course_code' => 'IF201',
                'lecturer_name' => 'Dr. Wahyu',
            ];
            $this->meetings = [];
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        if ($tab === 'attendance') {
            $this->checkActivePresensi();
        }
        if ($tab === 'rekapitulasi') {
            $this->loadRekapitulasi();
        }
    }

    public function setActiveMeeting($id)
    {
        $this->activeMeetingId = $id;
    }


    public function submitAttendance()
    {
        if ($this->isReadonly) return;
        
        $this->attendanceStatus = null;
        
        if (empty(trim($this->attendanceCode))) {
            $this->attendanceStatus = 'error';
            return;
        }

        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;

        if (!$mahasiswa) {
            $this->attendanceStatus = 'error';
            return;
        }

        // Find active code
        $activeCode = PresensiCode::where('code', strtoupper(trim($this->attendanceCode)))
            ->where('is_active', true)
            ->whereHas('pertemuan', function($q) {
                $q->where('kelas_id', $this->classId);
            })
            ->first();

        if (!$activeCode) {
            $this->attendanceStatus = 'error';
            return;
        }

        try {
            // Record attendance
            PresensiMahasiswa::updateOrCreate(
                [
                    'pertemuan_id' => $activeCode->pertemuan_id,
                    'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                ],
                [
                    'status' => 'hadir',
                    'waktu_presensi' => now(),
                    'metode' => 'code',
                ]
            );

            $this->attendanceStatus = 'success';
            $this->attendanceCode = ''; // Reset code on success
            $this->loadRekapitulasi(); // Refresh stats
            $this->checkActivePresensi();
        } catch (\Exception $e) {
            $this->attendanceStatus = 'error';
        }
    }

    public function loadRekapitulasi()
    {
        $userId = Auth::id();
        $mahasiswa = Auth::user()->mahasiswa;

        if (!$mahasiswa) return;

        $gradingService = app(\App\Services\GradingService::class);

        // 1. New Weighted Calculations
        $this->attendanceRate = (int) $gradingService->calculateAttendanceScore($this->classId, $userId);
        $this->gradingWeights = $gradingService->getActiveWeights($this->classId);
        $this->categoryScores = $gradingService->getCategoryAverages($this->classId, $userId);
        $this->finalGrade = $gradingService->calculateFinalGrade($this->classId, $userId);

        // 2. Assignment List (For detailed table)
        $allAssignments = \App\Models\MasterSoal::whereHas('tahapanSintaks.sintaksBelajar.pertemuan', function($q) {
            $q->where('kelas_id', $this->classId);
        })->get();

        $assignmentIds = $allAssignments->pluck('master_soal_id');
        
        $allAnswers = \App\Models\JawabanMahasiswa::whereIn('master_soal_id', $assignmentIds)
            ->where('user_id', $userId)
            ->get()
            ->groupBy('master_soal_id');

        $assignmentStats = [];
        $gradedCount = 0;

        foreach ($allAssignments as $assignment) {
            $answers = $allAnswers->get($assignment->master_soal_id, collect());
            
            $status = 'Belum Mengerjakan';
            if ($answers->isNotEmpty()) {
                $status = $answers->whereNotNull('nilai')->isNotEmpty() ? 'Sudah Dinilai' : 'Sudah Dikerjakan';
            }

            $assignmentStats[] = [
                'id' => $assignment->master_soal_id,
                'title' => $assignment->master_soal,
                'deadline' => $assignment->tenggat_waktu ? $assignment->tenggat_waktu->format('d M Y, H:i') : '-',
                'score' => round($answers->avg('nilai') ?? 0),
                'is_graded' => $answers->whereNotNull('nilai')->isNotEmpty(),
                'status' => $status,
                'submitted' => $answers->isNotEmpty()
            ];

            if ($answers->isNotEmpty()) {
                $gradedCount++;
            }
        }

        $this->assignmentProgress = $allAssignments->count() > 0 ? round(($gradedCount / $allAssignments->count()) * 100) : 0;

        $this->rekapData = [
            'attendance' => [
                'rate' => $this->attendanceRate
            ],
            'assignments' => [
                'list' => $assignmentStats,
                'count' => count($allAssignments),
                'submitted_count' => $gradedCount
            ]
        ];
    }

    public function render()
    {
        return view('livewire.student-class-detail')
            ->layout('components.layout');
    }
}
