<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin
        \App\Models\User::create([
            'name' => 'Super Admin',
            'email' => 'admin@lms.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create Dosen
        $dosenUser = \App\Models\User::create([
            'name' => 'Dr. Wahyu',
            'email' => 'dosen@lms.com',
            'password' => bcrypt('password'),
            'role' => 'dosen',
            'is_active' => true,
        ]);

        \Illuminate\Support\Facades\DB::table('dosens')->insert([
            'dosen_id' => (string) \Illuminate\Support\Str::uuid(),
            'nama' => $dosenUser->name,
            'user_id' => $dosenUser->id,
            'kode' => 'DSN' . rand(100, 999),
            'no_wa' => '081234567890',
            'foto' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Mahasiswa
        $mhsUser = \App\Models\User::create([
            'name' => 'Mahasiswa Teladan',
            'email' => 'mahasiswa@lms.com',
            'password' => bcrypt('password'),
            'role' => 'mahasiswa',
            'is_active' => true,
        ]);

        $kelasId = \App\Models\Kelas::first()->kelas_id ?? null;

        \Illuminate\Support\Facades\DB::table('mahasiswas')->insert([
            'mahasiswa_id' => (string) \Illuminate\Support\Str::uuid(),
            'nama' => $mhsUser->name,
            'user_id' => $mhsUser->id,
            'nim' => '123456789',
            'angkatan' => '2023',
            'program_studi' => 'Informatika',
            'no_wa' => '089876543210',
            'foto' => 'default.png',
            'is_active' => true,
            'kode_verifikasi' => \Illuminate\Support\Str::random(10),
            'kelas_id' => $kelasId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
