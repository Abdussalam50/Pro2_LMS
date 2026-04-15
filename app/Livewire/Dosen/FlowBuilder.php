<?php

namespace App\Livewire\Dosen;

use Livewire\Component;
use App\Livewire\Traits\ManagesLearningSyntax;
use App\Models\Kelas;
use App\Models\Pertemuan;
use App\Models\SintaksBelajar;
use App\Models\TahapanSintaks;
use App\Models\Materi;
use App\Models\MasterSoal;
use App\Models\MainSoal;
use App\Models\Soal;
use App\Models\KunciJawaban;
use App\Models\PilihanGanda;
use App\Models\Kegiatan;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FlowBuilder extends Component
{
    use ManagesLearningSyntax;
    public $kelasId;
    public $pertemuanId = null;
    public $classData = [];
    public $gradingComponents = [];

    public function getIsReadonlyProperty()
    {
        return isset($this->classData['academic_period']) && !$this->classData['academic_period']['is_active'];
    }

    public $pertemuanKe = '';
    public $modelPembelajaran = 'CUSTOM';
    
    // Array to hold custom stages
    public $tahapan = [];

    // Custom Model Naming
    public $customModelName = '';

    // Modal state
    public $isMateriModalOpen = false;
    public $isTugasModalOpen = false;
    public $activeStageIndex = null;

    public function mount($kelasId, $pertemuanId = null)
    {
        $this->kelasId = $kelasId;
        $this->pertemuanId = $pertemuanId;
        
        
        $service = new \App\Services\ClassroomService();
        $this->classData = $service->getClassData($this->kelasId);
        
        $gradingSvc = app(\App\Services\GradingService::class);
        $gradingSvc->getActiveComponents($this->kelasId); // ensures defaults exist
        $this->gradingComponents = \App\Models\GradingComponent::where('kelas_id', $this->kelasId)
            ->whereIn('mapping_type', ['assignment', 'exam'])
            ->get()->toArray();

        // Initialize with default template if creating new
        if (!$pertemuanId) {
            $this->updatedModelPembelajaran();
        } else {
            $pertemuan = Pertemuan::find($pertemuanId);
            if ($pertemuan) {
                $this->pertemuanKe = $pertemuan->pertemuan ?? '';
                
                $sintaks = SintaksBelajar::where('pertemuan_id', $this->pertemuanId)->first();
                if ($sintaks) {
                    $loadedModel = $sintaks->model_pembelajaran;
                    if (in_array($loadedModel, ['PBL', 'PjBL', 'DISCOVERY'])) {
                        $this->modelPembelajaran = $loadedModel;
                        $this->customModelName = '';
                    } else {
                        $this->modelPembelajaran = 'CUSTOM';
                        $this->customModelName = $loadedModel;
                    }
                    $stages = TahapanSintaks::where('sintaks_belajar_id', $sintaks->sintaks_belajar_id)
                                ->orderBy('urutan')->get();
                    
                    if ($stages->count() > 0) {
                         $this->tahapan = $stages->map(function($stage) {
                             $materiData = \App\Models\Materi::where('tahapan_sintaks_id', $stage->tahapan_sintaks_id)->first();
                             $tugasData  = \App\Models\MasterSoal::with(['mainSoal.soal.kunciJawaban','mainSoal.soal.pilihanGanda'])->where('tahapan_sintaks_id', $stage->tahapan_sintaks_id)->first();
                             $kegiatanData = \App\Models\Kegiatan::where('tahapan_sintaks_id', $stage->tahapan_sintaks_id)->orderBy('created_at')->pluck('kegiatan')->toArray();

                             if (empty($kegiatanData)) {
                                 $kegiatanData = [''];
                             }

                             // Build full soal_data payload from DB
                             $soalPayload = [
                                 'id'                    => $tugasData?->master_soal_id ?? Str::random(8),
                                 'title'                 => $tugasData?->master_soal ?? '',
                                 'bobot'                 => $tugasData?->bobot ?? 10,
                                 'tenggat_waktu'         => $tugasData?->tenggat_waktu ? Carbon::parse($tugasData->tenggat_waktu)->format('Y-m-d\TH:i') : '',
                                 'is_diskusi'            => (bool)($tugasData?->is_diskusi ?? false),
                                 'is_show_jawaban'       => (bool)($tugasData?->is_show_jawaban ?? true),
                                 'is_show_kunci_jawaban' => (bool)($tugasData?->is_show_kunci_jawaban ?? false),
                                 'is_show_master_soal'   => (bool)($tugasData?->is_show_master_soal ?? true),
                                 'is_shared'             => (bool)($tugasData?->is_shared ?? false),
                                 'grading_component_id'  => $tugasData?->grading_component_id ?? '',
                                 'bank_soal_id'          => $tugasData?->bank_soal_id ?? '',
                                 'share_kelas_id'        => '',
                                 'share_pertemuan_id'    => '',
                                 'main_soals'            => [],
                             ];

                             if ($tugasData) {
                                 foreach ($tugasData->mainSoal as $ms) {
                                     $soals = [];
                                     foreach ($ms->soal as $s) {
                                         $kj = $s->kunciJawaban;
                                         $opts = $s->pilihanGanda->map(fn($pg) => ['id' => $pg->id ?? uniqid(), 'text' => $pg->pilihan_ganda, 'is_correct' => (bool)$pg->status])->toArray();
                                         $soals[] = [
                                            'db_id' => $s->soal_id, // Store real DB ID
                                            'id' => uniqid(), 
                                            'pertanyaan' => $s->soal, 
                                            'tipe_soal' => $kj?->tipe_soal ?? 'esai', 
                                            'bobot' => $s->bobot ?? 10, 
                                            'kunci_jawaban' => $kj?->kunci_jawaban ?? '', 
                                            'pilihan_ganda_options' => $opts
                                        ];
                                     }
                                     $soalPayload['main_soals'][] = [
                                        'db_id' => $ms->main_soal_id, // Store real DB ID
                                        'id' => uniqid(), 
                                        'narasi' => $ms->main_soal, 
                                        'soals' => $soals
                                    ];
                                 }
                             }
                             if (empty($soalPayload['main_soals'])) {
                                 $soalPayload['main_soals'] = [['id' => Str::random(8), 'narasi' => '', 'soals' => [['id' => Str::random(8), 'pertanyaan' => '', 'tipe_soal' => 'esai', 'bobot' => 10, 'kunci_jawaban' => '', 'pilihan_ganda_options' => []]]]];
                             }

                             return [
                                 'id'          => $stage->tahapan_sintaks_id,
                                 'nama'        => $stage->nama_tahapan,
                                 'has_tugas'   => $tugasData ? true : false,
                                 'has_diskusi'  => true,
                                 'has_materi'  => $materiData ? true : false,
                                 'soal_data'   => $soalPayload,
                                 'materi'      => ['id' => $materiData?->id, 'judul' => $materiData?->judul ?? '', 'isi' => $materiData?->isi_materi ?? ''],
                                 'kegiatan'    => $kegiatanData
                             ];
                         })->toArray();
                    }
                }
            }
        }
    }

    public function addTahapan()
    {
        $blank_soal = [
            'id'                    => Str::random(8),
            'title'                 => '',
            'bobot'                 => 10,
            'tenggat_waktu'         => '',
            'is_diskusi'            => false,
            'is_show_jawaban'       => true,
            'is_show_kunci_jawaban' => false,
            'is_show_master_soal'   => true,
            'is_shared'             => false,
            'grading_component_id'  => '',
            'bank_soal_id'          => '',
            'share_kelas_id'        => '',
            'share_pertemuan_id'    => '',
            'main_soals'            => [
                ['id' => Str::random(8), 'narasi' => '', 'soals' => [
                    ['id' => Str::random(8), 'pertanyaan' => '', 'tipe_soal' => 'esai', 'bobot' => 10, 'kunci_jawaban' => '', 'pilihan_ganda_options' => []]
                ]]
            ]
        ];
        $this->tahapan[] = [
            'id'          => Str::random(8),
            'nama'        => '',
            'has_tugas'   => false,
            'has_diskusi' => false,
            'has_materi'  => false,
            'soal_data'   => $blank_soal,
            'materi'      => ['judul' => '', 'isi' => ''],
            'kegiatan'    => ['']
        ];
    }

    public function addKegiatan($index)
    {
        $this->tahapan[$index]['kegiatan'][] = '';
    }

    public function removeKegiatan($stageIndex, $kegIndex)
    {
        unset($this->tahapan[$stageIndex]['kegiatan'][$kegIndex]);
        $this->tahapan[$stageIndex]['kegiatan'] = array_values($this->tahapan[$stageIndex]['kegiatan']);
        if (empty($this->tahapan[$stageIndex]['kegiatan'])) {
             $this->tahapan[$stageIndex]['kegiatan'] = [''];
        }
    }

    public function updatedModelPembelajaran()
    {
        if ($this->modelPembelajaran !== 'CUSTOM') {
            $this->customModelName = '';
        }

        $template = $this->getSyntaxTemplate($this->modelPembelajaran);
        $this->tahapan = array_map(function($step) {
            return [
                'id'          => $step['id'],
                'nama'        => $step['title'],
                'has_tugas'   => in_array('assignment', $step['tools']),
                'has_diskusi' => in_array('class_chat', $step['tools']) || in_array('group_chat', $step['tools']),
                'has_materi'  => in_array('material', $step['tools']),
                'soal_data'   => [
                    'id'                    => Str::random(8),
                    'title'                 => '',
                    'bobot'                 => 10,
                    'tenggat_waktu'         => '',
                    'is_diskusi'            => false,
                    'is_show_jawaban'       => true,
                    'is_show_kunci_jawaban' => false,
                    'is_show_master_soal'   => true,
                    'is_shared'             => false,
                    'grading_component_id'  => '',
                    'bank_soal_id'          => '',
                    'share_kelas_id'        => '',
                    'share_pertemuan_id'    => '',
                    'main_soals'            => [
                        ['id' => Str::random(8), 'narasi' => '', 'soals' => [
                            ['id' => Str::random(8), 'pertanyaan' => '', 'tipe_soal' => 'esai', 'bobot' => 10, 'kunci_jawaban' => '', 'pilihan_ganda_options' => []]
                        ]]
                    ]
                ],
                'materi'      => ['judul' => '', 'isi' => ''],
                'kegiatan'    => $step['sub_steps'] ?? ['']
            ];
        }, $template);
    }

    public function openMateriModal($index)
    {
        $this->activeStageIndex = $index;
        $this->tahapan[$index]['has_materi'] = true; // Auto enable when opened
        $this->isMateriModalOpen = true;
    }

    public function closeMateriModal()
    {
        // If they closed it but left it empty, maybe we'd turn it off? 
        // For now, let's keep it simple. If they want to remove it, they can via a button.
        $this->isMateriModalOpen = false;
        $this->activeStageIndex = null;
    }

    public function removeMateri()
    {
        if ($this->activeStageIndex !== null) {
            $this->tahapan[$this->activeStageIndex]['has_materi'] = false;
            $this->tahapan[$this->activeStageIndex]['materi'] = ['judul' => '', 'isi' => ''];
            $this->closeMateriModal();
        }
    }

    public function openTugasModal($index)
    {
        $this->activeStageIndex = $index;
        $this->tahapan[$index]['has_tugas'] = true;
        // Load existing soal_data into currentSoalData so the trait modal can edit it
        $this->currentSoalData = $this->tahapan[$index]['soal_data'];
        $this->activeStepIndex = $index;  // required by trait methods
        $this->activeSoalIndex = 0;       // editing the single master soal per stage
        $this->showSoalModal = true;
    }

    /**
     * Override the trait's saveSoalModal so the result is stored back
     * into the correct tahapan slot instead of meetingForm['syntaxConfig'].
     */
    public function saveSoalModal()
    {
        // 1. Format correct answers for all pilihan_ganda questions
        foreach ($this->currentSoalData['main_soals'] ?? [] as $mIdx => $mainSoal) {
            foreach ($mainSoal['soals'] ?? [] as $sIdx => $soal) {
                if (($soal['tipe_soal'] ?? 'pilihan_ganda') === 'pilihan_ganda' && isset($soal['pilihan_ganda_options'])) {
                    $correctText = '';
                    foreach ($soal['pilihan_ganda_options'] as $opt) {
                        if ($opt['is_correct']) { $correctText = $opt['text']; break; }
                    }
                    $this->currentSoalData['main_soals'][$mIdx]['soals'][$sIdx]['kunci_jawaban'] = $correctText;
                }
            }
        }

        // 2. Sync with Bank Soal (Feature parity with Trait)
        $dosenId = \Illuminate\Support\Facades\Auth::user()->dosen->dosen_id;
        $totalBobot = 0;
        foreach ($this->currentSoalData['main_soals'] ?? [] as $ms) {
            foreach ($ms['soals'] ?? [] as $s) {
                $totalBobot += (int)($s['bobot'] ?? 0);
            }
        }

        $bankData = [
            'dosen_id' => $dosenId,
            'jenis' => 'tugas',
            'tipe_soal' => 'kompleks',
            'judul_soal' => $this->currentSoalData['title'] ?: 'Tugas Baru',
            'konten_soal' => 'Tugas/Penugasan Sintaks',
            'opsi_jawaban' => $this->currentSoalData,
            'bobot_referensi' => $totalBobot ?: 10,
        ];

        if (!empty($this->currentSoalData['bank_soal_id'])) {
            $bank = \App\Models\BankSoal::find($this->currentSoalData['bank_soal_id']);
            if ($bank) { $bank->update($bankData); } 
            else {
                $newBank = \App\Models\BankSoal::create($bankData);
                $this->currentSoalData['bank_soal_id'] = $newBank->bank_soal_id;
            }
        } else {
            $newBank = \App\Models\BankSoal::create($bankData);
            $this->currentSoalData['bank_soal_id'] = $newBank->bank_soal_id;
        }

        // 3. Persist to Stage (Tahapan)
        if ($this->activeStageIndex !== null) {
            $this->tahapan[$this->activeStageIndex]['soal_data'] = $this->currentSoalData;
            $this->tahapan[$this->activeStageIndex]['has_tugas'] = !empty($this->currentSoalData['title']);
        }

        $this->showSoalModal = false;
        $this->activeStageIndex = null;
        $this->activeStepIndex  = null;
        $this->activeSoalIndex  = null;
        $this->currentSoalData  = [];
    }

    public function cancelSoalModal()
    {
        $this->showSoalModal    = false;
        $this->activeStageIndex = null;
        $this->activeStepIndex  = null;
        $this->activeSoalIndex  = null;
        $this->currentSoalData  = [];
    }

    public function removeTugas()
    {
        if ($this->activeStageIndex !== null) {
            $this->tahapan[$this->activeStageIndex]['has_tugas'] = false;
            $this->showSoalModal    = false;
            $this->tahapan[$this->activeStageIndex]['soal_data'] = [
                'id'                    => Str::random(8),
                'title'                 => '',
                'tenggat_waktu'         => '',
                'is_diskusi'            => false,
                'is_show_jawaban'       => true,
                'is_show_kunci_jawaban' => false,
                'is_show_master_soal'   => true,
                'is_shared'             => false,
                'grading_component_id'  => '',
                'share_kelas_id'        => '',
                'share_pertemuan_id'    => '',
                'main_soals'            => [
                    ['id' => Str::random(8), 'narasi' => '', 'soals' => [
                        ['id' => Str::random(8), 'pertanyaan' => '', 'tipe_soal' => 'esai', 'bobot' => 10, 'kunci_jawaban' => '', 'pilihan_ganda_options' => []]
                    ]]
                ]
            ];
            $this->closeMateriModal();
            $this->activeStageIndex = null;
            $this->activeStepIndex  = null;
            $this->activeSoalIndex  = null;
            $this->currentSoalData  = [];
        }
    }

    public function removeTahapan($index)
    {
        if (isset($this->tahapan[$index])) {
            unset($this->tahapan[$index]);
            $this->tahapan = array_values($this->tahapan); // re-index
        }
    }

    public function toggleDiskusi($index)
    {
        if (isset($this->tahapan[$index])) {
            $this->tahapan[$index]['has_diskusi'] = !($this->tahapan[$index]['has_diskusi'] ?? false);
        }
    }

    public function saveFlow()
    {
        if ($this->isReadonly) return;
        
        $this->validate([
            'pertemuanKe' => 'required',
        ]);

        // 1. Save or Update Pertemuan
        if (!$this->pertemuanId) {
            $pertemuan = Pertemuan::create([
                'kelas_id' => $this->kelasId,
                'pertemuan' => $this->pertemuanKe,
            ]);
            $this->pertemuanId = $pertemuan->pertemuan_id;
        } else {
            Pertemuan::where('pertemuan_id', $this->pertemuanId)->update([
                'pertemuan' => $this->pertemuanKe,
            ]);
        }

        // 2. Save or Update SintaksBelajar
        $saveModelName = ($this->modelPembelajaran === 'CUSTOM') 
            ? ($this->customModelName ?: 'CUSTOM') 
            : $this->modelPembelajaran;

        $sintaks = SintaksBelajar::updateOrCreate(
            ['pertemuan_id' => $this->pertemuanId],
            [
                'model_pembelajaran' => $saveModelName,
                'sintaks_belajar' => 'Custom Created Flow'
            ]
        );

        // 3. Update Existing or Create New TahapanSintaks
        $processedTahapanIds = [];
        $processedMateriIds = [];
        $processedMasterSoalIds = [];
        $processedMainSoalIds = [];
        $processedSoalIds = [];
        
        foreach ($this->tahapan as $index => &$tahap) {
            $isNewTahapan = !TahapanSintaks::where('tahapan_sintaks_id', $tahap['id'])->exists();
            
            \Illuminate\Support\Facades\Log::info("Processing Stage", ['index' => $index, 'id' => $tahap['id'], 'isNew' => $isNewTahapan]);

            $tahapanModel = TahapanSintaks::updateOrCreate(
                ['tahapan_sintaks_id' => $isNewTahapan ? Str::uuid() : $tahap['id']],
                [
                    'sintaks_belajar_id' => $sintaks->sintaks_belajar_id,
                    'nama_tahapan' => $tahap['nama'],
                    'urutan' => $index + 1,
                ]
            );
            $processedTahapanIds[] = $tahapanModel->tahapan_sintaks_id;
            $tahap['id'] = $tahapanModel->tahapan_sintaks_id;

            // Save Materi
            if ($tahap['has_materi'] && !empty($tahap['materi']['judul'])) {
                $materi = Materi::updateOrCreate(
                    ['tahapan_sintaks_id' => $tahapanModel->tahapan_sintaks_id],
                    [
                        'judul' => $tahap['materi']['judul'],
                        'isi_materi' => $tahap['materi']['isi'],
                    ]
                );
                $processedMateriIds[] = $materi->id;
                $tahap['materi']['id'] = $materi->id;
            }

            // Save Tugas (full MasterSoal > MainSoal > Soal structure)
            if ($tahap['has_tugas'] && !empty($tahap['soal_data']['title'])) {
                $sd = &$tahap['soal_data'];
                \Illuminate\Support\Facades\Log::info("Processing MasterSoal", ['stage_id' => $tahapanModel->tahapan_sintaks_id, 'sd_id' => $sd['id']]);

                $isNewMaster = !MasterSoal::where('master_soal_id', $sd['id'])->exists();

                $masterSoal = MasterSoal::updateOrCreate(
                    ['master_soal_id' => $isNewMaster ? Str::uuid() : $sd['id']],
                    [
                        'master_soal'            => $sd['title'],
                        'bobot'                  => $sd['bobot'] ?? 10,
                        'tahapan_sintaks_id'      => $tahapanModel->tahapan_sintaks_id,
                        'tenggat_waktu'           => !empty($sd['tenggat_waktu']) ? Carbon::parse($sd['tenggat_waktu']) : null,
                        'is_diskusi'             => (bool)($sd['is_diskusi'] ?? false),
                        'is_show_jawaban'        => (bool)($sd['is_show_jawaban'] ?? true),
                        'is_show_kunci_jawaban'  => (bool)($sd['is_show_kunci_jawaban'] ?? false),
                        'is_show_master_soal'    => (bool)($sd['is_show_master_soal'] ?? true),
                        'is_shared'              => (bool)($sd['is_shared'] ?? false),
                        'grading_component_id'   => !empty($sd['grading_component_id']) ? $sd['grading_component_id'] : null,
                        'bank_soal_id'           => !empty($sd['bank_soal_id']) ? $sd['bank_soal_id'] : null,
                    ]
                );
                $processedMasterSoalIds[] = $masterSoal->master_soal_id;
                $sd['id'] = $masterSoal->master_soal_id;

                foreach ($sd['main_soals'] ?? [] as $msIdx => &$msData) {
                    \Illuminate\Support\Facades\Log::info("Processing MainSoal", ['master_id' => $masterSoal->master_soal_id, 'db_id' => $msData['db_id'] ?? 'NULL']);
                    $isNewMain = empty($msData['db_id']) || !MainSoal::where('main_soal_id', $msData['db_id'])->exists();
                    
                    $mainSoal = MainSoal::updateOrCreate(
                        ['main_soal_id' => $isNewMain ? Str::uuid() : $msData['db_id']],
                        [
                            'master_soal_id' => $masterSoal->master_soal_id,
                            'main_soal'      => $msData['narasi'] ?? '',
                        ]
                    );
                    $processedMainSoalIds[] = $mainSoal->main_soal_id;
                    $msData['db_id'] = $mainSoal->main_soal_id;

                    foreach ($msData['soals'] ?? [] as $sIdx => &$soalItem) {
                        if (empty(trim($soalItem['pertanyaan'] ?? ''))) continue;

                        \Illuminate\Support\Facades\Log::info("Processing Soal", ['main_id' => $mainSoal->main_soal_id, 'db_id' => $soalItem['db_id'] ?? 'NULL']);
                        $isNewSoal = empty($soalItem['db_id']) || !Soal::where('soal_id', $soalItem['db_id'])->exists();
                        
                        $createdSoal = Soal::updateOrCreate(
                            ['soal_id' => $isNewSoal ? Str::uuid() : $soalItem['db_id']],
                            [
                                'main_soal_id' => $mainSoal->main_soal_id,
                                'soal'         => trim($soalItem['pertanyaan']),
                                'bobot'        => $soalItem['bobot'] ?? 10,
                            ]
                        );
                        $processedSoalIds[] = $createdSoal->soal_id;
                        $soalItem['db_id'] = $createdSoal->soal_id;

                        KunciJawaban::updateOrCreate(
                            ['soal_id' => $createdSoal->soal_id],
                            [
                                'kunci_jawaban'=> $soalItem['kunci_jawaban'] ?? null,
                                'tipe_soal'    => $soalItem['tipe_soal'] ?? 'esai',
                                'share_kelas_id'      => !empty($sd['share_kelas_id']) ? $sd['share_kelas_id'] : null,
                                'share_pertemuan_id'  => !empty($sd['share_pertemuan_id']) ? $sd['share_pertemuan_id'] : null,
                            ]
                        );

                        if (($soalItem['tipe_soal'] ?? '') === 'pilihan_ganda') {
                            PilihanGanda::where('soal_id', $createdSoal->soal_id)->delete();
                            foreach ($soalItem['pilihan_ganda_options'] ?? [] as $opt) {
                                if (!empty(trim($opt['text'] ?? ''))) {
                                    PilihanGanda::create([
                                        'soal_id'       => $createdSoal->soal_id,
                                        'pilihan_ganda' => trim($opt['text']),
                                        'status'        => (bool)($opt['is_correct'] ?? false),
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            // Save Kegiatan (Aktivitas) - Recreate for simplicity as it's just strings
            Kegiatan::where('tahapan_sintaks_id', $tahapanModel->tahapan_sintaks_id)->delete();
            foreach ($tahap['kegiatan'] ?? [] as $kegText) {
                if (!empty(trim($kegText))) {
                    Kegiatan::create([
                        'tahapan_sintaks_id' => $tahapanModel->tahapan_sintaks_id,
                        'kegiatan' => trim($kegText),
                    ]);
                }
            }
        }

        // 4. Cleanup items that were removed (Global Cleanup)
        // Order matters to respect constraints, although cascade usually helps
        
        // Clean orphaned Soals belonging to processed MainSoals
        if (!empty($processedMainSoalIds)) {
            Soal::whereIn('main_soal_id', $processedMainSoalIds)
                ->whereNotIn('soal_id', $processedSoalIds)
                ->delete();
        }

        // Clean orphaned MainSoals belonging to processed MasterSoals
        if (!empty($processedMasterSoalIds)) {
            MainSoal::whereIn('master_soal_id', $processedMasterSoalIds)
                ->whereNotIn('main_soal_id', $processedMainSoalIds)
                ->delete();
        }

        // Clean orphaned MasterSoals belonging to processed Tahapan
        if (!empty($processedTahapanIds)) {
            MasterSoal::whereIn('tahapan_sintaks_id', $processedTahapanIds)
                ->whereNotIn('master_soal_id', $processedMasterSoalIds)
                ->delete();
        }

        // Clean orphaned Materis belonging to processed Tahapan
        if (!empty($processedTahapanIds)) {
            Materi::whereIn('tahapan_sintaks_id', $processedTahapanIds)
                ->whereNotIn('id', $processedMateriIds)
                ->delete();
        }

        // Clean orphaned Tahapan belonging to this Sintaks
        TahapanSintaks::where('sintaks_belajar_id', $sintaks->sintaks_belajar_id)
            ->whereNotIn('tahapan_sintaks_id', $processedTahapanIds)
            ->delete();

        session()->flash('success_message', 'Alur Pembelajaran Berhasil Disimpan!');
        $this->dispatch('swal', [
            'title' => 'Tersimpan!',
            'text' => 'Alur Pembelajaran Berhasil Disimpan!',
            'icon' => 'success'
        ]);
    }

    public function render()
    {
        return view('livewire.dosen.flow-builder')->layout('components.layout');
    }
}
