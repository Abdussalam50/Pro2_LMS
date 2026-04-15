<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Pertemuan;
use App\Models\SintaksBelajar;
use App\Models\Kegiatan;
use Illuminate\Support\Facades\DB;

class ClassroomService
{
    /**
     * Get class basic data
     */
    public function getClassData($classId)
    {
        $kelas = Kelas::with(['mataKuliah.dosen', 'academicPeriod'])->find($classId);
        
        if (!$kelas) {
            return null;
        }

        return [
            'id' => $kelas->kelas_id,
            'name' => $kelas->kelas,
            'code' => $kelas->kode,
            'course_id' => $kelas->mata_kuliah_id,
            'course_name' => $kelas->mataKuliah->mata_kuliah ?? 'Unknown Course',
            'course_code' => $kelas->mataKuliah->kode ?? '---',
            'lecturer_name' => $kelas->mataKuliah->dosen->nama ?? 'Unknown Lecturer',
            'academic_period' => $kelas->academicPeriod ? $kelas->academicPeriod->toArray() : null,
            'students_count' => \App\Models\MahasiswaData::whereHas('kelass', fn($q) => $q->where('kelas_mahasiswa.kelas_id', $kelas->kelas_id))->count()
        ];
    }

    /**
     * Get all students for a class, optionally with performance stats.
     */
    public function getStudents($classId, $includeStats = false)
    {
        $students = \App\Models\MahasiswaData::whereHas('kelass', fn($q) => $q->where('kelas_mahasiswa.kelas_id', $classId))
            ->with('user')
            ->orderBy('nama', 'asc')
            ->get()
            ->map(function($m) use ($classId, $includeStats) {
                $base = [
                    'id'           => $m->mahasiswa_id,
                    'name'         => $m->nama,
                    'nim'          => $m->nim,
                    'angkatan'     => $m->angkatan,
                    'program_studi'=> $m->program_studi,
                    'foto'         => $m->foto,
                    'user_id'      => $m->user_id,
                    'email'        => $m->user->email ?? '-',
                    'is_active'    => $m->user->is_active ?? false,
                ];

                if ($includeStats) {
                    $base['stats'] = $this->getStudentStats($classId, $m->mahasiswa_id, $m->user_id);
                }

                return $base;
            })->toArray();

        return $students;
    }

