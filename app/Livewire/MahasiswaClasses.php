<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Kelas;
use App\Models\Kelompok;
use App\Models\KelompokAnggota;
use App\Models\MahasiswaData;
use Illuminate\Support\Facades\Auth;

class MahasiswaClasses extends Component
{
    public string $joinCode = '';
    public string $errorMessage = '';
    public string $successMessage = '';
    public array $myClasses = [];

    public function mount(): void
    {
        $this->loadClasses();
    }

    public function loadClasses(): void
    {
        $userId = Auth::id();
        $mahasiswa = MahasiswaData::where('user_id', $userId)->first();

        if (!$mahasiswa) {
            $this->myClasses = [];
            return;
        }

        $this->myClasses = $mahasiswa->kelass()
            ->with(['mataKuliah.dosen'])
            ->get()
            ->map(fn($k) => [
                'id'             => $k->kelas_id,
                'name'           => $k->kelas,
                'code'           => $k->kode,
                'course_name'    => $k->mataKuliah->mata_kuliah ?? '-',
                'course_code'    => $k->mataKuliah->kode ?? '-',
                'lecturer_name'  => $k->mataKuliah->dosen->nama ?? '-',
                'meetings_count' => \App\Models\Pertemuan::where('kelas_id', $k->kelas_id)->count(),
            ])
            ->toArray();
    }

    public function joinClass(): void
    {
        $this->errorMessage = '';
        $this->successMessage = '';

        if (empty(trim($this->joinCode))) {
            $this->errorMessage = 'Kode kelas tidak boleh kosong.';
            return;
        }

        $kelas = Kelas::with('mataKuliah')->where('kode', strtoupper(trim($this->joinCode)))->first();

        if (!$kelas) {
            $this->errorMessage = 'Kode kelas tidak ditemukan.';
            return;
        }

        $userId   = Auth::id();
        $mahasiswa = MahasiswaData::where('user_id', $userId)->first();

        if (!$mahasiswa) {
            $this->errorMessage = 'Data mahasiswa tidak ditemukan.';
            return;
        }

        // Cek apakah sudah terdaftar di kelas ini
        if ($mahasiswa->isEnrolledIn($kelas->kelas_id)) {
            $this->errorMessage = 'Kamu sudah terdaftar di kelas ini.';
            return;
        }

        // Cek apakah mahasiswa sudah ada di kelas lain untuk mata kuliah yang SAMA
        $existingClass = $mahasiswa->enrolledClassForCourse($kelas->mata_kuliah_id);

        if ($existingClass) {
             // We keep this check to warn them, but the actual removal happens on Dosen Approval
             session()->flash('warning', 'Anda sudah di kelas lain untuk matkul ini. Jika disetujui, progress kelas lama akan dihapus.');
        }

        // Cek request pending
        $existingRequest = \App\Models\EnrollmentRequest::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('kelas_id', $kelas->kelas_id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            $this->errorMessage = 'Kamu sudah mengajukan permintaan untuk bergabung ke kelas ini. Harap tunggu persetujuan dosen.';
            return;
        }

        // Buat request
        \App\Models\EnrollmentRequest::updateOrCreate(
            ['mahasiswa_id' => $mahasiswa->mahasiswa_id, 'kelas_id' => $kelas->kelas_id],
            ['status' => 'pending']
        );

        $this->joinCode = '';
        $this->successMessage = 'Permintaan bergabung ke kelas ' . $kelas->kelas . ' dikirim! Menunggu persetujuan dosen.';
        $this->loadClasses();
    }

    /**
     * Hapus semua progress mahasiswa dalam suatu kelas tertentu.
     */
    private function clearProgressForClass(MahasiswaData $mahasiswa, string $kelasId, int $userId): void
    {
        $mahasiswaId = $mahasiswa->mahasiswa_id;

        // 1. Hapus keanggotaan kelompok di kelas ini
        KelompokAnggota::whereHas('kelompok', fn($q) => $q->where('kelas_id', $kelasId))
            ->where('user_id', $userId)
            ->delete();

        // 2. Hapus jawaban tugas yang terkait kelas ini
        $masterSoalIds = \App\Models\MasterSoal::whereHas(
            'tahapanSintaks.sintaksBelajar.pertemuan',
            fn($q) => $q->where('kelas_id', $kelasId)
        )->pluck('master_soal_id');

        \App\Models\JawabanMahasiswa::whereIn('master_soal_id', $masterSoalIds)
            ->where('user_id', $userId)
            ->delete();

        // 3. Hapus presensi di kelas ini
        \App\Models\PresensiMahasiswa::whereHas('pertemuan', fn($q) => $q->where('kelas_id', $kelasId))
            ->where('mahasiswa_id', $mahasiswaId)
            ->delete();

        // 4. Hapus jawaban & nilai ujian di kelas ini
        $ujianIds = \App\Models\Ujian::where('kelas_id', $kelasId)->pluck('ujian_id');

        \App\Models\JawabanUjianMahasiswa::whereIn('ujian_id', $ujianIds)
            ->where('mahasiswa_id', $mahasiswaId)
            ->delete();

        \App\Models\NilaiUjianMahasiswa::whereIn('ujian_id', $ujianIds)
            ->where('mahasiswa_id', $mahasiswaId)
            ->delete();

        // 5. Hapus pesan diskusi di kelas ini
        \App\Models\DiskusiKelompok::whereHas('pertemuan', fn($q) => $q->where('kelas_id', $kelasId))
            ->where('user_id', $userId)
            ->delete();

        \App\Models\DiskusiKelas::whereHas('pertemuan', fn($q) => $q->where('kelas_id', $kelasId))
            ->where('user_id', $userId)
            ->delete();
    }

    public function render()
    {
        return view('livewire.mahasiswa-classes')
            ->layout('components.layout');
    }
}
