<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Kelompok;
use App\Models\KelompokAnggota;
use App\Models\User;
use App\Models\Kelas;
use App\Models\MataKuliah;
use Illuminate\Support\Facades\DB;

class DosenKelompok extends Component
{
    public $selectedKelasId = '';
    public $kelasList = [];
    
    // Data structures for the 2-column UI
    public $unassignedStudents = [];
    public $kelompokList = [];
    
    // Modal state
    public $newKelompokName = '';
    public $showCreateModal = false;
    
    // Edit Modal state
    public $editKelompokId = null;
    public $editKelompokName = '';
    public $showEditModal = false;

    public function mount()
    {
        $this->loadDosenClasses();
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

    public function loadDosenClasses()
    {
        $dosenId = $this->getDosenId();
        if (!$dosenId) return;

        // Fetch all classes taught by this lecturer
        $matkuls = MataKuliah::where('dosen_id', $dosenId)->with('kelas')->get();
        
        $classes = [];
        foreach ($matkuls as $mk) {
            foreach ($mk->kelas as $k) {
                $classes[] = [
                    'id' => $k->kelas_id,
                    'name' => "{$mk->mata_kuliah} - {$k->kelas} ({$mk->kode})"
                ];
            }
        }
        $this->kelasList = $classes;

        if (count($classes) > 0 && empty($this->selectedKelasId)) {
            $this->selectedKelasId = $classes[0]['id'];
            $this->loadKelompokData();
        }
    }

    public function updatedSelectedKelasId()
    {
        $this->loadKelompokData();
    }

    public function loadKelompokData()
    {
        if (empty($this->selectedKelasId)) return;
 
        // 1. Load Groups (Wadah Kelompok) and their members
        $groups = Kelompok::with(['users' => function($q) {
            $q->orderBy('name');
        }])->where('kelas_id', $this->selectedKelasId)
          ->orderBy('created_at')
          ->get();

        $this->kelompokList = $groups->map(function($g) {
            return [
                'id' => $g->kelompok_id,
                'name' => $g->nama_kelompok,
                'members' => $g->users->map(function($u) {
                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'role' => $u->pivot->peran
                    ];
                })->toArray()
            ];
        })->toArray();

        // Count assigned students so we can filter them out from the unassigned list
        $assignedUserIds = [];
        foreach ($this->kelompokList as $group) {
            foreach ($group['members'] as $member) {
                $assignedUserIds[] = $member['id'];
            }
        }

        // 2. Load Unassigned Students for this Class.
        // For simplicity right now, since we don't have a rigid kelas_mahasiswa pivot in this particular project,
        // we'll fetch all students and just treat them as "in class". In a real setup, we'd join with class enrollments.
        // Assuming every student should be able to be grouped:
        $allStudentsInClass = User::where('role', 'mahasiswa')
            ->whereHas('mahasiswa', function($q) {
                $q->where('kelas_id', $this->selectedKelasId);
            })
            ->orderBy('name')
            ->get();

        $this->unassignedStudents = $allStudentsInClass->filter(function($student) use ($assignedUserIds) {
            return !in_array($student->id, $assignedUserIds);
        })->map(function($s) {
            return [
                'id' => $s->id,
                'name' => $s->name,
                'email' => $s->email
            ];
        })->values()->toArray();
    }

    public function createKelompok()
    {
        $this->validate([
            'newKelompokName' => 'required|string|max:255'
        ]);

        if (empty($this->selectedKelasId)) return;

        Kelompok::create([
            'kelas_id' => $this->selectedKelasId,
            'nama_kelompok' => $this->newKelompokName
        ]);

        $this->newKelompokName = '';
        $this->showCreateModal = false;
        $this->loadKelompokData();
    }

    public function editKelompok($kelompokId, $currentName)
    {
        $this->editKelompokId = $kelompokId;
        $this->editKelompokName = $currentName;
        $this->showEditModal = true;
    }

    public function updateKelompok()
    {
        $this->validate([
            'editKelompokName' => 'required|string|max:255'
        ]);

        if ($this->editKelompokId) {
            Kelompok::where('kelompok_id', $this->editKelompokId)->update([
                'nama_kelompok' => $this->editKelompokName
            ]);
        }

        $this->showEditModal = false;
        $this->editKelompokId = null;
        $this->editKelompokName = '';
        $this->loadKelompokData();
    }

    public function deleteKelompok($kelompokId)
    {
        KelompokAnggota::where('kelompok_id', $kelompokId)->delete(); // Manual cascade
        Kelompok::where('kelompok_id', $kelompokId)->delete();
        $this->loadKelompokData();
    }

    // Handles the drag-and-drop sortable payload
    public function updateGroupAssignment($groupId, $userId)
    {
        // Remove the user from any existing group in this class first to prevent duplicates
        $classGroupIds = Kelompok::where('kelas_id', $this->selectedKelasId)->pluck('kelompok_id');
        KelompokAnggota::whereIn('kelompok_id', $classGroupIds)
                       ->where('user_id', $userId)
                       ->delete();

        if ($groupId !== 'unassigned') {
            KelompokAnggota::create([
                'kelompok_id' => $groupId,
                'user_id' => $userId,
                'peran' => 'anggota' // default role
            ]);
        }

        $this->loadKelompokData();
    }
    
    public function setAsKetua($groupId, $userId)
    {
        KelompokAnggota::where('kelompok_id', $groupId)
                       ->update(['peran' => 'anggota']); // Demote others

        KelompokAnggota::where('kelompok_id', $groupId)
                       ->where('user_id', $userId)
                       ->update(['peran' => 'ketua']); // Promote selected
                       
        $this->loadKelompokData();
    }

    public function render()
    {
        return view('livewire.dosen-kelompok')->layout('components.layout');
    }
}
