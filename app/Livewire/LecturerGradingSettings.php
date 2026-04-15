<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Kelas;
use App\Models\GradingComponent;
use App\Services\GradingService;

class LecturerGradingSettings extends Component
{
    public $kelasId;
    public $kelas;
    public $components = [];
    
    // New component form
    public $showAddModal = false;
    public $newName = '';
    public $newWeight = 0;
    public $newMappingType = 'exam';
    
    // Status
    public $totalWeight = 0;

    protected $rules = [
        'components.*.weight' => 'required|numeric|min:0|max:100',
    ];

    public function mount($kelasId)
    {
        $this->kelasId = $kelasId;
        $this->kelas = Kelas::with('mataKuliah')->findOrFail($kelasId);
        $this->loadComponents();
    }

    public function loadComponents()
    {
        $service = app(GradingService::class);
        // This will also seed default components if they don't exist
        $service->getActiveComponents($this->kelasId); 
        
        $this->components = GradingComponent::where('kelas_id', $this->kelasId)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->toArray();
            
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->totalWeight = collect($this->components)->sum('weight');
    }

    public function updatedComponents()
    {
        $this->calculateTotal();
    }

    public function saveWeights()
    {
        $this->validate();

        if ($this->totalWeight != 100) {
            $this->dispatch('swal', [
                'title' => 'Gagal!',
                'text' => 'Total bobot harus tepat 100%. Saat ini: ' . $this->totalWeight . '%',
                'icon' => 'error'
            ]);
            return;
        }

        foreach ($this->components as $compData) {
            GradingComponent::where('id', $compData['id'])->update(['weight' => $compData['weight']]);
        }

        $this->dispatch('swal', [
            'title' => 'Tersimpan!',
            'text' => 'Konfigurasi bobot nilai berhasil disimpan.',
            'icon' => 'success'
        ]);
        
        $this->loadComponents();
    }

    public function openAddModal()
    {
        $this->newName = '';
        $this->newWeight = 0;
        $this->newMappingType = 'manual';
        $this->showAddModal = true;
    }

    public function addComponent()
    {
        $this->validate([
            'newName' => 'required|string|max:255',
            'newWeight' => 'required|numeric|min:0|max:100',
            'newMappingType' => 'required|in:assignment,exam,attendance,manual',
        ]);

        GradingComponent::create([
            'kelas_id' => $this->kelasId,
            'name' => $this->newName,
            'category' => strtolower(str_replace(' ', '_', $this->newName)), // legacy fallback
            'weight' => $this->newWeight,
            'is_default' => false,
            'mapping_type' => $this->newMappingType,
        ]);

        $this->showAddModal = false;
        $this->loadComponents();
        
        $this->dispatch('swal', [
            'title' => 'Ditambahkan!',
            'text' => 'Komponen nilai baru berhasil ditambahkan. Jangan lupa simpan bobot untuk menyesuaikan total.',
            'icon' => 'success'
        ]);
    }

    public function deleteComponent($id)
    {
        $comp = GradingComponent::find($id);
        if ($comp && !$comp->is_default) {
            $comp->delete();
            $this->loadComponents();
            $this->dispatch('swal', [
                'title' => 'Dihapus!',
                'text' => 'Komponen berhasil dihapus. Sesuaikan kembali bobot aktif agar menjadi 100%.',
                'icon' => 'success'
            ]);
        } else {
            $this->dispatch('swal', [
                'title' => 'Ditolak!',
                'text' => 'Komponen default (Tugas, UTS, UAS, dll) tidak dapat dihapus, tapi Anda bisa mengatur bobotnya menjadi 0.',
                'icon' => 'error'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.lecturer-grading-settings');
    }
}
