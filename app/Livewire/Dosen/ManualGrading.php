<?php

namespace App\Livewire\Dosen;

use Livewire\Component;
use App\Models\Ujian;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ManualGrading extends Component
{
    public $ujianId;
    public $ujian;
    public $students = [];
    public $scores = []; // student_id => nilai
    public $search = '';

    public function mount($ujianId)
    {
        $this->ujianId = $ujianId;
        $this->ujian = Ujian::with(['mataKuliah', 'kelas', 'gradingComponent'])->findOrFail($ujianId);
        $this->loadStudentsAndScores();
    }

    public function loadStudentsAndScores()
    {
        // Get all students enrolled in this class
        $this->students = User::whereHas('mahasiswa', function($q) {
                $q->where('kelas_id', $this->ujian->kelas_id);
            })
            ->with('mahasiswa')
            ->when($this->search, function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhereHas('mahasiswa', function($sq) {
                      $sq->where('nim', 'like', '%' . $this->search . '%');
                  });
            })
            ->get()
            ->sortBy('name')
            ->values()
            ->toArray();

        // Get existing scores
        // The table is nilai_ujians_mahasiswa which relies on mahasiswa_id or user_id. Let's check table structure commonly used here.
        // In GradingService we saw: DB::table('nilai_ujians_mahasiswa')->join('mahasiswas', '...')->where('user_id', $userId)
        // Let's rely on standard practice for this app: nilai_ujians_mahasiswa has mahasiswa_id.
        $mahasiswaIds = array_column(array_column($this->students, 'mahasiswa'), 'mahasiswa_id');

        $existingScores = DB::table('nilai_ujians_mahasiswa')
            ->where('ujian_id', $this->ujianId)
            ->whereIn('mahasiswa_id', $mahasiswaIds)
            ->get()
            ->pluck('nilai', 'mahasiswa_id')
            ->toArray();

        // Populate scores array indexed by user id for livewire binding ease
        foreach ($this->students as $student) {
            $mId = $student['mahasiswa']['mahasiswa_id'];
            $this->scores[$student['id']] = $existingScores[$mId] ?? null;
        }
    }

    public function updatedSearch()
    {
        $this->loadStudentsAndScores();
    }

    public function saveAll()
    {
        $upsertData = [];
        $now = now();
        
        foreach ($this->students as $student) {
            $userId = $student['id'];
            $mId = $student['mahasiswa']['mahasiswa_id'];
            $nilai = $this->scores[$userId];

            if ($nilai !== null && $nilai !== '') {
                // Ensure max 100
                $nilai = min(100, max(0, floatval($nilai)));
                
                $upsertData[] = [
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'ujian_id' => $this->ujianId,
                    'mahasiswa_id' => $mId,
                    'nilai' => $nilai,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (count($upsertData) > 0) {
            // Because laravel upsert requires unique keys and standard UUID isn't naturally uniquely composite unless set,
            // we will do it via delete/insert or updateOrCreate logic to be safe based on existing migrations.
            DB::beginTransaction();
            try {
                foreach ($upsertData as $data) {
                    DB::table('nilai_ujians_mahasiswa')->updateOrInsert(
                        ['ujian_id' => $data['ujian_id'], 'mahasiswa_id' => $data['mahasiswa_id']],
                        ['nilai' => $data['nilai'], 'updated_at' => $now]
                    );
                }
                DB::commit();
                
                $this->dispatch('swal', [
                    'title' => 'Tersimpan!',
                    'text' => 'Nilai berhasil disimpan.',
                    'icon' => 'success'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                $this->dispatch('swal', [
                    'title' => 'Gagal!',
                    'text' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
                    'icon' => 'error'
                ]);
            }
        } else {
            $this->dispatch('swal', [
                'title' => 'Info',
                'text' => 'Tidak ada nilai yang diinput.',
                'icon' => 'info'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.dosen.manual-grading')
            ->layout('components.layout');
    }
}
