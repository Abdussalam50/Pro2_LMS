<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\AcademicPeriod;
use App\Models\Kelas;
use App\Models\User;
use App\Models\MahasiswaData;
use App\Models\NilaiUjianMahasiswa;
use App\Models\JawabanMahasiswa;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class GradeRecap extends Component
{
    use WithPagination;

    public $selectedPeriodId;
    public $selectedClassId;
    public $search = '';

    public function mount()
    {
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        $this->selectedPeriodId = $activePeriod ? $activePeriod->id : AcademicPeriod::first()->id ?? null;
    }

    public function updatedSelectedPeriodId()
    {
        $this->selectedClassId = null;
        $this->resetPage();
    }

    public function updatedSelectedClassId()
    {
        $this->resetPage();
    }

    public function getClassesProperty()
    {
        if (!$this->selectedPeriodId) return [];
        
        $query = Kelas::where('academic_period_id', $this->selectedPeriodId)
            ->with('mataKuliah');

        // Filter for lecturers
        if (auth()->user()->role === 'dosen' && auth()->user()->dosen) {
            $dosenId = auth()->user()->dosen->dosen_id;
            $query->whereHas('mataKuliah', function($q) use ($dosenId) {
                $q->where('dosen_id', $dosenId);
            });
        }

        return $query->get();
    }

    public function calculateGrades()
    {
        if (!$this->selectedClassId) return [];

        $kelas = Kelas::with(['mahasiswas.user', 'academicPeriod'])->find($this->selectedClassId);
        if (!$kelas) return [];

        $period = $kelas->academicPeriod;
        $students = $kelas->mahasiswas;

        $results = [];
        foreach ($students as $mhs) {
            // 1. Average Tugas
            // We find all master_soal linked to meetings in this class
            $avgTugas = JawabanMahasiswa::whereHas('masterSoal.tahapanSintaks.sintaksBelajar.pertemuan', function($q) use ($kelas) {
                    $q->where('kelas_id', $kelas->kelas_id);
                })
                ->where('user_id', $mhs->user_id)
                ->avg('nilai') ?? 0;

            // 2. UTS
            $uts = NilaiUjianMahasiswa::where('kelas_id', $kelas->kelas_id)
                ->where('mahasiswa_id', $mhs->mahasiswa_id)
                ->whereHas('ujian', fn($q) => $q->where('jenis_ujian', 'uts'))
                ->value('nilai') ?? 0;

            // 3. UAS
            $uas = NilaiUjianMahasiswa::where('kelas_id', $kelas->kelas_id)
                ->where('mahasiswa_id', $mhs->mahasiswa_id)
                ->whereHas('ujian', fn($q) => $q->where('jenis_ujian', 'uas'))
                ->value('nilai') ?? 0;

            // 4. Final Score Calculation
            $wTask = $period->weight_task / 100;
            $wMid = $period->weight_mid / 100;
            $wFinal = $period->weight_final / 100;

            $finalScore = ($avgTugas * $wTask) + ($uts * $wMid) + ($uas * $wFinal);
            $finalScore = round($finalScore, 2);

            // 5. Letter Grade
            $gradeLetter = $this->getGradeLetter($finalScore);

            $results[] = [
                'nim' => $mhs->nim,
                'name' => $mhs->user->name ?? '-',
                'tugas' => round($avgTugas, 1),
                'uts' => $uts,
                'uas' => $uas,
                'akhir' => $finalScore,
                'huruf' => $gradeLetter
            ];
        }

        // Apply Search
        if ($this->search) {
            $results = array_filter($results, function($item) {
                return str_contains(strtolower($item['name']), strtolower($this->search)) || 
                       str_contains(strtolower($item['nim']), strtolower($this->search));
            });
        }

        return $results;
    }

    private function getGradeLetter($score)
    {
        if ($score >= 85) return 'A';
        if ($score >= 75) return 'B';
        if ($score >= 65) return 'C';
        if ($score >= 50) return 'D';
        return 'E';
    }

    public function exportCsv()
    {
        if (!$this->selectedClassId) return;

        $kelas = Kelas::find($this->selectedClassId);
        $filename = "Rekap_Nilai_" . str_replace([' ', '/'], '_', $kelas->kelas) . "_" . date('Ymd') . ".csv";
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['NIM', 'Nama Mahasiswa', 'Rerata Tugas', 'UTS', 'UAS', 'Nilai Akhir', 'Huruf']);

            $data = $this->calculateGrades();
            foreach ($data as $row) {
                fputcsv($file, [$row['nim'], $row['name'], $row['tugas'], $row['uts'], $row['uas'], $row['akhir'], $row['huruf']]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.admin.grade-recap', [
            'periods' => AcademicPeriod::latest()->get(),
            'gradeData' => $this->calculateGrades()
        ])->layout('components.layout');
    }
}
