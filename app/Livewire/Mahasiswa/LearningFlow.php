<?php

namespace App\Livewire\Mahasiswa;

use Livewire\Component;
use App\Models\Pertemuan;
use App\Models\SintaksBelajar;
use App\Models\TahapanSintaks;
use App\Models\Materi;
use App\Models\MasterSoal;
use App\Models\JawabanMahasiswa;
use App\Models\TahapanCompletion;
use Illuminate\Support\Facades\Auth;

class LearningFlow extends Component
{
    public $kelasId;
    public $pertemuanId;
    public $pertemuan;
    public $sintaks;
    public $embedded = false;

    public $activeTahapId = null;
    public $activeTab = 'materi'; // materi, tugas, diskusi

    public $tahapanList = [];

    public function mount($kelasId, $pertemuanId, $embedded = false)
    {
        $this->kelasId = $kelasId;
        $this->pertemuanId = $pertemuanId;
        $this->embedded = $embedded;

        // Validasi akses: Pastikan akses diizinkan (mahasiswa terdaftar ATAU dosen sedang pratinjau)
        $user = Auth::user();
        if ($user->role !== 'dosen') {
            $mahasiswa = $user->mahasiswa;
            if (!$mahasiswa || !$mahasiswa->isEnrolledIn($kelasId)) {
                session()->flash('error', 'Anda tidak memiliki akses ke materi/alur belajar kelas ini.');
                return redirect('/mahasiswa/classes');
            }
        }
        
        $this->loadData();
    }

    public function loadData()
    {
        $this->pertemuan = Pertemuan::find($this->pertemuanId);
        if ($this->pertemuan) {
            $sintaksModel = SintaksBelajar::with(['tahapanSintaks' => function($q) {
                $q->with('kegiatan')->orderBy('urutan');
            }])->where('pertemuan_id', $this->pertemuanId)->first();
            
            $this->sintaks = $sintaksModel;

            if ($sintaksModel && $sintaksModel->tahapanSintaks->count() > 0) {
                // Get permanent completion record from database
                $completedIds = TahapanCompletion::where('user_id', Auth::id())
                    ->where('pertemuan_id', $this->pertemuanId)
                    ->pluck('tahapan_sintaks_id')
                    ->toArray();

                $this->tahapanList = $sintaksModel->tahapanSintaks->map(function($tahap) use ($completedIds) {
                    $isCompleted = in_array($tahap->tahapan_sintaks_id, $completedIds);
                    return [
                        'id' => $tahap->tahapan_sintaks_id,
                        'urutan' => $tahap->urutan,
                        'nama' => $tahap->nama_tahapan,
                        'kegiatan' => $tahap->kegiatan->pluck('kegiatan')->toArray(),
                        'status' => $isCompleted ? 'completed' : 'active'
                    ];
                })->toArray();

                $this->activeTahapId = $this->tahapanList[0]['id'];

                // Auto-advance to the last unlocked/active stage
                foreach ($this->tahapanList as $t) {
                    if ($t['status'] === 'active') {
                        $this->activeTahapId = $t['id'];
                        break;
                    }
                }
            }
        }
    }

    public function setActiveTahap($tahapId)
    {
        $this->activeTahapId = $tahapId;
        $this->activeTab = 'materi'; // reset tab when changing stage
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function markAsCompleted()
    {
        // 1. Check if current stage has mandatory task
        $task = MasterSoal::where('tahapan_sintaks_id', $this->activeTahapId)->first();
        if ($task) {
            $isSubmitted = JawabanMahasiswa::where('master_soal_id', $task->master_soal_id)
                ->where('user_id', Auth::id())
                // In some cases we might use is_submitted flag, 
                // but checking existence is often enough for "done" status
                ->exists();

            if (!$isSubmitted) {
                session()->flash('error_message', 'Tugas di tahap ini belum dikerjakan. Selesaikan tugas sebelum lanjut.');
                return;
            }
        }

        // 2. Move to next stage logic
        $currentIndex = -1;
        foreach ($this->tahapanList as $index => $t) {
            if ($t['id'] === $this->activeTahapId) {
                $currentIndex = $index;
                break;
            }
        }
        
        if ($currentIndex !== -1) {
            // Save permanently to database
            TahapanCompletion::updateOrCreate([
                'user_id' => Auth::id(),
                'tahapan_sintaks_id' => $this->activeTahapId,
                'pertemuan_id' => $this->pertemuanId
            ], [
                'status' => 'completed'
            ]);

            $this->tahapanList[$currentIndex]['status'] = 'completed';
            
            // Unlock next if exists
            if (isset($this->tahapanList[$currentIndex + 1])) {
                $this->tahapanList[$currentIndex + 1]['status'] = 'active';
                $this->activeTahapId = $this->tahapanList[$currentIndex + 1]['id'];
                $this->activeTab = 'materi';
            } else {
                session()->flash('success_message', 'Semua tahapan telah selesai!');
            }
        }
    }

    public function getActiveTahapProperty()
    {
        foreach ($this->tahapanList as $t) {
            if ($t['id'] === $this->activeTahapId) {
                return $t;
            }
        }
        return null;
    }

    public function getActiveTahapMateriProperty()
    {
        if (!$this->activeTahapId) return null;
        return Materi::where('tahapan_sintaks_id', $this->activeTahapId)->first();
    }

    public function getActiveTahapTugasProperty()
    {
        if (!$this->activeTahapId) return null;
        return MasterSoal::where('tahapan_sintaks_id', $this->activeTahapId)->first();
    }

    public function render()
    {
        if ($this->embedded) {
            return view('livewire.mahasiswa.learning-flow-inline');
        }
        return view('livewire.mahasiswa.learning-flow')->layout('components.layout');
    }
}
