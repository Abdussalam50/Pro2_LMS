<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Models\MahasiswaData;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;

class RegisterMahasiswa extends Component
{
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $nim;
    public $angkatan;
    public $program_studi;
    public $no_wa;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'nim' => 'required|string|max:20|unique:mahasiswas,nim',
        'angkatan' => 'required|string',
        'program_studi' => 'required|string',
        'no_wa' => 'required|string',
    ];

    public function register()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => 'mahasiswa',
            'is_active' => false,
        ]);

        MahasiswaData::create([
            'mahasiswa_id' => (string) Str::uuid(),
            'nama' => $this->name,
            'user_id' => $user->id,
            'nim' => $this->nim,
            'angkatan' => $this->angkatan,
            'program_studi' => $this->program_studi,
            'no_wa' => $this->no_wa,
            'foto' => '', // Default empty or a placeholder
            'is_active' => false, // Auto active for now, or change based on policy
            'kode_verifikasi' => Str::random(6),
        ]);

        session()->flash('message', 'Registrasi berhasil! Akun Anda sedang menunggu persetujuan Admin.');
        return $this->redirect('/login', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register-mahasiswa');
    }
}
