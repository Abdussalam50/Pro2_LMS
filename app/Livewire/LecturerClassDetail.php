<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\ClassroomService;
use App\Livewire\Traits\ManagesLearningSyntax;
use App\Services\SintaksDuplicatorService;
use App\Models\Kelas;
use App\Models\Pertemuan;
use App\Models\MasterSoal;
use App\Models\JawabanMahasiswa;
use App\Models\User;
use App\Services\GeminiAiService;
use App\Services\ClaudeAiService;
use Illuminate\Support\Facades\Auth;

class LecturerClassDetail extends Component
{
    use WithFileUploads;

    public $classId;
    public $activeTab = 'meetings';

    public function getIsReadonlyProperty()
    {
        return isset($this->classData['academic_period']) && !$this->classData['academic_period']['is_active'];
    }
    
    public $classData = [];
    public $meetings = [];
    public $mahasiswaList = [];
    public $pendingRequests = [];

    public $meetingForm = [
        'id' => null,
        'title' => '', 
        'date' => '', 
        'learning_model' => 'none',
    ];

    public $showMeetingModal = false;

    // Duplication Modal State
    public $showDuplicateModal = false;
    public $duplicateSourceSintaksId = null;
    public $duplicateTargetClassId = '';
    public $duplicateTargetMeetingId = '';
    public $availableClasses = [];
    public $availableMeetings = [];
    public $searchSubmission = '';

    // Grading State
    public $assignments = [];
    public $selectedAssignment = null;
    public $submissions = [];
    public $selectedSubmission = null; // Current student being graded
    public $aiReviews = []; // Store AI analysis per soal_id
    public $gradingForm = [
        'scores' => [], // [soal_id => nilai]
        'notes' => [],  // [soal_id => catatan]
    ];

    // External Materi State
    public $externalMaterials = [];
    public $showExternalMateriModal = false;
    public $externalMateriForm = [
        'id' => null,
        'judul' => '',
        'link' => '',
        'deskripsi' => ''
    ];

    // Ujian Modal State
    public $showUjianModal = false;
    public $ujianForm = [
        'ujian_id' => null,
        'mata_kuliah_id' => '',
        'kelas_id' => '',
        'nama_ujian' => '',
        'deskripsi' => '',
        'jenis_ujian' => 'uts',
        'waktu_mulai' => '',
        'waktu_selesai' => '',
        'jumlah_soal' => 0,
        'bobot_nilai' => 0,
        'is_active' => false,
        'is_open' => false,
        'mode_batasan' => 'open',
    ];

    #[On('openUjianModal')]
    public function openUjianModal($classId = null, $ujianId = null)
    {
        $this->resetUjianForm();
        
        if ($ujianId) {
            $ujian = \App\Models\Ujian::find($ujianId);
            if ($ujian) {
                $this->ujianForm = [
                    'ujian_id' => $ujian->ujian_id,
                    'mata_kuliah_id' => $ujian->mata_kuliah_id,
                    'kelas_id' => $ujian->kelas_id,
                    'nama_ujian' => $ujian->nama_ujian,
                    'deskripsi' => $ujian->deskripsi,
                    'jenis_ujian' => $ujian->jenis_ujian,
                    'waktu_mulai' => $ujian->waktu_mulai->format('Y-m-d\TH:i'),
                    'waktu_selesai' => $ujian->waktu_selesai->format('Y-m-d\TH:i'),
                    'jumlah_soal' => $ujian->jumlah_soal,
                    'bobot_nilai' => $ujian->bobot_nilai,
                    'is_active' => $ujian->is_active,
                    'is_open' => $ujian->is_open,
                    'mode_batasan' => $ujian->mode_batasan,
                ];
            }
        } elseif ($classId) {
            $this->ujianForm['kelas_id'] = $classId;
            $this->ujianForm['mata_kuliah_id'] = $this->classData['course_id'] ?? '';
        }

        $this->showUjianModal = true;
    }

    public function resetUjianForm()
    {
        $this->ujianForm = [
            'ujian_id' => null,
            'mata_kuliah_id' => '',
            'kelas_id' => '',
            'nama_ujian' => '',
            'deskripsi' => '',
            'jenis_ujian' => 'kuis',
            'waktu_mulai' => '',
            'waktu_selesai' => '',
            'jumlah_soal' => 0,
            'bobot_nilai' => 0,
            'is_active' => false,
            'is_open' => false,
            'mode_batasan' => 'open',
        ];
    }

