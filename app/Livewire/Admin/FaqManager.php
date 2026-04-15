<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Faq;
use Livewire\WithPagination;

class FaqManager extends Component
{
    use WithPagination;

    public $pertanyaan;
    public $jawaban;
    public $kategori;
    public $urutan = 0;
    public $is_active = true;
    
    public $editingId = null;
    public $showForm = false;

    protected $rules = [
        'pertanyaan' => 'required|min:5|max:255',
        'jawaban' => 'required|min:10',
        'kategori' => 'nullable|string|max:50',
        'urutan' => 'integer|min:0',
    ];

    public function createFaq()
    {
        $this->validate();

        Faq::create([
            'pertanyaan' => $this->pertanyaan,
            'jawaban' => $this->jawaban,
            'kategori' => $this->kategori,
            'urutan' => $this->urutan,
            'is_active' => $this->is_active,
        ]);

        $this->reset(['pertanyaan', 'jawaban', 'kategori', 'urutan', 'showForm']);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Tersimpan', 'text' => 'FAQ baru telah berhasil ditambahkan.']);
    }

    public function editFaq($id)
    {
        $faq = Faq::findOrFail($id);
        $this->editingId = $id;
        $this->pertanyaan = $faq->pertanyaan;
        $this->jawaban = $faq->jawaban;
        $this->kategori = $faq->kategori;
        $this->urutan = $faq->urutan;
        $this->is_active = $faq->is_active;
        $this->showForm = true;
    }

    public function updateFaq()
    {
        $this->validate();

        Faq::findOrFail($this->editingId)->update([
            'pertanyaan' => $this->pertanyaan,
            'jawaban' => $this->jawaban,
            'kategori' => $this->kategori,
            'urutan' => $this->urutan,
            'is_active' => $this->is_active,
        ]);

        $this->reset(['pertanyaan', 'jawaban', 'kategori', 'urutan', 'editingId', 'showForm']);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Terupdate', 'text' => 'FAQ telah diperbarui.']);
    }

    public function deleteFaq($id)
    {
        Faq::findOrFail($id)->delete();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Terhapus', 'text' => 'FAQ telah berhasil dihapus.']);
    }

    public function toggleActive($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->update(['is_active' => !$faq->is_active]);
    }

    public function render()
    {
        return view('livewire.admin.faq-manager', [
            'faqs' => Faq::orderBy('urutan')->orderBy('created_at', 'desc')->paginate(10)
        ])->layout('components.layout');
    }
}
