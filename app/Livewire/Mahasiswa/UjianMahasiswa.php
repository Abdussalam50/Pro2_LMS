<?php

namespace App\Livewire\Mahasiswa;

use Livewire\Component;
use App\Models\Ujian;
use App\Models\MahasiswaData;
use Illuminate\Support\Facades\DB;

class UjianMahasiswa extends Component
{
    public $ujians;

    public function mount()
    {
        $this->loadUjians();
    }

    public function loadUjians()
    {
        $userId = auth()->id();
        
        $mahasiswa = \App\Models\MahasiswaData::where('user_id', $userId)->first();

        if ($mahasiswa) {
            $kelasIds = $mahasiswa->kelass()->pluck('kelas.kelas_id')->toArray();
            $mahasiswaId = $mahasiswa->mahasiswa_id;

            $this->ujians = Ujian::with(['mataKuliah', 'dosen', 'soalUjians'])
                ->whereIn('kelas_id', $kelasIds)
                ->where('is_active', true)
                ->orderBy('waktu_mulai', 'asc')
                ->get()
                ->map(function ($ujian) use ($mahasiswaId) {
                    $ujian->has_submitted = \App\Models\NilaiUjianMahasiswa::where('ujian_id', $ujian->ujian_id)
                        ->where('mahasiswa_id', $mahasiswaId)
                        ->exists();
                    return $ujian;
                });
        } else {
            $this->ujians = collect();
        }
    }

    protected function getMahasiswaId()
    {
        $user = auth()->user();
        return $user?->mahasiswa?->mahasiswa_id;
    }

    public function render()
    {
        return view('livewire.mahasiswa.ujian-mahasiswa')
            ->layout('components.layout');
    }
}