    public function saveUjian()
    {
        if ($this->isReadonly) return;
        
        $this->validate([
            'ujianForm.mata_kuliah_id' => 'required',
            'ujianForm.kelas_id' => 'required',
            'ujianForm.nama_ujian' => 'required|string|max:255',
            'ujianForm.waktu_mulai' => 'required|date',
            'ujianForm.waktu_selesai' => 'required|date|after:ujianForm.waktu_mulai',
            'ujianForm.jumlah_soal' => 'required|integer|min:1',
        ]);

        $dosenId = null;
        $user = auth()->user();
        if ($user && $user->dosen) {
            $dosenId = $user->dosen->dosen_id;
        }

        if (!$dosenId) return;

        $data = $this->ujianForm;
        $data['dosen_id'] = $dosenId;

        if ($data['ujian_id']) {
            \App\Models\Ujian::find($data['ujian_id'])->update($data);
            session()->flash('message', 'Ujian berhasil diperbarui.');
            $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Ujian berhasil diperbarui.', 'icon' => 'success']);
        } else {
            \App\Models\Ujian::create($data);
            session()->flash('message', 'Ujian berhasil dibuat.');
            $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Ujian berhasil dibuat.', 'icon' => 'success']);
        }

        $this->showUjianModal = false;
        $this->dispatch('refreshExams')->to('dosen.manage-ujian');
    }

    public function mount($id)
    {
        $this->classId = $id;
        $this->loadData();
    }

    public function loadData()
    {
        $service = new ClassroomService();
        $classData = $service->getClassData($this->classId);
        
        if ($classData) {
            $this->classData = $classData;
            $this->meetings = $service->getMeetings($this->classId);
            $this->mahasiswaList = $service->getStudents($this->classId);
            
            $this->pendingRequests = \App\Models\EnrollmentRequest::with('mahasiswa')
                ->where('kelas_id', $this->classId)
                ->where('status', 'pending')
                ->get()
                ->map(function($req) {
                    return [
                        'id' => $req->id,
                        'mahasiswa_id' => $req->mahasiswa_id,
                        'nama' => $req->mahasiswa->nama ?? '-',
                        'nim' => $req->mahasiswa->nim ?? '-',
                        'program_studi' => $req->mahasiswa->program_studi ?? '-',
                        'created_at' => $req->created_at->format('Y-m-d H:i:s'),
                    ];
                })->toArray();
            
            $this->loadExternalMaterials();
        } else {
            // Fallback Dummy Data if no class found to prevent UI breakage
            $this->classData = [
                'id' => $this->classId,
                'name' => 'Kelas A (Mock)',
                'code' => 'PWA',
                'course_name' => 'Pemrograman Web',
                'course_code' => 'IF201',
                'students_count' => 35
            ];
            $this->meetings = [];
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        if ($tab === 'grades') {
            $this->loadAssignments();
        }
        if ($tab === 'students') {
            $this->loadStudentsWithStats(); // Fresh data with stats
        }
    }

    public function loadStudentsWithStats()
    {
        $service = new ClassroomService();
        $this->mahasiswaList = $service->getStudents($this->classId, true);
    }



    public function loadExternalMaterials()
    {
        if (isset($this->classData['course_id'])) {
            $this->externalMaterials = \App\Models\ExternalMateri::where('mata_kuliah_id', $this->classData['course_id'])
                ->latest()
                ->get()
                ->toArray();
        }
    }

    public function openExternalMateriModal($id = null)
    {
        $this->resetExternalMateriForm();
        if ($id) {
            $materi = \App\Models\ExternalMateri::find($id);
            if ($materi) {
                $this->externalMateriForm = [
                    'id' => $materi->external_materi_id,
                    'judul' => $materi->judul,
                    'link' => $materi->link,
                    'deskripsi' => $materi->deskripsi
                ];
            }
        }
        $this->showExternalMateriModal = true;
    }

    public function resetExternalMateriForm()
    {
        $this->externalMateriForm = [
            'id' => null,
            'judul' => '',
            'link' => '',
            'deskripsi' => ''
        ];
    }

    public function saveExternalMateri()
    {
        if ($this->isReadonly) return;
        
        $rules = [
            'externalMateriForm.judul' => 'required|string|max:255',
            'externalMateriForm.deskripsi' => 'required|string',
        ];

        if (!$this->externalMateriForm['id'] || is_object($this->externalMateriForm['link'])) {
            $rules['externalMateriForm.link'] = 'required|file|max:20480';
        } else {
             $rules['externalMateriForm.link'] = 'nullable';
        }

        $this->validate($rules, [
            'externalMateriForm.link.required' => 'File materi harus diunggah.',
            'externalMateriForm.link.file' => 'Format file tidak valid.',
            'externalMateriForm.link.max' => 'Ukuran file maksimal 20MB.',
        ]);

        $filePath = null;
        if (is_object($this->externalMateriForm['link'])) {
            $filePath = $this->externalMateriForm['link']->store('external_materials', 'public');
        }

        if ($this->externalMateriForm['id']) {
            $materi = \App\Models\ExternalMateri::find($this->externalMateriForm['id']);
            if ($materi) {
                $materi->judul = $this->externalMateriForm['judul'];
                $materi->deskripsi = $this->externalMateriForm['deskripsi'];
                if ($filePath) {
                    $materi->link = $filePath;
                }
                $materi->save();
            }
        } else {
            \App\Models\ExternalMateri::create([
                'judul' => $this->externalMateriForm['judul'],
                'link' => $filePath,
                'deskripsi' => $this->externalMateriForm['deskripsi'],
                'mata_kuliah_id' => $this->classData['course_id'],
            ]);
        }

        $this->showExternalMateriModal = false;
        $this->loadExternalMaterials();
        session()->flash('message', 'Materi eksternal berhasil disimpan.');
        $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Materi eksternal berhasil disimpan.', 'icon' => 'success']);
    }

    public function deleteExternalMateri($id)
    {
        if ($this->isReadonly) return;
        
        \App\Models\ExternalMateri::destroy($id);
        $this->loadExternalMaterials();
    }

    public function toggleStudentStatus($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $user->is_active = !$user->is_active;
            $user->save();
            $this->loadData(); // Refresh list
            session()->flash('student_message', 'Status mahasiswa berhasil diperbarui.');
            $this->dispatch('swal', ['title' => 'Diperbarui!', 'text' => 'Status mahasiswa berhasil diperbarui.', 'icon' => 'info']);
        }
    }

