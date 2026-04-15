<?php

namespace App\Livewire\Dosen;

use Livewire\Component;
use App\Models\Ujian;
use App\Models\MataKuliah;
use App\Models\Pertemuan;
use App\Models\Kelas;
use App\Models\GradingComponent;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class ManageUjian extends Component
{
    public $ujians;
    public $mataKuliahs;
    public $kelases;
    public $gradingComponents = [];
    public $kelasId = null; // optional: filter by class when embedded as tab
    public $isCustomJenis = false;

    // Modal State - handled directly in this component
    public $showUjianModal = false;
    public $ujianForm = [
        'ujian_id' => null,
        'mata_kuliah_id' => '',
        'kelas_id' => '',
        'nama_ujian' => '',
        'deskripsi' => '',
        'grading_component_id' => '',
        'waktu_mulai' => '',
        'waktu_selesai' => '',
        'jumlah_soal' => 0,
        'bobot_nilai' => 0,
        'is_active' => false,
        'is_open' => false,
        'is_random' => false,
        'mode_batasan' => 'open',
        'custom_handler' => '',
        'custom_settings' => [],
    ];

    public function mount()
    {
        $this->loadInitialData();
        $this->loadUjians();
    }

    public function loadInitialData()
    {
        $dosenId = $this->getDosenId();
        if ($dosenId) {
            $this->mataKuliahs = MataKuliah::where('dosen_id', $dosenId)->get();
            $this->kelases = Kelas::whereIn('mata_kuliah_id', $this->mataKuliahs->pluck('mata_kuliah_id'))->get();
        }
    }

    #[On('refreshExams')]
    public function loadUjians()
    {
        $dosenId = $this->getDosenId();
        if ($dosenId) {
            $query = Ujian::with(['mataKuliah', 'kelas', 'gradingComponent'])
                ->where('dosen_id', $dosenId)
                ->latest();

            if ($this->kelasId) {
                $query->where('kelas_id', $this->kelasId);
            }

            $this->ujians = $query->get();
        }
    }

    protected function getDosenId()
    {
        $user = auth()->user();
        if ($user) {
            $dosen = DB::table('dosens')->where('user_id', $user->id)->first();
            return $dosen ? $dosen->dosen_id : null;
        }
        return null;
    }

    public function openModal($ujianId = null, $classId = null)
    {
        $this->resetUjianForm();

        if ($ujianId) {
            $ujian = Ujian::find($ujianId);
            if ($ujian) {
                $this->ujianForm = [
                    'ujian_id' => $ujian->ujian_id,
                    'mata_kuliah_id' => $ujian->mata_kuliah_id,
                    'kelas_id' => $ujian->kelas_id,
                    'nama_ujian' => $ujian->nama_ujian,
                    'deskripsi' => $ujian->deskripsi,
                    'grading_component_id' => $ujian->grading_component_id ?? '',
                    'waktu_mulai' => $ujian->waktu_mulai->format('Y-m-d\TH:i'),
                    'waktu_selesai' => $ujian->waktu_selesai->format('Y-m-d\TH:i'),
                    'jumlah_soal' => $ujian->jumlah_soal,
                    'bobot_nilai' => $ujian->bobot_nilai,
                    'is_active' => $ujian->is_active,
                    'is_open' => $ujian->is_open,
                    'is_random' => $ujian->is_random,
                    'mode_batasan' => $ujian->mode_batasan ?? 'open',
                    'custom_handler' => $ujian->custom_handler ?? '',
                    'custom_settings' => $ujian->custom_settings ?? [],
                ];

                $this->loadGradingComponents($ujian->kelas_id);
                $this->isCustomJenis = false;
            }
        } elseif ($classId ?? $this->kelasId) {
            $targetKelasId = $classId ?? $this->kelasId;
            $kelas = Kelas::with('mataKuliah')->find($targetKelasId);
            if ($kelas) {
                $this->ujianForm['kelas_id'] = $targetKelasId;
                $this->ujianForm['mata_kuliah_id'] = $kelas->mata_kuliah_id;
                $this->loadGradingComponents($targetKelasId);
            }
        }

        $this->showUjianModal = true;
    }

    public function resetUjianForm()
    {
        $this->ujianForm = [
            'ujian_id' => null,
            'mata_kuliah_id' => $this->kelasId ? (Kelas::find($this->kelasId)?->mata_kuliah_id ?? '') : '',
            'kelas_id' => $this->kelasId ?? '',
            'nama_ujian' => '',
            'deskripsi' => '',
            'grading_component_id' => '',
            'waktu_mulai' => '',
            'waktu_selesai' => '',
            'jumlah_soal' => 0,
            'bobot_nilai' => 0,
            'is_active' => false,
            'is_open' => false,
            'is_random' => false,
            'mode_batasan' => 'open',
            'custom_handler' => '',
            'custom_settings' => [],
        ];
        $this->isCustomJenis = false;
    }

    // Jembatan agar Livewire 3 mengenali perubahan di dalam array ujianForm
    public function updatedUjianForm($value, $name)
    {
        if ($name === 'mata_kuliah_id') {
            $this->updatedUjianFormMataKuliahId($value);
        }

        if ($name === 'kelas_id') {
            $this->updatedUjianFormKelasId($value);
        }
    }

    public function updatedUjianFormMataKuliahId($value)
    {
        $this->ujianForm['kelas_id'] = '';
        $this->gradingComponents = [];
    }

    public function updatedUjianFormKelasId($value)
    {
        if ($value) {
            $this->loadGradingComponents($value);
        } else {
            $this->gradingComponents = [];
        }
    }

    private function loadGradingComponents($kelasId)
    {
        $service = app(\App\Services\GradingService::class);
        $service->getActiveComponents($kelasId); // ensures defaults exist
        $this->gradingComponents = GradingComponent::where('kelas_id', $kelasId)->get();
    }

    public function saveUjian()
    {
        $this->validate([
            'ujianForm.mata_kuliah_id' => 'required',
            'ujianForm.kelas_id' => 'required',
            'ujianForm.grading_component_id' => 'required',
            'ujianForm.nama_ujian' => 'required|string|max:255',
            'ujianForm.waktu_mulai' => 'required|date',
            'ujianForm.waktu_selesai' => 'required|date|after:ujianForm.waktu_mulai',
            'ujianForm.jumlah_soal' => 'required|integer|min:1',
        ]);

        $dosenId = $this->getDosenId();
        if (!$dosenId)
            return;

        $data = $this->ujianForm;
        $data['dosen_id'] = $dosenId;

        if ($data['ujian_id']) {
            Ujian::find($data['ujian_id'])->update($data);
            session()->flash('message', 'Ujian berhasil diperbarui.');
            $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Ujian berhasil diperbarui.', 'icon' => 'success']);
        } else {
            Ujian::create($data);
            session()->flash('message', 'Ujian berhasil dibuat.');
            $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Ujian berhasil dibuat.', 'icon' => 'success']);
        }

        $this->showUjianModal = false;
        $this->loadUjians();
    }

    public function delete($id)
    {
        Ujian::destroy($id);
        $this->loadUjians();
        session()->flash('message', 'Ujian berhasil dihapus.');
        $this->dispatch('swal', [
            'title' => 'Dihapus!',
            'text' => 'Ujian berhasil dihapus.',
            'icon' => 'success'
        ]);
    }

    public function toggleActive($id)
    {
        $ujian = Ujian::find($id);
        $ujian->update(['is_active' => !$ujian->is_active]);
        $this->loadUjians();
    }

    public function toggleOpen($id)
    {
        $ujian = Ujian::find($id);
        $ujian->update(['is_open' => !$ujian->is_open]);
        $this->loadUjians();
    }

    public function render()
    {
        return view('livewire.dosen.manage-ujian');
    }
}