    /**
     * Compute performance statistics for a single student in a class.
     */
    private function getStudentStats($classId, $mahasiswaId, $userId)
    {
        // 1. Total pertemuan on that class
        $totalMeetings = DB::table('pertemuans')->where('kelas_id', $classId)->count();

        // 2. Attended pertemuan count
        $attended = DB::table('presensi_mahasiswa')
            ->where('mahasiswa_id', $mahasiswaId)
            ->whereIn('pertemuan_id', function($q) use ($classId) {
                $q->select('pertemuan_id')->from('pertemuans')->where('kelas_id', $classId);
            })
            ->where('status', 'hadir')
            ->count();

        $attendancePct = $totalMeetings > 0 ? round(($attended / $totalMeetings) * 100, 0) : 100;

        // 3. Task completion (master_soal submitted vs available)
        $totalTasks = DB::table('pertemuans')
            ->join('sintaks_belajar', 'pertemuans.pertemuan_id', '=', 'sintaks_belajar.pertemuan_id')
            ->join('tahapan_sintaks', 'sintaks_belajar.sintaks_belajar_id', '=', 'tahapan_sintaks.sintaks_belajar_id')
            ->join('master_soal', 'tahapan_sintaks.tahapan_sintaks_id', '=', 'master_soal.tahapan_sintaks_id')
            ->where('pertemuans.kelas_id', $classId)
            ->count('master_soal.master_soal_id');

        $submittedTasks = DB::table('pertemuans')
            ->join('sintaks_belajar', 'pertemuans.pertemuan_id', '=', 'sintaks_belajar.pertemuan_id')
            ->join('tahapan_sintaks', 'sintaks_belajar.sintaks_belajar_id', '=', 'tahapan_sintaks.sintaks_belajar_id')
            ->join('master_soal', 'tahapan_sintaks.tahapan_sintaks_id', '=', 'master_soal.tahapan_sintaks_id')
            ->join('jawaban_mahasiswa', 'master_soal.master_soal_id', '=', 'jawaban_mahasiswa.master_soal_id')
            ->where('pertemuans.kelas_id', $classId)
            ->where('jawaban_mahasiswa.user_id', $userId)
            ->distinct('master_soal.master_soal_id')
            ->count('master_soal.master_soal_id');

        // 4. Exam scores - average of all exams in this class
        $ujianScores = DB::table('ujians')
            ->join('nilai_ujians_mahasiswa', 'ujians.ujian_id', '=', 'nilai_ujians_mahasiswa.ujian_id')
            ->join('mahasiswas', 'nilai_ujians_mahasiswa.mahasiswa_id', '=', 'mahasiswas.mahasiswa_id')
            ->where('ujians.kelas_id', $classId)
            ->where('mahasiswas.mahasiswa_id', $mahasiswaId)
            ->avg('nilai_ujians_mahasiswa.nilai');

        $avgUjian = $ujianScores ? round($ujianScores, 1) : null;

        // 5. Last activity = most recent of (last presensi, last jawaban submission)
        $lastPresensi = DB::table('presensi_mahasiswa')
            ->where('mahasiswa_id', $mahasiswaId)
            ->whereIn('pertemuan_id', fn($q) => $q->select('pertemuan_id')->from('pertemuans')->where('kelas_id', $classId))
            ->max('created_at');

        $lastJawaban = DB::table('jawaban_mahasiswa')
            ->where('user_id', $userId)
            ->whereIn('master_soal_id', function($q) use ($classId) {
                $q->select('master_soal.master_soal_id')
                  ->from('master_soal')
                  ->join('tahapan_sintaks', 'master_soal.tahapan_sintaks_id', '=', 'tahapan_sintaks.tahapan_sintaks_id')
                  ->join('sintaks_belajar', 'tahapan_sintaks.sintaks_belajar_id', '=', 'sintaks_belajar.sintaks_belajar_id')
                  ->join('pertemuans', 'sintaks_belajar.pertemuan_id', '=', 'pertemuans.pertemuan_id')
                  ->where('pertemuans.kelas_id', $classId);
            })
            ->max('created_at');

        $lastActivity = max($lastPresensi, $lastJawaban);

        return [
            'attendance_pct'   => $attendancePct,
            'attended'         => $attended,
            'total_meetings'   => $totalMeetings,
            'tasks_submitted'  => $submittedTasks,
            'tasks_total'      => $totalTasks,
            'avg_ujian'        => $avgUjian,
            'last_activity'    => $lastActivity,
        ];
    }