    public function approveEnrollment($requestId)
    {
        if ($this->isReadonly) return;
        
        $req = \App\Models\EnrollmentRequest::find($requestId);
        if ($req) {
            $mahasiswa = \App\Models\MahasiswaData::find($req->mahasiswa_id);
            if ($mahasiswa) {
                $kelas = \App\Models\Kelas::find($req->kelas_id);
                // Check if student has another class for the same course
                $existingClass = $mahasiswa->enrolledClassForCourse($kelas->mata_kuliah_id);
                if ($existingClass) {
                    // Detach from old class
                    $mahasiswa->kelass()->detach($existingClass->kelas_id);
                    // We should also clear progress, but for simplicity we detach.
                }

                $mahasiswa->kelass()->attach($kelas->kelas_id);
                $mahasiswa->update(['kelas_id' => $kelas->kelas_id]);
                
                $req->delete();
                $this->loadData();
                session()->flash('message', 'Permintaan disetujui, mahasiswa ditambahkan ke kelas.');
                $this->dispatch('swal', ['title' => 'Disetujui!', 'text' => 'Mahasiswa berhasil dimasukkan ke kelas.', 'icon' => 'success']);
            }
        }
    }

    public function rejectEnrollment($requestId)
    {
        if ($this->isReadonly) return;
        
        $req = \App\Models\EnrollmentRequest::find($requestId);
        if ($req) {
            $req->delete();
            $this->loadData();
            session()->flash('message', 'Permintaan bergabung ditolak.');
            $this->dispatch('swal', ['title' => 'Ditolak!', 'text' => 'Permintaan bergabung ke kelas ditolak.', 'icon' => 'info']);
        }
    }

    public function removeStudent($userId)
    {
        if ($this->isReadonly) return;
        
        $user = User::with('mahasiswa')->find($userId);
        if ($user && $user->mahasiswa) {
            $user->mahasiswa->kelass()->detach($this->classId);
            // Optionally update legacy kelas_id if it matched this class
            if ($user->mahasiswa->kelas_id === $this->classId) {
                $user->mahasiswa->update(['kelas_id' => null]);
            }
            $this->loadData();
            session()->flash('message', 'Mahasiswa berhasil dikeluarkan dari kelas.');
            $this->dispatch('swal', ['title' => 'Dikeluarkan!', 'text' => 'Mahasiswa telah dikeluarkan dari kelas.', 'icon' => 'success']);
        }
    }

