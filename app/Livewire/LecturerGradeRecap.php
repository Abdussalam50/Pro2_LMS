<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Kelas;
use App\Models\Pertemuan;
use App\Models\MataKuliah;
use App\Models\JawabanMahasiswa;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LecturerGradeRecap extends Component
{
    public $courses = [];
    public $selectedCourseId = '';
    
    public $classes = [];
    public $selectedClassId = '';

    public $meetings = [];
    public $selectedMeetingId = '';

    public $students = [];
    public $assignments = [];
    public $grades = [];
    public $finalGrades = [];
    public $activeComponents = [];

    public function mount()
    {
        $this->loadCourses();
    }

    public function loadCourses()
    {
        $user = Auth::user();
        if ($user && $user->dosen) {
            $dosenId = $user->dosen->dosen_id;
            $this->courses = MataKuliah::where('dosen_id', $dosenId)
                ->get()
                ->toArray();
        } else {
            // Fallback for admin or testing
            $this->courses = MataKuliah::all()->toArray();
        }
    }

    public function updatedSelectedCourseId($courseId)
    {
        $this->selectedClassId = '';
        $this->selectedMeetingId = '';
        $this->classes = [];
        $this->meetings = [];
        $this->resetGrades();

        if ($courseId) {
            $this->classes = Kelas::where('mata_kuliah_id', $courseId)
                ->get()
                ->toArray();
        }
    }

    public function updatedSelectedClassId($classId)
    {
        $this->selectedMeetingId = '';
        $this->meetings = [];
        $this->resetGrades();

        if ($classId) {
             $this->meetings = Pertemuan::where('kelas_id', $classId)
                ->orderBy('created_at', 'asc')
                ->get()
                ->toArray();
            
            $this->loadClassData($classId);
        }
    }

    private function loadClassData($classId)
    {
        // 1. Get all students in this class (from enrollment)
        // In this system, we can find students who are linked to this class_id in mahasiswas table
        // or through the enrollments if that table exists. 
        // Based on previous migrations, mahasiswas has a kelas_id (nullable) or there is a pivot.
        
        $this->students = User::whereHas('mahasiswa', function($q) use ($classId) {
                $q->where('kelas_id', $classId);
            })
            ->with('mahasiswa')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'nim' => $user->mahasiswa->nim ?? '-',
                ];
            })
            ->sortBy('name')
            ->values()
            ->toArray();

        // 2. If no students found by kelas_id, fallback to students who have answers (legacy/flexible)
        if (empty($this->students)) {
            $userIds = JawabanMahasiswa::join('master_soal', 'jawaban_mahasiswa.master_soal_id', '=', 'master_soal.master_soal_id')
                ->join('tahapan_sintaks', 'master_soal.tahapan_sintaks_id', '=', 'tahapan_sintaks.tahapan_sintaks_id')
                ->join('sintaks_belajar', 'tahapan_sintaks.sintaks_belajar_id', '=', 'sintaks_belajar.sintaks_belajar_id')
                ->join('pertemuans', 'sintaks_belajar.pertemuan_id', '=', 'pertemuans.pertemuan_id')
                ->where('pertemuans.kelas_id', $classId)
                ->distinct()
                ->pluck('user_id');

            $this->students = User::whereIn('id', $userIds)
                ->with('mahasiswa')
                ->get()
                ->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'nim' => $user->mahasiswa->nim ?? '-',
                    ];
                })
                ->sortBy('name')
                ->values()
                ->toArray();
        }

        // 3. Calculate Final Weighted Grades using GradingService
        $gradingService = app(\App\Services\GradingService::class);
        $this->activeComponents = $gradingService->getActiveComponents($classId)->toArray();
        
        $this->finalGrades = [];
        foreach ($this->students as $index => $student) {
            // Optimization: Get averages once and reuse for final grade calculation
            $averages = $gradingService->getCategoryAverages($classId, $student['id']);
            $this->students[$index]['averages'] = $averages;
            $this->finalGrades[$student['id']] = $gradingService->calculateFinalGrade($classId, $student['id'], $averages);
        }
    }

    public function refreshData()
    {
        if ($this->selectedClassId) {
            $this->loadClassData($this->selectedClassId);
            $this->dispatch('swal', [
                'title' => 'Berhasil!',
                'text' => 'Data nilai telah diperbarui dari database.',
                'icon' => 'success'
            ]);
        }
    }

    public function updatedSelectedMeetingId($meetingId)
    {
        $this->resetGrades();
        if ($meetingId) {
            $this->loadGrades($meetingId);
        }
    }

    private function resetGrades()
    {
        $this->students = [];
        $this->assignments = [];
        $this->grades = [];
    }

    public function loadGrades($meetingId)
    {
        if (!$this->selectedClassId || !$meetingId) return;

        // 1. Get assignments for this meeting
        $pertemuan = Pertemuan::with(['sintaksBelajar.tahapanSintaks.masterSoal'])->find($meetingId);
        if (!$pertemuan || !$pertemuan->sintaksBelajar) return;

        $meetingAssignments = collect();
        foreach ($pertemuan->sintaksBelajar->tahapanSintaks as $tahapan) {
            $meetingAssignments = $meetingAssignments->concat($tahapan->masterSoal);
        }
        $this->assignments = $meetingAssignments->toArray();
        $assignmentIds = $meetingAssignments->pluck('master_soal_id')->toArray();

        if(empty($assignmentIds)) {
            return; // No assignments in this meeting
        }

        // 2. Get students in this class
        $kelas = Kelas::find($this->selectedClassId);
        
        // This query fetches users who either are enrolled directly in the class 
        // normally we would fetch from a class_user table, but here we fetch anyone who 
        // has answered the questions for this class/meeting to be safe or use a known list if available.
        // Assuming user->mahasiswa relation exists
        
        // Find users who have submitted answers for these assignments
        $userIds = JawabanMahasiswa::whereIn('master_soal_id', $assignmentIds)
            ->distinct()
            ->pluck('user_id');
            
        $this->students = User::whereIn('id', $userIds)
            ->with('mahasiswa')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'nim' => $user->mahasiswa->nim ?? '-',
                ];
            })
            ->sortBy('name')
            ->values()
            ->toArray();

        // 3. Get Grades
        $rawGrades = JawabanMahasiswa::whereIn('master_soal_id', $assignmentIds)
            ->whereIn('user_id', $userIds)
            ->select('user_id', 'master_soal_id', DB::raw('SUM(nilai) as total_nilai'))
            ->groupBy('user_id', 'master_soal_id')
            ->get();

        $gradeMap = [];
        foreach ($rawGrades as $grade) {
            $gradeMap[$grade->user_id][$grade->master_soal_id] = $grade->total_nilai;
        }
        
        $this->grades = $gradeMap;

        // 4. Set final grades and averages (Optional: re-calculate or just keep from loadClassData)
        // Since loadClassData already runs GradingService, we only need local data for specific meeting display if needed.
        // For now, the global averages are what we show in the main table.
    }

    public function render()
    {
        return view('livewire.lecturer-grade-recap')
            ->layout('components.layout');
    }
}