    /**
     * Get all meetings for a class mapped for UI
     */
    public function getMeetings($classId)
    {
        $pertemuans = Pertemuan::with([
            'sintaksBelajar.tahapanSintaks.kegiatan', 
            'sintaksBelajar.tahapanSintaks.materis', 
            'sintaksBelajar.tahapanSintaks.masterSoal.mainSoal.soal.kunciJawaban',
            'sintaksBelajar.tahapanSintaks.masterSoal.mainSoal.soal.pilihanGanda'
        ])
            ->where('kelas_id', $classId)
            ->orderBy('created_at', 'asc')
            ->get();
            
        return $pertemuans->map(function($p) {
            $learningModel = $p->sintaksBelajar ? $p->sintaksBelajar->model_pembelajaran : 'none';
            
            $steps = [];
            $allMaterials = [];
            $allAssignments = [];

            if ($p->sintaksBelajar && $p->sintaksBelajar->tahapanSintaks) {
                foreach($p->sintaksBelajar->tahapanSintaks as $tahapan) {
                    $activities = [];
                    foreach($tahapan->kegiatan as $keg) {
                        $activities[] = $keg->kegiatan;
                    }
                    
                    // Fetch materials for this step
                    $stepMaterials = $tahapan->materis->map(function($m) {
                        return ['id' => $m->id, 'title' => $m->judul, 'content' => $m->isi_materi];
                    })->toArray();
                    
                    // Fetch assignments for this step
                    $stepSoals = $tahapan->masterSoal->map(function($mst) {
                        
                        $mainSoalsPayload = [];
                        foreach($mst->mainSoal as $mainS) {
                            $soalsPayload = [];
                            
                            foreach($mainS->soal as $soal) {
                                $kj = $soal->kunciJawaban;
                                
                                $pgOptions = [];
                                if ($soal->pilihanGanda) {
                                    foreach($soal->pilihanGanda as $pg) {
                                        $pgOptions[] = [
                                            'id' => uniqid(),
                                            'text' => $pg->pilihan_ganda,
                                            'is_correct' => filter_var($pg->status, FILTER_VALIDATE_BOOLEAN)
                                        ];
                                    }
                                }

                                // ensure we always have at least 2 empty options for UI to not break on new edits
                                if (empty($pgOptions)) {
                                    $pgOptions = [
                                        ['id' => uniqid(), 'text' => '', 'is_correct' => false],
                                        ['id' => uniqid(), 'text' => '', 'is_correct' => false],
                                    ];
                                }
                                
                                $soalsPayload[] = [
                                    'id' => uniqid(),
                                    'db_id' => $soal->soal_id, // PRESERVE REAL ID
                                    'pertanyaan' => $soal->soal,
                                    'tipe_soal' => $kj?->tipe_soal ?? 'esai',
                                    'bobot' => $soal->bobot,
                                    'kunci_jawaban' => $kj?->kunci_jawaban ?? '',
                                    'pilihan_ganda_options' => $pgOptions,
                                ];
                            }
                            
                            $mainSoalsPayload[] = [
                                'id' => uniqid(),
                                'db_id' => $mainS->main_soal_id, // PRESERVE REAL ID
                                'narasi' => $mainS->main_soal,
                                'soals' => $soalsPayload
                            ];
                        }
                        
                        // Set fallback if empty
                        if(empty($mainSoalsPayload)) {
                            $mainSoalsPayload[] = [
                                'id' => uniqid(),
                                'narasi' => '',
                                'soals' => [
                                    [
                                        'id' => uniqid(),
                                        'pertanyaan' => '',
                                        'tipe_soal' => 'esai',
                                        'kunci_jawaban' => '',
                                        'pilihan_ganda_options' => [
                                            ['id' => uniqid(), 'text' => '', 'is_correct' => false],
                                            ['id' => uniqid(), 'text' => '', 'is_correct' => false],
                                        ]
                                    ]
                                ]
                            ];
                        }

                        // We rely on the first Soal's KunciJawaban settings for shared configurations
                        $firstKj = $mst->mainSoal->first()?->soal->first()?->kunciJawaban;

                        return [
                            'id' => $mst->master_soal_id,
                            'db_id' => $mst->master_soal_id,
                            'title' => $mst->master_soal, 
                            'tenggat_waktu' => $mst->tenggat_waktu ? $mst->tenggat_waktu->format('Y-m-d H:i') : '-',
                            'is_diskusi' => $mst->is_diskusi,
                            'is_show_jawaban' => $mst->is_show_jawaban,
                            'is_show_kunci_jawaban' => $mst->is_show_kunci_jawaban,
                            'is_show_master_soal' => $mst->is_show_master_soal,
                            'is_shared' => $mst->is_shared,
                            'share_kelas_id' => $firstKj?->share_kelas_id ?? '',
                            'share_pertemuan_id' => $firstKj?->share_pertemuan_id ?? '',
                            'bank_soal_id' => $mst->bank_soal_id,
                            'main_soals' => $mainSoalsPayload
                        ];
                    })->toArray();
                    
                    $tools = [];
                    if (!empty($stepMaterials)) $tools[] = 'material';
                    if (!empty($stepSoals)) $tools[] = 'assignment';

                    // Collect for step
                    $steps[] = [
                        'id' => $tahapan->tahapan_sintaks_id,
                        'title' => $tahapan->nama_tahapan, 
                        'sub_steps' => $activities,
                        'tools' => $tools,
                        'materials' => $stepMaterials,
                        'soals' => $stepSoals
                    ];

                    // Collect for global fallback lists (used by ui blocks)
                    $allMaterials = array_merge($allMaterials, $stepMaterials);
                    $allAssignments = array_merge($allAssignments, $stepSoals);
                }
            }

            // Fallback for default UI display if no steps saved yet
            if (empty($steps) && $learningModel === 'pbl') {
                    $steps = [
                    ['title' => 'Orientasi siswa pada masalah', 'sub_steps' => [], 'tools' => [], 'materials' => [], 'soals' => []],
                    ['title' => 'Mengorganisasi siswa untuk belajar', 'sub_steps' => [], 'tools' => [], 'materials' => [], 'soals' => []],
                ];
            } else if (empty($steps) && $learningModel === 'pjbl') {
                $steps = [
                    ['title' => 'Penentuan Pertanyaan Mendasar', 'sub_steps' => [], 'tools' => [], 'materials' => [], 'soals' => []],
                    ['title' => 'Mendesain Perencanaan Proyek', 'sub_steps' => [], 'tools' => [], 'materials' => [], 'soals' => []],
                ];
            }

            return [
                'id' => $p->pertemuan_id,
                'title' => $p->pertemuan,
                'date' => $p->tanggal ?? $p->created_at->format('Y-m-d'), // Use actual date if available
                'learning_model' => $learningModel,
                'syntax' => [
                    'id' => $p->sintaksBelajar?->sintaks_belajar_id,
                    'steps' => $steps
                ],
                'materials' => $allMaterials,
                'assignments' => $allAssignments
            ];
        })->toArray();
    }