    public function loadAssignments()
    {
        // Fetch meetings that have assignments
        $this->assignments = \App\Models\Pertemuan::where('kelas_id', $this->classId)
            ->whereHas('sintaksBelajar.tahapanSintaks.masterSoal')
            ->with(['sintaksBelajar.tahapanSintaks.masterSoal'])
            ->get()
            ->flatMap(function($pertemuan) {
                if ($pertemuan->sintaksBelajar && $pertemuan->sintaksBelajar->tahapanSintaks) {
                    return collect($pertemuan->sintaksBelajar->tahapanSintaks)
                        ->flatMap(fn($tahapan) => $tahapan->masterSoal)
                        ->map(function($masterSoal) use ($pertemuan) {
                            $data = is_array($masterSoal) ? $masterSoal : $masterSoal->toArray();
                            $data['meeting_title'] = $pertemuan->pertemuan;
                            $data['meeting_date'] = $pertemuan->tanggal;
                            return $data;
                        });
                }
                return collect();
            })
            ->values()
            ->toArray();
    }

    public function selectAssignment($id)
    {
        $this->selectedAssignment = MasterSoal::with('mainSoal.soal')->find($id)->toArray();
        $this->loadSubmissions($id);
        $this->selectedSubmission = null;
    }

    public function loadSubmissions($assignmentId)
    {
        // Get unique users who have answered any question in this master soal
        $userIds = JawabanMahasiswa::where('master_soal_id', $assignmentId)
            ->distinct()
            ->pluck('user_id');

        // PRELOAD ALL JAWABANS FOR THIS ASSIGNMENT TO FIX N+1
        $allJawabans = JawabanMahasiswa::where('master_soal_id', $assignmentId)
            ->whereIn('user_id', $userIds)
            ->get()
            ->groupBy('user_id');

        $this->submissions = User::whereIn('id', $userIds)
            ->with(['mahasiswa'])
            ->get()
            ->map(function($user) use ($assignmentId, $allJawabans) {
                $jawabans = $allJawabans->get($user->id, collect());
                
                $totalQuestions = count($this->selectedAssignment['main_soal'] ? collect($this->selectedAssignment['main_soal'])->pluck('soal')->flatten(1) : []);
                $gradedCount = $jawabans->whereNotNull('nilai')->count();

                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'nim' => $user->mahasiswa->nim ?? '-',
                    'total_score' => $jawabans->sum('nilai'),
                    'count' => $jawabans->count(),
                    'graded_count' => $gradedCount,
                    'total_questions' => $totalQuestions,
                    'is_fully_graded' => $gradedCount > 0 && $gradedCount >= $totalQuestions,
                ];
            });

        if (!empty($this->searchSubmission)) {
            $searchTerm = strtolower($this->searchSubmission);
            $this->submissions = $this->submissions->filter(function($sub) use ($searchTerm) {
                return str_contains(strtolower($sub['name']), $searchTerm) || 
                       str_contains(strtolower($sub['nim']), $searchTerm);
            });
        }

