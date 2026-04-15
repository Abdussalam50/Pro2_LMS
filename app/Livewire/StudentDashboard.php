<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\KelompokAnggota;
use App\Models\Pengumuman;
use Illuminate\Support\Facades\Auth;

class StudentDashboard extends Component
{
    public array $stats = ['kelas' => 0, 'pertemuan' => 0, 'materi' => 0, 'kelompok' => 0];
    public array $myClasses = [];
    public array $pengumuman = [];
    public array $myKelompok = [];
    public $selectedPeriod = 'active';
    public $activePeriodData = null;

    public function mount(): void
    {
        $this->activePeriodData = \App\Models\AcademicPeriod::getActive();
        $this->loadDashboardData();
    }

    public function updatedSelectedPeriod()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $userId = Auth::id();
        $kelasIds = $this->getKelasIds($userId);

        $this->loadStats($userId, $kelasIds);
        $this->loadPengumuman();
        $this->loadKelompok($userId);
        $this->loadRecentClasses($kelasIds);
    }

    private function getKelasIds(int $userId): array
    {
        $mahasiswa = \App\Models\MahasiswaData::where('user_id', $userId)->first();
        if (!$mahasiswa) return [];

        $periodId = $this->selectedPeriod;
        if ($periodId === 'active') {
            $periodId = $this->activePeriodData?->id;
        }

        // Get classes from pivot table filtered by period
        return \App\Models\Kelas::whereHas('mahasiswas', function($q) use ($mahasiswa) {
                $q->where('mahasiswas.mahasiswa_id', $mahasiswa->mahasiswa_id);
            })
            ->when($periodId, function($q) use ($periodId) {
                $q->where('academic_period_id', $periodId);
            })
            ->pluck('kelas_id')
            ->toArray();
    }

    public function getAvailablePeriodsProperty()
    {
        return \App\Models\AcademicPeriod::orderBy('name')->get();
    }

    private function loadStats(int $userId, array $kelasIds): void
    {
        $pertemuanCount = \App\Models\Pertemuan::whereIn('kelas_id', $kelasIds)->count();
        $kelompokCount  = KelompokAnggota::where('user_id', $userId)->count();

        $this->stats = [
            'kelas'     => count($kelasIds),
            'pertemuan' => $pertemuanCount,
            'kelompok'  => $kelompokCount,
            'materi'    => 0, // placeholder
        ];
    }

    private function loadPengumuman(): void
    {
        $this->pengumuman = Pengumuman::with('dosen')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($p) => [
                'id'     => $p->pengumuman_id,
                'judul'  => $p->judul,
                'konten' => $p->konten,
                'dosen'  => $p->dosen?->nama ?? 'Sistem',
                'waktu'  => $p->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    private function loadKelompok(int $userId): void
    {
        $this->myKelompok = KelompokAnggota::where('user_id', $userId)
            ->with(['kelompok.kelas.mataKuliah'])
            ->get()
            ->map(fn($a) => [
                'kelompok_id'  => $a->kelompok->kelompok_id ?? null,
                'nama'         => $a->kelompok->nama_kelompok ?? '-',
                'kelas'        => $a->kelompok->kelas->kelas ?? '-',
                'mata_kuliah'  => $a->kelompok->kelas?->mataKuliah?->mata_kuliah ?? '-',
                'peran'        => $a->peran,
            ])
            ->toArray();
    }

    private function loadRecentClasses(array $kelasIds): void
    {
        $this->myClasses = Kelas::with(['mataKuliah.dosen'])
            ->whereIn('kelas_id', $kelasIds)
            ->latest()
            ->take(3)
            ->get()
            ->map(fn($k) => [
                'id'          => $k->kelas_id,
                'name'        => $k->kelas,
                'course_name' => $k->mataKuliah->mata_kuliah ?? '-',
                'lecturer'    => $k->mataKuliah->dosen->name ?? '-',
                'meetings'    => \App\Models\Pertemuan::where('kelas_id', $k->kelas_id)->count(),
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.student-dashboard')
            ->layout('components.layout');
    }
}
