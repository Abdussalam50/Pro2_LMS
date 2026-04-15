<?php

namespace App\Livewire\Mahasiswa;

use Livewire\Component;
use App\Models\Ujian;
use App\Models\SoalUjian;
use Illuminate\Support\Facades\Auth;

class TakeUjian extends Component
{
    /** @var \App\Models\Ujian */
    public $ujian;
    public $soals;
    public $answers = [];
    public $securitySettings = [];
    public $classData = [];

    public function getIsReadonlyProperty()
    {
        return isset($this->classData['academic_period']) && !$this->classData['academic_period']['is_active'];
    }

    public function mount($ujianId)
    {
        $this->ujian = Ujian::with(['mataKuliah', 'kelas.academicPeriod', 'dosen', 'materiUjians'])
            ->where('ujian_id', $ujianId)
            ->firstOrFail();

        $service = new \App\Services\ClassroomService();
        $this->classData = $service->getClassData($this->ujian->kelas_id);

        // Security check: ensure student belongs to the class
        /** @var \App\Models\User $user */
        $user = Auth::user();
        /** @var \App\Models\MahasiswaData|null $mahasiswa */
        $mahasiswa = $user->mahasiswaData;
        
        if (!$mahasiswa || !optional($mahasiswa)->isEnrolledIn($this->ujian?->kelas_id)) {
            session()->flash('error', 'Anda tidak terdaftar di kelas ini.');
            return redirect()->route('mahasiswa.ujians');
        }

        // Timing check
        if (now()->isBefore(optional($this->ujian)->waktu_mulai)) {
            session()->flash('error', 'Ujian belum dimulai.');
            return redirect()->route('mahasiswa.ujians');
        }

        if (now()->isAfter(optional($this->ujian)->waktu_selesai)) {
            session()->flash('error', 'Waktu ujian telah berakhir.');
            return redirect()->route('mahasiswa.ujians');
        }

        if (!optional($this->ujian)->is_open) {
            session()->flash('error', 'Ujian sedang ditutup oleh dosen.');
            return redirect()->route('mahasiswa.ujians');
        }

        // Check if student has already submitted this exam
        $alreadySubmitted = \App\Models\NilaiUjianMahasiswa::where('ujian_id', $this->ujian->ujian_id)
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->exists();

        if ($alreadySubmitted) {
            session()->flash('error', 'Anda sudah mengikuti dan mengumpulkan ujian ini.');
            return redirect()->route('mahasiswa.ujians');
        }

        $query = $this->ujian->soalUjians();
        if (optional($this->ujian)->is_random) {
            // Use session to persist randomization seed for this user and this exam
            $seedKey = "ujian_seed_" . optional($this->ujian)->ujian_id . "_" . auth()->id();
            $seed = session()->get($seedKey, function() use ($seedKey) {
                $newSeed = rand(1, 9999);
                session()->put($seedKey, $newSeed);
                return $newSeed;
            });
            $query->inRandomOrder($seed);
        }
        $this->soals = $query->get();

        // Load existing answers from DB
        $existingAnswers = \App\Models\JawabanUjianMahasiswa::where('ujian_id', $this->ujian->ujian_id)
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->get();

        foreach ($existingAnswers as $jawaban) {
            $this->answers[$jawaban->soal_id] = $jawaban->jawaban_pilihan_ganda ?? $jawaban->jawaban_esai;
        }

        // Initialize security settings via handler
        $this->securitySettings = $this->ujian->getHandler()->getSecuritySettings($this->ujian);
    }

    public function updatedAnswers($value, $key)
    {
        if ($this->isReadonly) return;
        
        // Auto-save specific answer to DB to prevent data loss
        $soalId = $key;
        $mahasiswa = Auth::user()->mahasiswaData;
        if (!$mahasiswa) return;

        $soal = $this->soals->where('soal_id', $soalId)->first();
        if (!$soal) return;

        $isBenar = false;
        $skor = 0;
        if ($soal->pilihan_ganda) {
            $isBenar = (trim((string) $value) == trim((string) $soal->jawaban_benar));
            $skor = $isBenar ? $soal->bobot : 0;
        }

        \App\Models\JawabanUjianMahasiswa::updateOrCreate(
            [
                'ujian_id' => optional($this->ujian)->ujian_id,
                'soal_id' => $soalId,
                'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            ],
            [
                'jawaban_pilihan_ganda' => $soal->pilihan_ganda ? $value : null,
                'jawaban_esai' => !$soal->pilihan_ganda ? $value : null,
                'is_benar' => $isBenar,
                'skor' => $skor,
            ]
        );
    }

    public function submit()
    {
        if ($this->isReadonly) return;
        
        if (now()->isAfter(optional($this->ujian)->waktu_selesai->addMinutes(2))) { // 2 min grace period
             session()->flash('error', 'Waktu ujian telah berakhir. Jawaban tidak dapat dikumpulkan.');
             return redirect()->route('mahasiswa.ujians');
        }

        $mahasiswa = Auth::user()->mahasiswaData;
        if (!$mahasiswa) {
            session()->flash('error', 'Data mahasiswa tidak ditemukan.');
            return redirect()->route('mahasiswa.ujians');
        }
        $totalSkor = 0;

        foreach ($this->soals as $soal) {
            $jawabanValue = $this->answers[$soal->soal_id] ?? null;
            $isBenar = false;
            $skor = 0;

            if ($soal->pilihan_ganda) {
                $isBenar = (trim((string) $jawabanValue) == trim((string) $soal->jawaban_benar));
                $skor = $isBenar ? $soal->bobot : 0;
            }

            \App\Models\JawabanUjianMahasiswa::updateOrCreate(
                [
                    'ujian_id' => optional($this->ujian)->ujian_id,
                    'soal_id' => $soal->soal_id,
                    'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                ],
                [
                    'jawaban_pilihan_ganda' => $soal->pilihan_ganda ? $jawabanValue : null,
                    'jawaban_esai' => !$soal->pilihan_ganda ? $jawabanValue : null,
                    'is_benar' => $isBenar,
                    'skor' => $skor,
                ]
            );

            $totalSkor += $skor;
        }

        \App\Models\NilaiUjianMahasiswa::updateOrCreate(
            [
                'ujian_id' => optional($this->ujian)->ujian_id,
                'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            ],
            [
                'kelas_id' => optional($this->ujian)->kelas_id,
                'mata_kuliah_id' => optional($this->ujian)->mata_kuliah_id,
                'dosen_id' => optional($this->ujian)->dosen_id,
                'nilai' => $totalSkor,
            ]
        );

        session()->flash('message', 'Jawaban berhasil dikumpulkan.');
        return redirect()->route('mahasiswa.ujians');
    }

    public function render()
    {
        return view('livewire.mahasiswa.take-ujian')
            ->layout('components.layout');
    }
}
