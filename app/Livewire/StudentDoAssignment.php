<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MasterSoal;
use App\Models\MainSoal;
use App\Models\Soal;
use App\Models\PilihanGanda;
use App\Models\JawabanMahasiswa;
use Illuminate\Support\Facades\Auth;

class StudentDoAssignment extends Component
{
    public string $masterSoalId;
    public array $assignment = [];
    public array $pages = [];      // setiap halaman = satu main_soal
    public int $currentPage = 0;
    public array $answers = [];    // [soal_id => jawaban/pilihan]
    public array $feedback = [];   // [soal_id => ['score' => x, 'note' => y]]
    public bool $submitted = false;
    public bool $sudahDikerjakan = false;
    public array $classData = [];

    public function getIsReadonlyProperty()
    {
        return isset($this->classData['academic_period']) && !$this->classData['academic_period']['is_active'];
    }

    public function mount(string $masterSoalId)
    {
        $this->masterSoalId = $masterSoalId;

        // Validasi akses: Pastikan tugas milik kelas mahasiswa
        $mahasiswa = Auth::user()->mahasiswa;
        $master = MasterSoal::with('tahapanSintaks.sintaksBelajar.pertemuan')->find($masterSoalId);
        
        $kelasId = $master?->tahapanSintaks?->sintaksBelajar?->pertemuan?->kelas_id;

        if (!$master || !$mahasiswa || !$mahasiswa->isEnrolledIn($kelasId)) {
             session()->flash('error', 'Anda tidak memiliki akses ke tugas ini atau tugas tidak ditemukan.');
             return $this->redirect('/mahasiswa/classes', navigate: true);
        }

        $service = new \App\Services\ClassroomService();
        $this->classData = $service->getClassData($kelasId);

        $this->loadAssignment($master);
        $this->loadExistingAnswers();
    }

    private function loadAssignment(?MasterSoal $master = null): void
    {
        if (!$master) {
            $master = MasterSoal::with(['mainSoal.soal.pilihanGanda'])->find($this->masterSoalId);
        }
        if (!$master) return;

        $this->assignment = [
            'id'             => $master->master_soal_id,
            'title'          => $master->master_soal,
            'tenggat'        => $master->tenggat_waktu?->format('d M Y H:i'),
            'is_diskusi'     => $master->is_diskusi,
        ];

        $this->pages = $master->mainSoal->map(fn($ms) => [
            'id'     => $ms->main_soal_id,
            'narasi' => $ms->main_soal,
            'soals'  => $ms->soal->map(fn($s) => [
                'id'        => $s->soal_id,
                'teks'      => $s->soal,
                'tipe'      => $s->kunciJawaban?->tipe_soal ?? 'esai',
                'has_kunci' => !empty($s->kunciJawaban?->kunci_jawaban),
                'tipe_kunci' => $s->kunciJawaban?->tipe_soal,
                'bobot'      => $s->bobot,
                'options'   => $s->pilihanGanda->map(fn($pg) => [
                    'id'   => $pg->pilihan_ganda_id,
                    'teks' => $pg->pilihan_ganda,
                ])->toArray(),
            ])->each(function($soal) {
                \Illuminate\Support\Facades\Log::info("Student Soal Loaded", ['id' => $soal['id'], 'resolved_tipe' => $soal['tipe']]);
            })->toArray(),
        ])->toArray();
    }

    private function loadExistingAnswers(): void
    {
        $userId = Auth::id();
        $jawabans = JawabanMahasiswa::where('master_soal_id', $this->masterSoalId)
            ->where('user_id', $userId)
            ->get();

        if ($jawabans->isNotEmpty()) {
            // ONLY lock out the student if they actually clicked Submit
            if ($jawabans->contains('is_submitted', true)) {
                $this->sudahDikerjakan = true;
            }
            
            foreach ($jawabans as $j) {
                $this->answers[$j->soal_id] = $j->pilihan ?? $j->jawaban ?? '';
                $this->feedback[$j->soal_id] = [
                    'score' => $j->nilai,
                    'note' => $j->catatan
                ];
            }
        }
    }

    public function setAnswer(string $soalId, string $value): void
    {
        if ($this->sudahDikerjakan || $this->isReadonly) return;
        $this->answers[$soalId] = $value;
        $this->saveSingleAnswer($soalId, $value);
    }

    public function updated($propertyName)
    {
        if ($this->sudahDikerjakan || $this->isReadonly) return;
        if (str_starts_with($propertyName, 'answers.')) {
            $soalId = explode('.', $propertyName)[1];
            $value = $this->answers[$soalId] ?? '';
            $this->saveSingleAnswer($soalId, $value);
        }
    }

    private function saveSingleAnswer(string $soalId, string $nilai): void
    {
        $userId = Auth::id();
        $soal = Soal::with('kunciJawaban')->find($soalId);
        if (!$soal) return;

        $tipe = $soal->kunciJawaban?->tipe_soal ?? 'esai';

        JawabanMahasiswa::updateOrCreate(
            ['soal_id' => $soalId, 'user_id' => $userId],
            [
                'master_soal_id' => $this->masterSoalId,
                'jawaban'        => $tipe === 'esai' ? $nilai : null,
                'pilihan'        => $tipe === 'pilihan_ganda' ? $nilai : null,
            ]
        );

        $this->dispatch('saved');
    }

    public function nextPage(): void
    {
        if ($this->currentPage < count($this->pages) - 1) {
            $this->currentPage++;
        }
    }

    public function prevPage(): void
    {
        if ($this->currentPage > 0) {
            $this->currentPage--;
        }
    }

    public function submit(): void
    {
        if ($this->sudahDikerjakan || $this->isReadonly) return;
        $userId = Auth::id();

        foreach ($this->answers as $soalId => $nilai) {
            $soal = Soal::with('kunciJawaban')->find($soalId);
            if (!$soal) continue;

            $tipe = $soal->kunciJawaban?->tipe_soal ?? 'esai';

            JawabanMahasiswa::updateOrCreate(
                ['soal_id' => $soalId, 'user_id' => $userId],
                [
                    'master_soal_id' => $this->masterSoalId,
                    'jawaban'        => $tipe === 'esai' ? $nilai : null,
                    'pilihan'        => $tipe === 'pilihan_ganda' ? $nilai : null,
                    'is_submitted'   => true,
                ]
            );
        }

        $this->submitted = true;
        $this->sudahDikerjakan = true;

        try {
            $master = MasterSoal::with('tahapanSintaks.sintaksBelajar.pertemuan.kelas.mataKuliah.dosen')->find($this->masterSoalId);
            $dosenUserId = $master?->tahapanSintaks?->sintaksBelajar?->pertemuan?->kelas?->mataKuliah?->dosen?->user_id;

            if ($dosenUserId) {
                app(\App\Services\FirebaseNotificationService::class)->sendToUser(
                    $dosenUserId,
                    'Tugas Baru Dikumpulkan',
                    'Mahasiswa ' . Auth::user()->name . ' telah mengumpulkan tugas.',
                    [
                        'type' => 'pengumpulan_tugas',
                        'url'  => $master->tahapanSintaks->sintaksBelajar->pertemuan->kelas->kelas_id ? 
                                  '/lecturer/class/' . $master->tahapanSintaks->sintaksBelajar->pertemuan->kelas->kelas_id . '?tab=grades' : '/'
                    ]
                );
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengirim notifikasi submit tugas: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.student-do-assignment')
            ->layout('components.layout');
    }
}
