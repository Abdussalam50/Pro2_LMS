<?php

namespace App\Livewire\Dosen;

use Livewire\Component;
use App\Models\Ujian;
use App\Models\NilaiUjianMahasiswa;
use Illuminate\Support\Facades\Auth;

class ManageHasilUjian extends Component
{
    public $ujian;
    public $hasilUjians;

    public function mount($ujianId)
    {
        $this->ujian = Ujian::with(['mataKuliah', 'kelas'])
            ->where('ujian_id', $ujianId)
            ->firstOrFail();

        // Check if user is the lecturer for this exam
        $user = Auth::user();
        if ($user->role !== 'dosen' || $this->ujian->dosen_id !== $user->dosenData->dosen_id) {
             abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $this->loadHasil();
    }

    public function loadHasil()
    {
        $this->hasilUjians = NilaiUjianMahasiswa::with(['mahasiswa.user'])
            ->where('ujian_id', $this->ujian->ujian_id)
            ->get();
    }

    public function render()
    {
        return view('livewire.dosen.manage-hasil-ujian')
            ->layout('components.layout');
    }
}
