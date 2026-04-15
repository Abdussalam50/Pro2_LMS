<?php

namespace App\Livewire\Mahasiswa;

use Livewire\Component;
use App\Models\ExternalMateri;
use Illuminate\Support\Facades\Auth;

class ExternalMateriList extends Component
{
    public function render()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        $materiList = collect();
        if ($mahasiswa) {
            $mataKuliahIds = $mahasiswa->kelass()->with('mataKuliah')->get()
                ->pluck('mata_kuliah_id')
                ->filter()
                ->unique();

            if ($mataKuliahIds->isNotEmpty()) {
                $materiList = ExternalMateri::whereIn('mata_kuliah_id', $mataKuliahIds)
                    ->with('mataKuliah')
                    ->latest()
                    ->get();
            }
        }

        return view('livewire.mahasiswa.external-materi-list', [
            'materiList' => $materiList
        ])->layout('components.layout');
    }
}
