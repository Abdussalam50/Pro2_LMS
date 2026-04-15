<?php

namespace App\Livewire\Lecturer;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\MataKuliah;
use App\Models\Kelas;
use App\Models\Pengumuman;
use App\Models\Notifikasi;
use App\Models\ExternalMateri;
use App\Models\User;
use App\Models\Pertemuan;
use App\Models\MasterSoal;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class Dashboard extends Component
{
    public $activeTab = 'courses';
    public $selectedMataKuliahId = 'all';
    public $selectedKelasId = 'all';
    public $selectedPertemuanId = 'all';

    // Ujian Modal State
    public $showUjianModal = false;
    public $ujianForm = [
        'ujian_id' => null,
        'mata_kuliah_id' => '',
        'kelas_id' => '',
        'nama_ujian' => '',
        'deskripsi' => '',
        'jenis_ujian' => 'uts',
        'waktu_mulai' => '',
        'waktu_selesai' => '',
        'jumlah_soal' => 0,
        'bobot_nilai' => 0,
        'is_active' => false,
        'is_open' => false,
        'mode_batasan' => 'open',
    ];

    #[On('openUjianModal')]
    public function openUjianModal($classId = null, $ujianId = null)
    {
        $this->resetUjianForm();
        
        if ($ujianId) {
            $ujian = \App\Models\Ujian::find($ujianId);
            if ($ujian) {
                $this->ujianForm = [
                    'ujian_id' => $ujian->ujian_id,
                    'mata_kuliah_id' => $ujian->mata_kuliah_id,
                    'kelas_id' => $ujian->kelas_id,
                    'nama_ujian' => $ujian->nama_ujian,
                    'deskripsi' => $ujian->deskripsi,
                    'jenis_ujian' => $ujian->jenis_ujian,
                    'waktu_mulai' => $ujian->waktu_mulai->format('Y-m-d\TH:i'),
                    'waktu_selesai' => $ujian->waktu_selesai->format('Y-m-d\TH:i'),
                    'jumlah_soal' => $ujian->jumlah_soal,
                    'bobot_nilai' => $ujian->bobot_nilai,
                    'is_active' => $ujian->is_active,
                    'is_open' => $ujian->is_open,
                    'mode_batasan' => $ujian->mode_batasan,
                ];
            }
        } elseif ($classId) {
            $kelas = Kelas::with('mataKuliah')->find($classId);
            if ($kelas) {
                $this->ujianForm['kelas_id'] = $classId;
                $this->ujianForm['mata_kuliah_id'] = $kelas->mata_kuliah_id;
            }
        }

        $this->showUjianModal = true;
    }

    public function resetUjianForm()
    {
        $this->ujianForm = [
            'ujian_id' => null,
            'mata_kuliah_id' => '',
            'kelas_id' => '',
            'nama_ujian' => '',
            'deskripsi' => '',
            'jenis_ujian' => 'uts',
            'waktu_mulai' => '',
            'waktu_selesai' => '',
            'jumlah_soal' => 0,
            'bobot_nilai' => 0,
            'is_active' => false,
            'is_open' => false,
            'mode_batasan' => 'open',
        ];
    }

    public function saveUjian()
    {
        $this->validate([
            'ujianForm.mata_kuliah_id' => 'required',
            'ujianForm.kelas_id' => 'required',
            'ujianForm.nama_ujian' => 'required|string|max:255',
            'ujianForm.waktu_mulai' => 'required|date',
            'ujianForm.waktu_selesai' => 'required|date|after:ujianForm.waktu_mulai',
            'ujianForm.jumlah_soal' => 'required|integer|min:1',
        ]);

        $dosenId = $this->getDosenId();
        if (!$dosenId) return;

        $data = $this->ujianForm;
        $data['dosen_id'] = $dosenId;

        if ($data['ujian_id']) {
            \App\Models\Ujian::find($data['ujian_id'])->update($data);
            session()->flash('message', 'Ujian berhasil diperbarui.');
            $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Ujian berhasil diperbarui.', 'icon' => 'success']);
        } else {
            \App\Models\Ujian::create($data);
            session()->flash('message', 'Ujian berhasil dibuat.');
            $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Ujian berhasil dibuat.', 'icon' => 'success']);
        }

        $this->showUjianModal = false;
        // Check if on correct tab to see the update
        if ($this->activeTab !== 'exams') {
             // Optional: switch tab or show success
        }
    }

    public function mount()
    {
        $this->selectedMataKuliahId = 'all';
        $this->selectedKelasId = 'all';
        $this->selectedPertemuanId = 'all';
    }

    protected function getDosenId()
    {
        $user = auth()->user() ?? User::where('email', 'dosen@lms.com')->first();
        if ($user) {
            $dosen = DB::table('dosens')->where('user_id', $user->id)->first();
            return $dosen ? $dosen->dosen_id : null;
        }
        return null;
    }

    #[Computed]
    public function stats()
    {
        $dosenId = $this->getDosenId();
        if (!$dosenId) return ['mahasiswa' => 0, 'materi' => 0, 'diskusi' => 0];

        $kelasIds = Kelas::whereHas('mataKuliah', function($q) use ($dosenId) {
            $q->where('dosen_id', $dosenId);
        })->pluck('kelas_id');

        $totalMahasiswa = DB::table('mahasiswas')
            ->whereIn('kelas_id', $kelasIds)
            ->count();

        $totalMateri = ExternalMateri::whereIn('mata_kuliah_id', MataKuliah::where('dosen_id', $dosenId)->pluck('mata_kuliah_id'))->count();

        $unreadDiskusi = Notifikasi::where('user_id', auth()->id())
            ->where('dibaca', false)
            ->whereIn('tipe', ['diskusi_kelompok', 'diskusi_dosen'])
            ->count();

        return [
            'mahasiswa' => $totalMahasiswa,
            'materi' => $totalMateri,
            'diskusi' => $unreadDiskusi
        ];
    }

    #[Computed]
    public function upcomingAssignments()
    {
        $dosenId = $this->getDosenId();
        if (!$dosenId) return [];

        return MasterSoal::whereHas('tahapanSintaks.sintaksBelajar.pertemuan.kelas.mataKuliah', function($q) use ($dosenId) {
            $q->where('dosen_id', $dosenId);
        })
        ->where('tenggat_waktu', '>=', now())
        ->with(['tahapanSintaks.sintaksBelajar.pertemuan.kelas'])
        ->orderBy('tenggat_waktu', 'asc')
        ->take(5)
        ->get();
    }

    #[Computed]
    public function kelasList()
    {
        $dosenId = $this->getDosenId();
        if (!$dosenId) return [];

        $query = Kelas::whereHas('mataKuliah', function($q) use ($dosenId) {
            $q->where('dosen_id', $dosenId);
        });

        if ($this->selectedMataKuliahId !== 'all') {
            $query->where('mata_kuliah_id', $this->selectedMataKuliahId);
        }

        return $query->get();
    }

    #[Computed]
    public function pertemuanList()
    {
        $query = Pertemuan::query();

        if ($this->selectedKelasId !== 'all') {
            $query->where('kelas_id', $this->selectedKelasId);
        } elseif ($this->selectedMataKuliahId !== 'all') {
            $query->whereHas('kelas', function($q) {
                $q->where('mata_kuliah_id', $this->selectedMataKuliahId);
            });
        } else {
            $dosenId = $this->getDosenId();
            if (!$dosenId) return [];
            $query->whereHas('kelas.mataKuliah', function($q) use ($dosenId) {
                $q->where('dosen_id', $dosenId);
            });
        }

        return $query->select('pertemuan_id', 'kelas_id', 'created_at')->orderBy('created_at', 'asc')->get();
    }

    #[Computed]
    public function gradeChartData()
    {
        $dosenId = $this->getDosenId();
        if (!$dosenId) return ['labels' => [], 'data' => []];

        // CASE 1: all/all/all -> Average per Mata Kuliah
        if ($this->selectedMataKuliahId === 'all' && $this->selectedKelasId === 'all' && $this->selectedPertemuanId === 'all') {
            $data = DB::table('mata_kuliah')
                ->leftJoin('kelas', 'mata_kuliah.mata_kuliah_id', '=', 'kelas.mata_kuliah_id')
                ->leftJoin('pertemuans', 'kelas.kelas_id', '=', 'pertemuans.kelas_id')
                ->leftJoin('sintaks_belajar', 'pertemuans.pertemuan_id', '=', 'sintaks_belajar.pertemuan_id')
                ->leftJoin('tahapan_sintaks', 'sintaks_belajar.sintaks_belajar_id', '=', 'tahapan_sintaks.sintaks_belajar_id')
                ->leftJoin('master_soal', 'tahapan_sintaks.tahapan_sintaks_id', '=', 'master_soal.tahapan_sintaks_id')
                ->leftJoin('jawaban_mahasiswa', 'master_soal.master_soal_id', '=', 'jawaban_mahasiswa.master_soal_id')
                ->where('mata_kuliah.dosen_id', $dosenId)
                ->select('mata_kuliah.mata_kuliah as label', DB::raw('COALESCE(AVG(jawaban_mahasiswa.nilai), 0) as average_grade'))
                ->groupBy('mata_kuliah.mata_kuliah_id', 'mata_kuliah.mata_kuliah')
                ->get();
        }
        // CASE 2: MK/all/Pertemuan -> Average per Class for specific meeting (MK can be all or specific)
        elseif ($this->selectedKelasId === 'all' && $this->selectedPertemuanId !== 'all') {
            $mkId = $this->selectedMataKuliahId;
            
            // If MK is 'all', find the MK of the selected pertemuan
            if ($mkId === 'all') {
                $pertemuan = DB::table('pertemuans')
                    ->join('kelas', 'pertemuans.kelas_id', '=', 'kelas.kelas_id')
                    ->where('pertemuan_id', $this->selectedPertemuanId)
                    ->select('kelas.mata_kuliah_id')
                    ->first();
                if ($pertemuan) {
                    $mkId = $pertemuan->mata_kuliah_id;
                }
            }

            $query = DB::table('kelas')
                ->join('mata_kuliah', 'kelas.mata_kuliah_id', '=', 'mata_kuliah.mata_kuliah_id')
                ->leftJoin('pertemuans', function($join) {
                    $join->on('kelas.kelas_id', '=', 'pertemuans.kelas_id')
                         ->where('pertemuans.pertemuan_id', '=', $this->selectedPertemuanId);
                })
                ->leftJoin('sintaks_belajar', 'pertemuans.pertemuan_id', '=', 'sintaks_belajar.pertemuan_id')
                ->leftJoin('tahapan_sintaks', 'sintaks_belajar.sintaks_belajar_id', '=', 'tahapan_sintaks.sintaks_belajar_id')
                ->leftJoin('master_soal', 'tahapan_sintaks.tahapan_sintaks_id', '=', 'master_soal.tahapan_sintaks_id')
                ->leftJoin('jawaban_mahasiswa', 'master_soal.master_soal_id', '=', 'jawaban_mahasiswa.master_soal_id')
                ->where('mata_kuliah.dosen_id', $dosenId);

            if ($mkId !== 'all') {
                $query->where('mata_kuliah.mata_kuliah_id', $mkId);
            }

            $data = $query->select('kelas.kelas as label', DB::raw('COALESCE(AVG(jawaban_mahasiswa.nilai), 0) as average_grade'))
                ->groupBy('kelas.kelas_id', 'kelas.kelas')
                ->get();
        }
        // CASE 3: MK/all/all -> Average per Class in that MK
        elseif ($this->selectedMataKuliahId !== 'all' && $this->selectedKelasId === 'all' && $this->selectedPertemuanId === 'all') {
            $data = DB::table('kelas')
                ->join('mata_kuliah', 'kelas.mata_kuliah_id', '=', 'mata_kuliah.mata_kuliah_id')
                ->leftJoin('pertemuans', 'kelas.kelas_id', '=', 'pertemuans.kelas_id')
                ->leftJoin('sintaks_belajar', 'pertemuans.pertemuan_id', '=', 'sintaks_belajar.pertemuan_id')
                ->leftJoin('tahapan_sintaks', 'sintaks_belajar.sintaks_belajar_id', '=', 'tahapan_sintaks.sintaks_belajar_id')
                ->leftJoin('master_soal', 'tahapan_sintaks.tahapan_sintaks_id', '=', 'master_soal.tahapan_sintaks_id')
                ->leftJoin('jawaban_mahasiswa', 'master_soal.master_soal_id', '=', 'jawaban_mahasiswa.master_soal_id')
                ->where('mata_kuliah.mata_kuliah_id', $this->selectedMataKuliahId)
                ->select('kelas.kelas as label', DB::raw('COALESCE(AVG(jawaban_mahasiswa.nilai), 0) as average_grade'))
                ->groupBy('kelas.kelas_id', 'kelas.kelas')
                ->get();
        }
        // CASE 4: MK/Class/all -> Average per Meeting (Progress)
        elseif ($this->selectedKelasId !== 'all' && $this->selectedPertemuanId === 'all') {
            $data = DB::table('pertemuans')
                ->leftJoin('sintaks_belajar', 'pertemuans.pertemuan_id', '=', 'sintaks_belajar.pertemuan_id')
                ->leftJoin('tahapan_sintaks', 'sintaks_belajar.sintaks_belajar_id', '=', 'tahapan_sintaks.sintaks_belajar_id')
                ->leftJoin('master_soal', 'tahapan_sintaks.tahapan_sintaks_id', '=', 'master_soal.tahapan_sintaks_id')
                ->leftJoin('jawaban_mahasiswa', 'master_soal.master_soal_id', '=', 'jawaban_mahasiswa.master_soal_id')
                ->where('pertemuans.kelas_id', $this->selectedKelasId)
                ->select(DB::raw('pertemuans.pertemuan_id as label_id'), DB::raw('COALESCE(AVG(jawaban_mahasiswa.nilai), 0) as average_grade'))
                ->groupBy('pertemuans.pertemuan_id', 'pertemuans.created_at')
                ->orderBy('pertemuans.created_at', 'asc')
                ->get();
            
            $data = $data->map(function($item, $index) {
                return (object)[
                    'label' => "Pertemuan " . ($index + 1),
                    'average_grade' => $item->average_grade
                ];
            });
        }
        // CASE 5: MK/Class/Pertemuan -> Specific Task View
        else {
            // Need all tasks for THIS meeting
            $data = DB::table('master_soal')
                ->join('tahapan_sintaks', 'master_soal.tahapan_sintaks_id', '=', 'tahapan_sintaks.tahapan_sintaks_id')
                ->join('sintaks_belajar', 'tahapan_sintaks.sintaks_belajar_id', '=', 'sintaks_belajar.sintaks_belajar_id')
                ->leftJoin('jawaban_mahasiswa', 'master_soal.master_soal_id', '=', 'jawaban_mahasiswa.master_soal_id')
                ->where('sintaks_belajar.pertemuan_id', $this->selectedPertemuanId)
                ->select('master_soal.master_soal as label', DB::raw('COALESCE(AVG(jawaban_mahasiswa.nilai), 0) as average_grade'))
                ->groupBy('master_soal.master_soal_id', 'master_soal.master_soal')
                ->get();
        }

        if ($data->isEmpty()) {
            return ['labels' => ['No Data'], 'data' => [0]];
        }

        return [
            'labels' => $data->pluck('label')->toArray(),
            'data' => $data->pluck('average_grade')->map(fn($v) => round($v, 2))->toArray()
        ];
    }

    public function updatedSelectedMataKuliahId($value)
    {
        $this->selectedKelasId = 'all';
        $this->selectedPertemuanId = 'all';
        $this->dispatch('chartUpdated', $this->gradeChartData);
    }

    public function updatedSelectedKelasId($value)
    {
        $this->selectedPertemuanId = 'all';
        $this->dispatch('chartUpdated', $this->gradeChartData);
    }

    public function updatedSelectedPertemuanId()
    {
        $this->dispatch('chartUpdated', $this->gradeChartData);
    }

    #[Computed]
    public function todaySchedule()
    {
        $dosenId = $this->getDosenId();
        if (!$dosenId) return [];

        $matkuls = MataKuliah::with('kelas')->where('dosen_id', $dosenId)->get();
        $schedule = [];
        foreach($matkuls as $mk) {
            foreach($mk->kelas as $cls) {
                $schedule[] = [
                    'time' => '10:00 - 12:00', // Mock time for now
                    'course' => $mk->mata_kuliah,
                    'class' => $cls->kelas,
                    'room' => 'Lab Komputer 1',
                    'status' => 'Upcoming'
                ];
            }
        }
        return $schedule;
    }

    #[Computed]
    public function courses()
    {
        $dosenId = $this->getDosenId();
        if (!$dosenId) return [];

        return MataKuliah::with('kelas')->where('dosen_id', $dosenId)->get()->map(function ($mk) {
            return [
                'id' => $mk->mata_kuliah_id,
                'name' => $mk->mata_kuliah,
                'code' => $mk->kode,
                'description' => 'Mata kuliah yang diampu.',
                'classes' => $mk->kelas->map(function ($k) {
                    return [
                        'id' => $k->kelas_id,
                        'name' => $k->kelas,
                        'code' => $k->kode
                    ];
                })->toArray()
            ];
        })->toArray();
    }

    #[Computed]
    public function announcements()
    {
        $dosenId = $this->getDosenId();
        if (!$dosenId) return [];

        return Pengumuman::where('dosen_id', $dosenId)->latest()->get()->map(function ($p) {
            return [
                'id' => $p->pengumuman_id,
                'title' => $p->judul,
                'content' => $p->konten,
                'created_at' => $p->created_at->format('Y-m-d'),
                'author_name' => 'Dosen Anda'
            ];
        })->toArray();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.lecturer.dashboard')
            ->layout('components.layout');
    }
}
