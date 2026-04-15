<?php

namespace App\Livewire;

use Livewire\Component;

use App\Models\Kelas;
use App\Models\Pertemuan;
use App\Models\PresensiCode;
use App\Models\PresensiMahasiswa;
use App\Models\MahasiswaData;
use App\Models\DiskusiKelas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LecturerAttendance extends Component
{
    public $classes = [];
    public $meetings = [];
    public $students = [];
    
    public $selectedClass = '';
    public $selectedMeeting = '';
    
    public $activeCode = null;
    public $attendanceRecords = [];
    
    public $showQR = false;
    public $qrUrl = '';
    public $duration = 60; // Default 60 minutes

    public function mount()
    {
        $dosen = Auth::user()->dosen;
        if (!$dosen) {
            return redirect('/dashboard');
        }

        $this->classes = Kelas::whereHas('mataKuliah', function($q) use ($dosen) {
            $q->where('dosen_id', $dosen->dosen_id);
        })
            ->with('mataKuliah')
            ->get()
            ->map(fn($c) => [
                'id' => $c->kelas_id,
                'name' => $c->kelas . ' - ' . ($c->mataKuliah->mata_kuliah ?? ''),
            ])
            ->toArray();
    }

    public function updatedSelectedClass($value)
    {
        $this->selectedMeeting = '';
        $this->activeCode = null;
        $this->attendanceRecords = [];
        
        if ($value) {
            $this->meetings = Pertemuan::where('kelas_id', $value)
                ->orderBy('pertemuan')
                ->get()
                ->map(fn($m) => [
                    'id' => $m->pertemuan_id,
                    'title' => 'Pertemuan ' . $m->pertemuan . ($m->tanggal ? ' (' . $m->tanggal . ')' : ''),
                ])
                ->toArray();
        } else {
            $this->meetings = [];
        }
    }

    public function updatedSelectedMeeting($value)
    {
        $this->loadAttendanceData();
    }

    public function loadAttendanceData()
    {
        if (!$this->selectedMeeting) return;

        // Load active code
        $code = PresensiCode::where('pertemuan_id', $this->selectedMeeting)
            ->where('is_active', true)
            ->latest()
            ->first();
        
        $this->activeCode = $code ? $code->toArray() : null;

        // Load attendance records
        $records = PresensiMahasiswa::where('pertemuan_id', $this->selectedMeeting)
            ->with('mahasiswa.user')
            ->get();

        // Get all students in class
        $allStudents = MahasiswaData::whereHas('kelass', fn($q) => $q->where('kelas_mahasiswa.kelas_id', $this->selectedClass))
            ->with('user')
            ->get();

        $this->attendanceRecords = $allStudents->map(function($s) use ($records) {
            $record = $records->where('mahasiswa_id', $s->mahasiswa_id)->first();
            return [
                'id' => $s->mahasiswa_id,
                'name' => $s->user->name ?? '?',
                'nim' => $s->nim,
                'status' => $record->status ?? 'belum_absen',
                'waktu' => $record ? $record->waktu_presensi->format('H:i') : '-',
                'metode' => $record->metode ?? '-',
            ];
        })->toArray();
    }

    public function generateCode($type = 'text')
    {
        $this->validate([
            'selectedMeeting' => 'required',
            'duration' => 'required|numeric|min:1'
        ]);

        // Deactivate old codes
        PresensiCode::where('pertemuan_id', $this->selectedMeeting)->update(['is_active' => false]);

        $codeStr = strtoupper(Str::random(6));
        
        $code = PresensiCode::create([
            'pertemuan_id' => $this->selectedMeeting,
            'type' => $type,
            'code' => $codeStr,
            'expires_at' => now()->addMinutes((int)$this->duration),
            'is_active' => true,
        ]);

        $this->activeCode = $code->toArray();
        
        if ($type === 'qr') {
            // In a real app, generate QR URL. For now, we use a placeholder or manual generator
            $this->qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($codeStr);
            $this->showQR = true;
        }

        // Send Notification to Class
        try {
            $fcm = app(\App\Services\FirebaseNotificationService::class);
            $pertemuan = Pertemuan::find($this->selectedMeeting);
            $fcm->sendToKelas(
                $this->selectedClass,
                'Presensi Dibuka!',
                'Presensi untuk Pertemuan ' . ($pertemuan->pertemuan ?? '') . ' telah dibuka. Silakan masukkan kode atau scan QR.',
                [
                    'type' => 'attendance',
                    'pertemuan_id' => $this->selectedMeeting,
                    'kelas_id' => $this->selectedClass,
                    'url' => '/mahasiswa/classes/' . $this->selectedClass . '?tab=attendance'
                ]
            );
        } catch (\Exception $e) {
            // Log or ignore notification failure
        }

        // Auto-post to Diskusi Kelas
        try {
            DiskusiKelas::create([
                'pertemuan_id' => $this->selectedMeeting,
                'user_id' => Auth::id(),
                'pesan' => "📌 **PRESENSI DIBUKA**: Silakan gunakan kode `{$codeStr}` untuk absensi hari ini. Berlaku selama {$this->duration} menit.",
            ]);
        } catch (\Exception $e) {
            // Ignore if discussion table has issues
        }

        $this->dispatch('attendance-code-generated', code: $codeStr);
        $this->loadAttendanceData();
    }

    public function toggleCode()
    {
        if ($this->activeCode) {
            PresensiCode::where('presensi_code_id', $this->activeCode['presensi_code_id'])
                ->update(['is_active' => !$this->activeCode['is_active']]);
            $this->loadAttendanceData();
        }
    }

    public function render()
    {
        return view('livewire.lecturer-attendance')
            ->layout('components.layout');
    }
}