    /**
     * Create or update a meeting
     */
    public function saveMeeting($classId, $data, $meetingId = null)
    {
        if ($meetingId) {
            $meeting = Pertemuan::find($meetingId);
            if ($meeting) {
                $meeting->update([
                    'pertemuan' => $data['title'],
                    'tanggal' => $data['date'] ?? null
                ]);

                // Update model if provided
                if (isset($data['learning_model']) && $data['learning_model'] !== 'none') {
                    $sintaks = SintaksBelajar::firstOrCreate(
                        ['pertemuan_id' => $meeting->pertemuan_id],
                        ['sintaks_belajar' => 'Sintaks: ' . $data['title'], 'model_pembelajaran' => $data['learning_model']]
                    );
                    if ($sintaks->model_pembelajaran !== $data['learning_model']) {
                        $sintaks->update(['model_pembelajaran' => $data['learning_model']]);
                    }
                } else if (isset($data['learning_model']) && $data['learning_model'] === 'none') {
                     SintaksBelajar::where('pertemuan_id', $meeting->pertemuan_id)->delete();
                }
            }
            return $meeting;
        }

        // Create new
        $meeting = Pertemuan::create([
            'pertemuan' => $data['title'],
            'tanggal' => $data['date'] ?? null,
            'kelas_id' => $classId
        ]);

        if (isset($data['learning_model']) && $data['learning_model'] !== 'none') {
            SintaksBelajar::create([
                'pertemuan_id' => $meeting->pertemuan_id,
                'sintaks_belajar' => 'Sintaks Pembelajaran',
                'model_pembelajaran' => $data['learning_model']
            ]);
        }

        return $meeting;
    }

    /**
     * Delete a meeting
     */
    public function deleteMeeting($meetingId)
    {
        $meeting = Pertemuan::find($meetingId);
        if ($meeting) {
            // Relational deletes (SintaksBelajar -> TahapanSintaks -> Kegiatan, Materis, MasterSoal)
            // If cascade is working on DB level this might be redundant but explicit is safer
            $sintaks = SintaksBelajar::where('pertemuan_id', $meetingId)->first();
            if ($sintaks) {
                $tahapans = \App\Models\TahapanSintaks::where('sintaks_belajar_id', $sintaks->sintaks_belajar_id)->get();
                foreach($tahapans as $tahapan) {
                    Kegiatan::where('tahapan_sintaks_id', $tahapan->tahapan_sintaks_id)->delete();
                    \App\Models\Materi::where('tahapan_sintaks_id', $tahapan->tahapan_sintaks_id)->delete();
                    $masterSoals = \App\Models\MasterSoal::where('tahapan_sintaks_id', $tahapan->tahapan_sintaks_id)->get();
                    foreach($masterSoals as $ms) {
                       $mainSoals = \App\Models\MainSoal::where('master_soal_id', $ms->master_soal_id)->get();
                       foreach($mainSoals as $mso) {
                            $soals = \App\Models\Soal::where('main_soal_id', $mso->main_soal_id)->get();
                            foreach($soals as $s) {
                                \App\Models\KunciJawaban::where('soal_id', $s->soal_id)->delete();
                                $s->delete();
                            }
                            $mso->delete();
                       }
                       $ms->delete();
                    }
                    $tahapan->delete();
                }
                $sintaks->delete();
            }
            $meeting->delete();
            return true;
        }
        return false;
    }

