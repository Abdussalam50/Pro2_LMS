<?php

namespace App\Livewire\Traits;

trait ManagesLearningSyntax
{

    // Modal Control for Soal (Master Soal Level)
    public $showSoalModal = false;
    public $activeStepIndex = null;
    public $activeSoalIndex = null; // References the Master Soal index in the step
    public $currentSoalData = []; // Represents one Master Soal payload

    // Sharing State (for soal/penugasan modal)
    public $shareAvailableClasses = [];
    public $shareAvailableMeetings = [];

    // Bank Tugas State
    public $showBankTugasModal = false;
    public $bankTugas = [];
    public $previewBankSoal = null;
    
    public function openBankTugasModal($stepIndex)
    {
        $this->activeStepIndex = $stepIndex;
        $dosenId = \Illuminate\Support\Facades\Auth::user()->dosen->dosen_id;
        $this->bankTugas = \App\Models\BankSoal::where('dosen_id', $dosenId)
            ->where('jenis', 'tugas')
            ->latest()
            ->get();
            
        $this->showBankTugasModal = true;
    }

    public function openPreviewBankTugas($id)
    {
        $this->previewBankSoal = \App\Models\BankSoal::find($id);
    }

    public function closePreviewBankTugas()
    {
        $this->previewBankSoal = null;
    }

    public function importFromBankTugas($bankSoalId)
    {
        $bank = \App\Models\BankSoal::find($bankSoalId);
        if ($bank) {
            // Import JSON structure back into current step
            $soalData = $bank->opsi_jawaban; // Using opsi_jawaban to hold the structured JSON
            // Regenerate IDs to prevent conflicts
            $soalData['id'] = uniqid();
            $soalData['bank_soal_id'] = $bank->bank_soal_id; // Store reference

            // IMPORTANT: Clear DB IDs so they are treated as NEW records in this context
            foreach ($soalData['main_soals'] ?? [] as &$ms) {
                $ms['db_id'] = null;
                foreach ($ms['soals'] ?? [] as &$s) {
                    $s['db_id'] = null;
                }
            }
            
            if (!isset($this->meetingForm['syntaxConfig'][$this->activeStepIndex]['soals'])) {
                $this->meetingForm['syntaxConfig'][$this->activeStepIndex]['soals'] = [];
            }
            $this->meetingForm['syntaxConfig'][$this->activeStepIndex]['soals'][] = $soalData;
            
            $this->showBankTugasModal = false;
            $this->previewBankSoal = null;
            session()->flash('message', 'Tugas berhasil diimpor dari bank.');
        }
    }

    /**
     * Triggered when dosen checks/unchecks "Bagikan ke Kelas Lain"
     */
    public function updatedCurrentSoalDataIsShared($value)
    {
        $this->currentSoalData['share_kelas_id'] = '';
        $this->currentSoalData['share_pertemuan_id'] = '';
        $this->shareAvailableMeetings = [];

        if ($value) {
            $user = \Illuminate\Support\Facades\Auth::user();
            if ($user && $user->dosen) {
                $dosenId = $user->dosen->dosen_id;
                // dosen_id column lives on mata_kuliah, not kelas — filter via whereHas
                $this->shareAvailableClasses = \App\Models\Kelas::whereHas('mataKuliah', function ($q) use ($dosenId) {
                    $q->where('dosen_id', $dosenId);
                })->get()->toArray();
            } else {
                $this->shareAvailableClasses = \App\Models\Kelas::with('mataKuliah')->get()->toArray();
            }
        } else {
            $this->shareAvailableClasses = [];
        }
    }

    /**
     * Triggered when dosen selects a target class for sharing
     */
    public function updatedCurrentSoalDataShareKelasId($classId)
    {
        $this->currentSoalData['share_pertemuan_id'] = '';
        if ($classId) {
            $this->shareAvailableMeetings = \App\Models\Pertemuan::where('kelas_id', $classId)
                ->orderBy('created_at', 'asc')
                ->get()
                ->toArray();
        } else {
            $this->shareAvailableMeetings = [];
        }
    }

