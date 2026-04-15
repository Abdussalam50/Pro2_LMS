<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\AcademicPeriod;
use App\Models\Kelas;
use App\Models\Pertemuan;
use App\Models\PresensiMahasiswa;
use Livewire\WithPagination;

class AcademicAudit extends Component
{
    use WithPagination;

    public $selectedPeriodId;
    public $search = '';

    public function mount()
    {
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        $this->selectedPeriodId = $activePeriod ? $activePeriod->id : AcademicPeriod::first()->id ?? null;
    }

    public function updatedSelectedPeriodId()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function exportCsv()
    {
        $period = AcademicPeriod::find($this->selectedPeriodId);
        $filename = "Audit_Akademik_" . str_replace(['/', ' '], '_', $period->name) . "_" . date('Ymd_His') . ".csv";
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'Mata Kuliah', 'Kelas', 'Dosen', 'Pertemuan Selesai', 'Progress (%)', 'Rata-rata Kehadiran (%)']);

            $data = $this->getAuditData();
            foreach ($data as $index => $row) {
                fputcsv($file, [
                    $index + 1,
                    $row['mata_kuliah'],
                    $row['kelas'],
                    $row['dosen'],
                    $row['completed_meetings'] . '/16',
                    $row['progress'],
                    $row['avg_attendance']
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getAuditData()
    {
        if (!$this->selectedPeriodId) return [];

        return Kelas::where('academic_period_id', $this->selectedPeriodId)
            ->with(['mataKuliah.dosen.user', 'mahasiswas'])
            ->when($this->search, function($q) {
                $q->where('kelas', 'like', '%' . $this->search . '%')
                  ->orWhereHas('mataKuliah', fn($mq) => $mq->where('mata_kuliah', 'like', '%' . $this->search . '%'));
            })
            ->get()
            ->map(function($kelas) {
                $totalMeetings = 16; // Standard
                $completedMeetings = Pertemuan::where('kelas_id', $kelas->kelas_id)->count();
                $progress = round(($completedMeetings / $totalMeetings) * 100);

                // Attendance Calculation
                $studentIds = $kelas->mahasiswas->pluck('mahasiswa_id')->toArray();
                $meetingIds = Pertemuan::where('kelas_id', $kelas->kelas_id)->pluck('pertemuan_id')->toArray();
                
                $totalPossibleAttendance = count($studentIds) * count($meetingIds);
                $actualAttendance = $totalPossibleAttendance > 0 
                    ? PresensiMahasiswa::whereIn('pertemuan_id', $meetingIds)
                        ->whereIn('mahasiswa_id', $studentIds)
                        ->where('status', 'hadir')
                        ->count()
                    : 0;

                $avgAttendance = $totalPossibleAttendance > 0 
                    ? round(($actualAttendance / $totalPossibleAttendance) * 100, 1) 
                    : 0;

                return [
                    'id' => $kelas->kelas_id,
                    'mata_kuliah' => $kelas->mataKuliah->mata_kuliah ?? '-',
                    'kelas' => $kelas->kelas,
                    'dosen' => $kelas->mataKuliah->dosen->user->name ?? '-',
                    'completed_meetings' => $completedMeetings,
                    'progress' => $progress,
                    'avg_attendance' => $avgAttendance,
                ];
            });
    }

    public function render()
    {
        $periods = AcademicPeriod::latest()->get();
        $auditData = $this->getAuditData();

        return view('livewire.admin.academic-audit', [
            'periods' => $periods,
            'auditData' => $auditData
        ])->layout('components.layout');
    }
}
