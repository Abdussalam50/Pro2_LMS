<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\AcademicPeriod;
use Livewire\WithPagination;

class PeriodManager extends Component
{
    use WithPagination;

    public $name;
    public $tahun;
    public $semester = 'ganjil';
    public $weight_task = 40;
    public $weight_mid = 30;
    public $weight_final = 30;
    public $is_active = false;

    public $editingId = null;
    public $showForm = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'tahun' => 'required|string|max:20',
        'semester' => 'required|in:ganjil,genap',
        'weight_task' => 'required|integer|min:0|max:100',
        'weight_mid' => 'required|integer|min:0|max:100',
        'weight_final' => 'required|integer|min:0|max:100',
    ];

    public function createPeriod()
    {
        $this->validate();

        // Check if total weight is 100
        if (($this->weight_task + $this->weight_mid + $this->weight_final) !== 100) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Bobot Salah', 'text' => 'Total bobot (Tugas+UTS+UAS) harus 100%.']);
            return;
        }

        AcademicPeriod::create([
            'name' => $this->name,
            'tahun' => $this->tahun,
            'semester' => $this->semester,
            'weight_task' => $this->weight_task,
            'weight_mid' => $this->weight_mid,
            'weight_final' => $this->weight_final,
            'is_active' => $this->is_active,
        ]);

        $this->reset(['name', 'tahun', 'semester', 'weight_task', 'weight_mid', 'weight_final', 'is_active', 'showForm']);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Tersimpan', 'text' => 'Periode akademik baru berhasil dibuat.']);
    }

    public function editPeriod($id)
    {
        $period = AcademicPeriod::findOrFail($id);
        $this->editingId = $id;
        $this->name = $period->name;
        $this->tahun = $period->tahun;
        $this->semester = $period->semester;
        $this->weight_task = $period->weight_task;
        $this->weight_mid = $period->weight_mid;
        $this->weight_final = $period->weight_final;
        $this->is_active = $period->is_active;
        $this->showForm = true;
    }

    public function updatePeriod()
    {
        $this->validate();

        if (($this->weight_task + $this->weight_mid + $this->weight_final) !== 100) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Bobot Salah', 'text' => 'Total bobot harus 100%.']);
            return;
        }

        AcademicPeriod::findOrFail($this->editingId)->update([
            'name' => $this->name,
            'tahun' => $this->tahun,
            'semester' => $this->semester,
            'weight_task' => $this->weight_task,
            'weight_mid' => $this->weight_mid,
            'weight_final' => $this->weight_final,
            'is_active' => $this->is_active,
        ]);

        $this->reset(['name', 'tahun', 'semester', 'weight_task', 'weight_mid', 'weight_final', 'is_active', 'editingId', 'showForm']);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Terupdate', 'text' => 'Periode akademik berhasil diperbarui.']);
    }

    public function setActive($id)
    {
        // Deactivate all
        AcademicPeriod::where('is_active', true)->update(['is_active' => false]);
        // Activate current
        AcademicPeriod::findOrFail($id)->update(['is_active' => true]);
        
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Semester Aktif', 'text' => 'Semester berjalan telah diubah.']);
    }

    public function deletePeriod($id)
    {
        $period = AcademicPeriod::findOrFail($id);
        if ($period->classes()->count() > 0) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Gagal', 'text' => 'Tidak bisa menghapus periode yang sudah memiliki kelas aktif.']);
            return;
        }
        $period->delete();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Terhapus', 'text' => 'Periode akademik telah dihapus.']);
    }

    public function render()
    {
        return view('livewire.admin.period-manager', [
            'periods' => AcademicPeriod::latest()->paginate(10)
        ])->layout('components.layout');
    }
}