    public function openSoalModal($stepIndex, $soalIndex = null)
    {
        $this->activeStepIndex = $stepIndex;
        
        if ($soalIndex !== null) {
            $this->activeSoalIndex = $soalIndex;
            // clone existing Master Soal
            $this->currentSoalData = $this->meetingForm['syntaxConfig'][$stepIndex]['soals'][$soalIndex];
        } else {
            // create new Master Soal structure with 1 default Main Soal and 1 Question
            $this->activeSoalIndex = null;
            $this->currentSoalData = [
                'id' => uniqid(),
                'bank_soal_id' => null, // Added for tracking
                'title' => '',
                'tenggat_waktu' => '',
                'is_diskusi' => false,
                'is_show_jawaban' => false,
                'is_show_kunci_jawaban' => false,
                'is_show_master_soal' => true,
                'is_shared' => false,
                'share_kelas_id' => '',
                'share_pertemuan_id' => '',
                
                // Nested Level 2: Main Soals (Narasi)
                'main_soals' => [
                    [
                        'id' => uniqid(),
                        'db_id' => null,
                        'narasi' => '',
                        
                        // Nested Level 3: Soals (Pertanyaan)
                        'soals' => [
                            [
                                'id' => uniqid(),
                                'db_id' => null,
                                'pertanyaan' => '',
                                'tipe_soal' => 'pilihan_ganda',
                                'bobot' => 10,
                                'kunci_jawaban' => '',
                                'pilihan_ganda_options' => [
                                    ['id' => uniqid(), 'text' => '', 'is_correct' => false],
                                    ['id' => uniqid(), 'text' => '', 'is_correct' => false],
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }

        $this->showSoalModal = true;
    }

    // --- Main Soal (Narasi) Controls ---
    public function addMainSoal()
    {
        $this->currentSoalData['main_soals'][] = [
            'id' => uniqid(),
            'db_id' => null,
            'narasi' => '',
            'soals' => [
                [
                    'id' => uniqid(),
                    'db_id' => null,
                    'pertanyaan' => '',
                    'tipe_soal' => 'pilihan_ganda',
                    'bobot' => 10,
                    'kunci_jawaban' => '',
                    'pilihan_ganda_options' => [
                        ['id' => uniqid(), 'text' => '', 'is_correct' => false],
                        ['id' => uniqid(), 'text' => '', 'is_correct' => false],
                    ]
                ]
            ]
        ];
    }

    public function removeMainSoal($mainIdx)
    {
        unset($this->currentSoalData['main_soals'][$mainIdx]);
        $this->currentSoalData['main_soals'] = array_values($this->currentSoalData['main_soals']);
    }

    // --- Soal (Pertanyaan) Controls ---
    public function addSoalToMain($mainIdx)
    {
        $this->currentSoalData['main_soals'][$mainIdx]['soals'][] = [
            'id' => uniqid(),
            'db_id' => null,
            'pertanyaan' => '',
            'tipe_soal' => 'pilihan_ganda',
            'bobot' => 10,
            'kunci_jawaban' => '',
            'pilihan_ganda_options' => [
                ['id' => uniqid(), 'text' => '', 'is_correct' => false],
                ['id' => uniqid(), 'text' => '', 'is_correct' => false],
            ]
        ];
    }

    public function removeSoalFromMain($mainIdx, $soalIdx)
    {
        unset($this->currentSoalData['main_soals'][$mainIdx]['soals'][$soalIdx]);
        $this->currentSoalData['main_soals'][$mainIdx]['soals'] = array_values($this->currentSoalData['main_soals'][$mainIdx]['soals']);
    }

    // --- Pilihan Ganda (Options) Controls ---
    public function addOptionToSoal($mainIdx, $soalIdx)
    {
        $this->currentSoalData['main_soals'][$mainIdx]['soals'][$soalIdx]['pilihan_ganda_options'][] = [
            'id' => uniqid(), 
            'text' => '', 
            'is_correct' => false
        ];
    }

    public function setCorrectOption($mainIdx, $soalIdx, $optionIndex)
    {
        // Unset all others
        foreach ($this->currentSoalData['main_soals'][$mainIdx]['soals'][$soalIdx]['pilihan_ganda_options'] as $idx => $opt) {
            $this->currentSoalData['main_soals'][$mainIdx]['soals'][$soalIdx]['pilihan_ganda_options'][$idx]['is_correct'] = ($idx === $optionIndex);
        }
    }

    public function removeOptionFromSoal($mainIdx, $soalIdx, $optionIndex)
    {
        unset($this->currentSoalData['main_soals'][$mainIdx]['soals'][$soalIdx]['pilihan_ganda_options'][$optionIndex]);
        $this->currentSoalData['main_soals'][$mainIdx]['soals'][$soalIdx]['pilihan_ganda_options'] = array_values($this->currentSoalData['main_soals'][$mainIdx]['soals'][$soalIdx]['pilihan_ganda_options']);
    }

    // --- Modal Finalization ---
    public function cancelSoalModal()
    {
        $this->showSoalModal = false;
        $this->activeStepIndex = null;
        $this->activeSoalIndex = null;
        $this->currentSoalData = [];
    }

    public function saveSoalModal()
    {
        // Default empty implementation, components should override this
    }

    public function getSyntaxTemplate($model)
    {
        $model = strtolower($model);
        $baseStep = function($title, $tools = [], $has_materi = false, $has_diskusi = false, $has_tugas = false) {
            return [
                'id' => uniqid(), 
                'selected' => true, 
                'title' => $title, 
                'sub_steps' => [''], 
                'tools' => $tools,
                'materials' => $has_materi ? [['id' => uniqid(), 'title' => '', 'content' => '']] : [],
                'soals' => []
            ];
        };

        if ($model === 'pbl') {
            return [
                $baseStep('Orientasi siswa pada masalah', ['class_chat'], false, true),
                $baseStep('Mengorganisasi siswa untuk belajar', ['group_chat'], false, true),
                $baseStep('Membimbing penyelidikan individual/kelompok', ['material', 'group_chat'], true, true),
                $baseStep('Mengembangkan dan menyajikan hasil karya', ['assignment', 'group_chat'], false, true),
                $baseStep('Menganalisis dan mengevaluasi proses pemecahan masalah', ['class_chat'], false, true)
            ];
        } else if ($model === 'pjbl') {
            return [
                $baseStep('Penentuan Pertanyaan Mendasar', ['class_chat'], false, true),
                $baseStep('Mendesain Perencanaan Proyek', ['group_chat'], false, true),
                $baseStep('Menyusun Jadwal', ['group_chat'], false, true),
                $baseStep('Memonitor Siswa dan Kemajuan Proyek', ['material', 'group_chat'], true, true),
                $baseStep('Menguji Hasil', ['assignment', 'group_chat'], false, true),
                $baseStep('Mengevaluasi Pengalaman', ['class_chat'], false, true)
            ];
        } else if ($model === 'discovery') {
            return [
                $baseStep('Pemberian Rangsangan (Stimulation)', ['material', 'class_chat'], true, true),
                $baseStep('Pernyataan/Identifikasi Masalah (Problem Statement)', ['class_chat'], false, true),
                $baseStep('Pengumpulan Data (Data Collection)', ['material', 'group_chat'], true, true),
                $baseStep('Pengolahan Data (Data Processing)', ['group_chat'], false, true),
                $baseStep('Pembuktian (Verification)', ['group_chat'], false, true),
                $baseStep('Menarik Simpulan/Generalisasi (Generalization)', ['assignment', 'class_chat'], false, true)
            ];
        } else {
            return [
                $baseStep('Langkah 1', ['material', 'class_chat', 'group_chat'], true, true)
            ];
        }
    }
}