        $this->submissions = $this->submissions->toArray();
    }

    public function updatedSearchSubmission()
    {
        if ($this->selectedAssignment) {
            $this->loadSubmissions($this->selectedAssignment['master_soal_id']);
        }
    }

    public function selectStudent($userId)
    {
        $assignmentId = $this->selectedAssignment['master_soal_id'];
        
        $jawabans = JawabanMahasiswa::where('master_soal_id', $assignmentId)
            ->where('user_id', $userId)
            ->get();

        $this->selectedSubmission = [
            'user' => User::with('mahasiswa')->find($userId)->toArray(),
            'answers' => $jawabans->keyBy('soal_id')->toArray()
        ];

        // Initialize form
        $this->gradingForm['scores'] = [];
        $this->gradingForm['notes'] = [];
        foreach ($jawabans as $j) {
            $this->gradingForm['scores'][$j->soal_id] = $j->nilai;
            $this->gradingForm['notes'][$j->soal_id] = $j->catatan;
        }
    }

    public function saveGrades()
    {
        if ($this->isReadonly) return;
        
        $userId = $this->selectedSubmission['user']['id'];
        $assignmentId = $this->selectedAssignment['master_soal_id'];

        foreach ($this->gradingForm['scores'] as $soalId => $score) {
            $soal = \App\Models\Soal::find($soalId);
            $maxScore = $soal ? $soal->bobot : 100;
            
            // Limit score to max weight if it exceeds it
            $finalScore = $score ?: 0;
            if ($finalScore > $maxScore) {
                $finalScore = $maxScore;
            }

            JawabanMahasiswa::where('soal_id', $soalId)
                ->where('user_id', $userId)
                ->update([
                    'nilai' => $finalScore,
                    'catatan' => $this->gradingForm['notes'][$soalId] ?? null
                ]);
        }

        session()->flash('grading_success', 'Nilai dan feedback berhasil disimpan.');
        $this->dispatch('swal', ['title' => 'Tersimpan!', 'text' => 'Nilai dan feedback berhasil disimpan.', 'icon' => 'success']);

        try {
            app(\App\Services\FirebaseNotificationService::class)->sendToUser(
                $userId,
                'Nilai Tugas Diperbarui',
                'Dosen telah memberikan nilai dan feedback untuk tugas Anda.',
                [
                    'type' => 'nilai_tugas'
                ]
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengirim notifikasi nilai tugas: ' . $e->getMessage());
        }

        $this->loadSubmissions($assignmentId);
        // Keep selected student to show updated state
        $this->selectStudent($userId);
    }

    public function openMeetingModal($meetingId = null)
    {
        if ($meetingId) {
            $meeting = collect($this->meetings)->firstWhere('id', $meetingId);
            $this->meetingForm = [
                'id' => $meetingId,
                'title' => $meeting['title'],
                'date' => $meeting['date'],
                'learning_model' => $meeting['learning_model'],
            ];
        } else {
            $this->meetingForm = [
                'id' => null,
                'title' => '', 
                'date' => date('Y-m-d'), 
                'learning_model' => 'none',
            ];
        }
        $this->showMeetingModal = true;
    }

    public function saveMeeting(ClassroomService $service)
    {
        if ($this->isReadonly) return;
        
        $this->validate([
            'meetingForm.title' => 'required|string|min:3',
            'meetingForm.date' => 'required|date'
        ], [
            'meetingForm.title.required' => 'Judul pertemuan wajib diisi.',
            'meetingForm.date.required' => 'Tanggal pertemuan wajib diisi.'
        ]);

        \Illuminate\Support\Facades\Log::info("Triggered saveMeeting", ['form' => $this->meetingForm]);
        
        try {
            $meeting = $service->saveMeeting($this->classId, $this->meetingForm, $this->meetingForm['id']);
            
            $this->showMeetingModal = false;
            $this->loadData();

            // If it's a new meeting OR an existing one that is being changed to a learning model,
            // and the user selected a non-'none' model, redirect to Flow Builder
            if ($this->meetingForm['learning_model'] !== 'none') {
                 return redirect("/dosen/classes/{$this->classId}/flow-builder/{$meeting->pertemuan_id}");
            }

            session()->flash('message', 'Pertemuan berhasil disimpan.');
            $this->dispatch('swal', [
                'title' => 'Berhasil!',
                'text' => 'Pertemuan berhasil disimpan.',
                'icon' => 'success'
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("saveMeeting ERROR: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
            session()->flash('error', 'Gagal menyimpan pertemuan: ' . $e->getMessage());
            $this->dispatch('swal', [
                'title' => 'Gagal!',
                'text' => 'Terjadi kesalahan saat menyimpan pertemuan.',
                'icon' => 'error'
            ]);
        }
    }

    public function deleteMeeting($meetingId, ClassroomService $service)
    {
        if ($this->isReadonly) return;
        
        $service->deleteMeeting($meetingId);
        $this->loadData();
    }

    // --- Duplication Logic ---
    public function openDuplicateModal($sintaksId)
    {
        $this->duplicateSourceSintaksId = $sintaksId;
        $this->duplicateTargetClassId = '';
        $this->duplicateTargetMeetingId = '';
        $this->availableMeetings = [];

        // Fetch classes taught by current lecturer.
        // dosen_id column lives on mata_kuliah, not kelas — so we use whereHas.
        $user = Auth::user();
        if ($user && $user->dosen) {
            $dosenId = $user->dosen->dosen_id;
            $this->availableClasses = Kelas::with('mataKuliah')
                ->whereHas('mataKuliah', function ($q) use ($dosenId) {
                    $q->where('dosen_id', $dosenId);
                })->get()->toArray();
        } else {
            // Fallback: show all classes if no dosen relation found (e.g. admin testing)
            $this->availableClasses = Kelas::with('mataKuliah')->get()->toArray();
        }

        $this->showDuplicateModal = true;
    }

    public function updatedDuplicateTargetClassId($classId)
    {
        $this->duplicateTargetMeetingId = '';
        if ($classId) {
            $this->availableMeetings = Pertemuan::where('kelas_id', $classId)
                ->orderBy('created_at', 'asc')
                ->get()
                ->toArray();
        } else {
            $this->availableMeetings = [];
        }
    }

    public function confirmDuplicate(SintaksDuplicatorService $duplicatorService)
    {
        $this->validate([
            'duplicateTargetMeetingId' => 'required',
        ], [
            'duplicateTargetMeetingId.required' => 'Pilih pertemuan tujuan terlebih dahulu.'
        ]);

        $result = $duplicatorService->duplicateSintaks($this->duplicateSourceSintaksId, $this->duplicateTargetMeetingId);

        if ($result) {
            session()->flash('message', 'Sintaks berhasil diduplikasi.');
            $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Sintaks berhasil diduplikasi.', 'icon' => 'success']);
            $this->showDuplicateModal = false;
            $this->loadData();
        } else {
            session()->flash('error', 'Gagal menduplikasi sintaks. Terjadi kesalahan pada server.');
            $this->dispatch('swal', ['title' => 'Gagal!', 'text' => 'Gagal menduplikasi sintaks.', 'icon' => 'error']);
        }
    }

    public function getAiReviewForAssignment($soalId)
    {
        \Illuminate\Support\Facades\Log::info('Triggering AI Review for Assignment', ['soal_id' => $soalId]);

        // Resolve Service based on config
        $provider = config('ai.provider', 'gemini');
        $aiService = ($provider === 'claude') ? app(ClaudeAiService::class) : app(GeminiAiService::class);
        
        $soal = \App\Models\Soal::with('kunciJawaban')->find($soalId);
        $jawaban = $this->selectedSubmission['answers'][$soalId] ?? null;

        if (!$soal || !$jawaban) {
            \Illuminate\Support\Facades\Log::warning('AI Review aborted: Data missing', [
                'soal_found' => (bool)$soal,
                'jawaban_found' => (bool)$jawaban
            ]);
            session()->flash('error', 'Data soal atau jawaban tidak ditemukan.');
            return;
        }

        $this->aiReviews[$soalId] = ['loading' => true];

        try {
            \Illuminate\Support\Facades\Log::info('Calling GeminiAiService::reviewEssay', [
                'soal' => $soal->soal,
                'max_score' => 100
            ]);

            $review = $aiService->reviewEssay(
                $soal->soal,
                $soal->kunciJawaban->kunci_jawaban ?? 'Tidak ada kunci jawaban.',
                $jawaban['jawaban'],
                $soal->bobot ?: 100 // Use question weight
            );

            \Illuminate\Support\Facades\Log::info('GeminiAiService::reviewEssay result', ['review' => $review]);

            if (isset($review['error'])) {
                $this->aiReviews[$soalId] = ['error' => $review['error']];
            } else {
                $this->aiReviews[$soalId] = $review;
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Exception in getAiReviewForAssignment', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->aiReviews[$soalId] = ['error' => 'Terjadi kesalahan: ' . $e->getMessage()];
        }
    }

    public function applyAiReview($soalId)
    {
        if (isset($this->aiReviews[$soalId]) && !isset($this->aiReviews[$soalId]['error'])) {
            $this->gradingForm['scores'][$soalId] = $this->aiReviews[$soalId]['suggested_score'];
            $this->gradingForm['notes'][$soalId] = $this->aiReviews[$soalId]['feedback'];
        }
    }

    public function render()
    {
        \Illuminate\Support\Facades\Log::info('LecturerClassDetail rendering', [
            'activeTab' => $this->activeTab,
            'has_aiReviews' => count($this->aiReviews)
        ]);
        return view('livewire.lecturer-class-detail')
            ->layout('components.layout');
    }
}
