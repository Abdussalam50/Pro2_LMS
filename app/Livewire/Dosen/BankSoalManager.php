<?php

namespace App\Livewire\Dosen;

use Livewire\Component;
use App\Models\BankSoal;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class BankSoalManager extends Component
{
    use WithPagination;

    public $search = '';
    public $filterJenis = ''; // all, ujian, tugas
    public $confirmingDeletion = null;
    public $previewSoal = null;

    protected $queryString = ['search', 'filterJenis'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterJenis()
    {
        $this->resetPage();
    }

    public function deleteSoal($id)
    {
        $soal = BankSoal::where('bank_soal_id', $id)
            ->where('dosen_id', Auth::user()->dosen->dosen_id)
            ->first();

        if ($soal) {
            $soal->delete();
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Terhapus',
                'message' => 'Soal berhasil dihapus dari bank.',
                'timer' => 2000
            ]);
        }
        $this->confirmingDeletion = null;
    }

    public function showPreview($id)
    {
        $this->previewSoal = BankSoal::find($id);
    }

    public function closePreview()
    {
        $this->previewSoal = null;
    }

    public function render()
    {
        $dosenId = Auth::user()->dosen->dosen_id;
        
        $query = BankSoal::where('dosen_id', $dosenId)
            ->when($this->search, function($q) {
                $q->where('judul_soal', 'like', '%' . $this->search . '%')
                  ->orWhere('konten_soal', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterJenis, function($q) {
                $q->where('jenis', $this->filterJenis);
            })
            ->latest();

        return view('livewire.dosen.bank-soal-manager', [
            'bankSoals' => $query->paginate(12)
        ])->layout('components.layout');
    }
}