    /**
     * Save Learning Syntax Steps
     */
    public function saveSyntax($meetingId, $model, $steps)
    {
        \Illuminate\Support\Facades\Log::info("Saving Syntax Config", ['meetingId' => $meetingId, 'model' => $model, 'steps_count' => count($steps)]);
        DB::beginTransaction();
        try {
            $sintaks = SintaksBelajar::updateOrCreate(
                ['pertemuan_id' => $meetingId],
                [
                    'sintaks_belajar' => 'Sintaks: ' . strtoupper($model),
                    'model_pembelajaran' => $model
                ]
            );

            $processedTahapanIds = [];
            $processedMateriIds = [];
            $processedMasterSoalIds = [];
            $processedMainSoalIds = [];
            $processedSoalIds = [];

            if (isset($steps) && is_array($steps)) {
                foreach ($steps as $index => $step) {
                    if (!isset($step['selected']) || $step['selected']) {
                        
                        // 1. Save Tahapan (Stage)
                        $isNewTahapan = empty($step['id']) || !\App\Models\TahapanSintaks::where('tahapan_sintaks_id', $step['id'])->exists();
                        $tahapan = \App\Models\TahapanSintaks::updateOrCreate(
                            ['tahapan_sintaks_id' => $isNewTahapan ? \Illuminate\Support\Str::uuid() : $step['id']],
                            [
                                'sintaks_belajar_id' => $sintaks->sintaks_belajar_id,
                                'nama_tahapan' => trim($step['title']),
                                'urutan' => $index + 1
                            ]
                        );
                        $processedTahapanIds[] = $tahapan->tahapan_sintaks_id;

                        // 2. Save Sub-Steps (Kegiatan) - Recreate
                        \App\Models\Kegiatan::where('tahapan_sintaks_id', $tahapan->tahapan_sintaks_id)->delete();
                        if (isset($step['sub_steps']) && is_array($step['sub_steps'])) {
                            foreach ($step['sub_steps'] as $subStep) {
                                if (!empty(trim($subStep))) {
                                    \App\Models\Kegiatan::create([
                                        'tahapan_sintaks_id' => $tahapan->tahapan_sintaks_id,
                                        'kegiatan' => trim($subStep)
                                    ]);
                                }
                            }
                        }

                        // 3. Save Materials
                        if (isset($step['materials']) && is_array($step['materials'])) {
                            foreach ($step['materials'] as $mat) {
                                if (!empty(trim($mat['title'] ?? ''))) {
                                    $materi = \App\Models\Materi::updateOrCreate(
                                        ['tahapan_sintaks_id' => $tahapan->tahapan_sintaks_id, 'judul' => trim($mat['title'])],
                                        ['isi_materi' => $mat['content'] ?? null]
                                    );
                                    $processedMateriIds[] = $materi->id;
                                }
                            }
                        }

                        // 4. Save Assignments (MasterSoal -> MainSoal -> Soal)
                        if (isset($step['soals']) && is_array($step['soals'])) {
                            foreach ($step['soals'] as $soalData) {
                                if (empty(trim($soalData['title'] ?? ''))) {
                                    $soalData['title'] = "Tugas " . ($tahapan->nama_tahapan ?? 'Pembelajaran');
                                }
                                    
                                $isNewMaster = empty($soalData['id']) || !\App\Models\MasterSoal::where('master_soal_id', $soalData['id'])->exists();
                                $masterSoal = \App\Models\MasterSoal::updateOrCreate(
                                    ['master_soal_id' => $soalData['db_id'] ?? (string) \Illuminate\Support\Str::uuid()],
                                    [
                                        'master_soal' => $soalData['title'] ?: 'Tugas Tanpa Judul',
                                        'tahapan_sintaks_id' => $tahapan->tahapan_sintaks_id,
                                        'is_diskusi' => (bool)($soalData['is_diskusi'] ?? false),
                                        'bank_soal_id' => $soalData['bank_soal_id'] ?? null,
                                        'is_show_jawaban' => (bool)($soalData['is_show_jawaban'] ?? false),
                                        'is_show_kunci_jawaban' => (bool)($soalData['is_show_kunci_jawaban'] ?? false),
                                        'is_show_master_soal' => (bool)($soalData['is_show_master_soal'] ?? true),
                                        'is_shared' => (bool)($soalData['is_shared'] ?? false),
                                        'tenggat_waktu' => (function() use ($soalData) {
                                                                if (empty($soalData['tenggat_waktu']) || $soalData['tenggat_waktu'] === '-') {
                                                                    return now()->addDays(7)->format('Y-m-d H:i:s');
                                                                }
                                                                try {
                                                                    return \Carbon\Carbon::parse($soalData['tenggat_waktu'])->format('Y-m-d H:i:s');
                                                                } catch (\Exception $e) {
                                                                    return now()->addDays(7)->format('Y-m-d H:i:s');
                                                                }
                                                            })()
                                    ]
                                );
                                $processedMasterSoalIds[] = $masterSoal->master_soal_id;

                                if (isset($soalData['main_soals']) && is_array($soalData['main_soals'])) {
                                    foreach ($soalData['main_soals'] as $mainSoalData) {
                                        $isNewMain = empty($mainSoalData['db_id']) || !\App\Models\MainSoal::where('main_soal_id', $mainSoalData['db_id'])->exists();
                                        $mainSoal = \App\Models\MainSoal::updateOrCreate(
                                            ['main_soal_id' => $isNewMain ? \Illuminate\Support\Str::uuid() : $mainSoalData['db_id']],
                                            [
                                                'master_soal_id' => $masterSoal->master_soal_id,
                                                'main_soal' => trim($mainSoalData['narasi'] ?? '')
                                            ]
                                        );
                                        $processedMainSoalIds[] = $mainSoal->main_soal_id;

                                        if (isset($mainSoalData['soals']) && is_array($mainSoalData['soals'])) {
                                            foreach ($mainSoalData['soals'] as $soalItem) {
                                                if (!empty(trim($soalItem['pertanyaan'] ?? ''))) {
                                                    $isNewSoal = empty($soalItem['db_id']) || !\App\Models\Soal::where('soal_id', $soalItem['db_id'])->exists();
                                                    $createdSoal = \App\Models\Soal::updateOrCreate(
                                                        ['soal_id' => $isNewSoal ? \Illuminate\Support\Str::uuid() : $soalItem['db_id']],
                                                        [
                                                            'main_soal_id' => $mainSoal->main_soal_id,
                                                            'soal' => trim($soalItem['pertanyaan']),
                                                            'bobot' => $soalItem['bobot'] ?? 10
                                                        ]
                                                    );
                                                    $processedSoalIds[] = $createdSoal->soal_id;

                                                    \App\Models\KunciJawaban::updateOrCreate(
                                                        ['soal_id' => $createdSoal->soal_id],
                                                        [
                                                            'kunci_jawaban' => $soalItem['kunci_jawaban'] ?? null,
                                                            'tipe_soal' => $soalItem['tipe_soal'] ?? 'esai',
                                                            'share_kelas_id' => !empty($soalData['share_kelas_id']) ? $soalData['share_kelas_id'] : null,
                                                            'share_pertemuan_id' => !empty($soalData['share_pertemuan_id']) ? $soalData['share_pertemuan_id'] : null,
                                                        ]
                                                    );

                                                    if (($soalItem['tipe_soal'] ?? '') === 'pilihan_ganda') {
                                                        \App\Models\PilihanGanda::where('soal_id', $createdSoal->soal_id)->delete();
                                                        foreach ($soalItem['pilihan_ganda_options'] ?? [] as $opt) {
                                                            if (!empty(trim($opt['text'] ?? ''))) {
                                                                \App\Models\PilihanGanda::create([
                                                                    'soal_id' => $createdSoal->soal_id,
                                                                    'pilihan_ganda' => trim($opt['text']),
                                                                    'status' => (bool)($opt['is_correct'] ?? false)
                                                                ]);
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Global Cleanup
            if (!empty($processedMainSoalIds)) {
                \App\Models\Soal::whereIn('main_soal_id', $processedMainSoalIds)->whereNotIn('soal_id', $processedSoalIds)->delete();
            }
            if (!empty($processedMasterSoalIds)) {
                \App\Models\MainSoal::whereIn('master_soal_id', $processedMasterSoalIds)->whereNotIn('main_soal_id', $processedMainSoalIds)->delete();
            }
            if (!empty($processedTahapanIds)) {
                \App\Models\MasterSoal::whereIn('tahapan_sintaks_id', $processedTahapanIds)->whereNotIn('master_soal_id', $processedMasterSoalIds)->delete();
                \App\Models\Materi::whereIn('tahapan_sintaks_id', $processedTahapanIds)->whereNotIn('id', $processedMateriIds)->delete();
            }
            \App\Models\TahapanSintaks::where('sintaks_belajar_id', $sintaks->sintaks_belajar_id)
                ->whereNotIn('tahapan_sintaks_id', $processedTahapanIds)
                ->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("saveSyntax ERROR: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
            throw $e;
        }
    }
}
