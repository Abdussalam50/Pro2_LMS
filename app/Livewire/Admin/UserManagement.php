<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\DosenData;
use App\Models\MahasiswaData;
use App\Models\Kelas;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $currentTab = 'mahasiswa';
    
    public $showModal = false;
    public $editingUser = null;
    
    // Form fields
    public $name;
    public $email;
    public $password;
    public $role = 'mahasiswa';
    
    // Role specific fields
    public $nim;           // Mahasiswa
    public $angkatan;      // Mahasiswa
    public $program_studi; // Mahasiswa
    public $kelas_id;      // Mahasiswa
    public $kode_dosen;    // Dosen
    public $no_wa;         // Both
    public $is_active = true;

    protected $queryString = ['search', 'currentTab'];

    public function updatedSearch() { $this->resetPage(); }
    public function setTab($tab) { 
        $this->currentTab = $tab; 
        $this->resetPage(); 
    }

    public function openModal($userId = null)
    {
        $this->resetValidation();
        $this->resetForm();
        
        if ($userId) {
            $this->editingUser = User::find($userId);
            $this->name = $this->editingUser->name;
            $this->email = $this->editingUser->email;
            $this->role = $this->editingUser->role;
            
            if ($this->role === 'mahasiswa' && $this->editingUser->mahasiswa) {
                $m = $this->editingUser->mahasiswa;
                $this->nim = $m->nim;
                $this->angkatan = $m->angkatan;
                $this->program_studi = $m->program_studi;
                $this->kelas_id = $m->kelas_id;
                $this->no_wa = $m->no_wa;
            } elseif ($this->role === 'dosen' && $this->editingUser->dosen) {
                $d = $this->editingUser->dosen;
                $this->kode_dosen = $d->kode;
                $this->no_wa = $d->no_wa;
            }
            $this->is_active = $this->editingUser->is_active;
        } else {
            $this->is_active = true;
        }
        
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->editingUser = null;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = 'mahasiswa';
        $this->nim = '';
        $this->angkatan = '';
        $this->program_studi = '';
        $this->kelas_id = '';
        $this->kode_dosen = '';
        $this->no_wa = '';
        $this->is_active = true;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . ($this->editingUser->id ?? 'NULL'),
            'role' => 'required|in:admin,dosen,mahasiswa',
        ];

        if (!$this->editingUser) {
            $rules['password'] = 'required|min:6';
        }

        if ($this->role === 'mahasiswa') {
            $rules['nim'] = 'required';
            $rules['kelas_id'] = 'required';
        } elseif ($this->role === 'dosen') {
            $rules['kode_dosen'] = 'required';
        }

        $this->validate($rules);

        \DB::transaction(function () {
            if ($this->editingUser) {
                $this->editingUser->update([
                    'name' => $this->name,
                    'email' => $this->email,
                    'role' => $this->role,
                    'is_active' => $this->is_active,
                ]);
                
                if ($this->password) {
                    $this->editingUser->update(['password' => Hash::make($this->password)]);
                }
                
                $user = $this->editingUser;
            } else {
                $user = User::create([
                    'name' => $this->name,
                    'email' => $this->email,
                    'password' => Hash::make($this->password),
                    'role' => $this->role,
                    'is_active' => $this->is_active,
                ]);
            }

            if ($this->role === 'mahasiswa') {
                MahasiswaData::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'mahasiswa_id' => ($this->editingUser && $this->editingUser->mahasiswa) ? $this->editingUser->mahasiswa->mahasiswa_id : (string) Str::uuid(),
                        'nama' => $this->name,
                        'nim' => $this->nim,
                        'angkatan' => $this->angkatan,
                        'program_studi' => $this->program_studi,
                        'kelas_id' => $this->kelas_id,
                        'no_wa' => $this->no_wa,
                    ]
                );
            } elseif ($this->role === 'dosen') {
                DosenData::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'dosen_id' => ($this->editingUser && $this->editingUser->dosen) ? $this->editingUser->dosen->dosen_id : (string) Str::uuid(),
                        'nama' => $this->name,
                        'kode' => $this->kode_dosen,
                        'no_wa' => $this->no_wa,
                    ]
                );
            }
        });

        $this->closeModal();
        session()->flash('message', 'User berhasil disimpan.');
        $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'User berhasil disimpan.', 'icon' => 'success']);
    }

    public function delete($userId)
    {
        $user = User::find($userId);
        if ($user && $user->id !== auth()->id()) {
            if ($user->role === 'mahasiswa') $user->mahasiswa()?->delete();
            if ($user->role === 'dosen') $user->dosen()?->delete();
            $user->delete();
            session()->flash('message', 'User berhasil dihapus.');
            $this->dispatch('swal', ['title' => 'Dihapus!', 'text' => 'User berhasil dihapus.', 'icon' => 'success']);
        }
    }

    public function toggleStatus($userId)
    {
        $user = User::find($userId);
        if ($user && $user->id !== auth()->id()) {
            $user->is_active = !$user->is_active;
            $user->save();
            session()->flash('message', 'Status user berhasil diperbarui.');
            $this->dispatch('swal', ['title' => 'Diperbarui!', 'text' => 'Status user berhasil diperbarui.', 'icon' => 'info']);
        }
    }

    public function approveUser($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $user->is_active = true;
            $user->save();
            session()->flash('message', 'User berhasil disetujui dan diaktifkan.');
            $this->dispatch('swal', ['title' => 'Disetujui!', 'text' => 'User berhasil disetujui dan dapat login ke sistem.', 'icon' => 'success']);
        }
    }

    public function rejectUser($userId)
    {
        $this->delete($userId);
    }

    public function render()
    {
        $users = User::query()
            ->when($this->currentTab === 'pending', function ($q) {
                $q->where('is_active', false)->where('role', '!=', 'admin');
            })
            ->when($this->currentTab !== 'pending', function ($q) {
                $q->where('role', $this->currentTab);
            })
            ->when($this->search, function ($q) {
                $q->where(function($sq) {
                    $sq->where('name', 'like', '%' . $this->search . '%')
                       ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->with(['mahasiswa', 'dosen'])
            ->latest()
            ->paginate(10);

        return view('livewire.admin.user-management', [
            'users' => $users,
            'kelasList' => Kelas::all()
        ])->layout('components.layout');
    }
}
