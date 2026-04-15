<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use App\Models\MataKuliah;
use App\Models\Kelas;
use App\Models\Pengumuman;
use App\Models\NotifikasiTerjadwal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\AcademicPeriod;
use App\Services\BaseCrudService;
use Livewire\Attributes\On;

class DosenDashboard extends Component
{
    use WithFileUploads;

    public $activeTab = 'courses';
    public $selectedPeriod = 'active'; // 'active' or UUID of period
    public $activePeriodData = null;

    public $showCourseModal = false;
    public $showClassModal = false;
    public $showAnnouncementModal = false;
    public $showNotificationModal = false;
    public $showUjianModal = false;

    public $courseForm = ['name' => '', 'code' => '', 'description' => ''];
    public $classForm = ['name' => '', 'code' => ''];
    public $announcementForm = ['title' => '', 'content' => ''];
    public $notificationForm = ['title' => '', 'body' => '', 'scheduled_at' => '', 'recurrence' => 'none', 'is_scheduled' => false];
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
    public $selectedCourseId = null;
    public $selectedClassId = null;

    // External Materi State
    public $showExternalMateriModal = false;
    public $externalMateriForm = [
        'id' => null,
        'judul' => '',
        'link' => '',
        'deskripsi' => '',
        'mata_kuliah_id' => ''
    ];
    
    protected function getDosenId()
    {
        $user = auth()->user() ?? User::where('email', 'dosen@lms.com')->first();
        if ($user) {
            $dosen = DB::table('dosens')->where('user_id', $user->id)->first();
            return $dosen ? $dosen->dosen_id : null;
        }
        return null;
    }

    public function mount()
    {
        $this->activePeriodData = AcademicPeriod::getActive();
    }

    #[Computed]
    public function isReadonly()
    {
        if ($this->selectedPeriod === 'active') return false;
        
        $period = AcademicPeriod::find($this->selectedPeriod);
        return $period ? !$period->is_active : true;
    }

    #[Computed]
    public function availablePeriods()
    {
        return AcademicPeriod::orderBy('name')->get();
    }

    #[Computed]
    public function courses()
    {
        $dosenId = $this->getDosenId();
        if (!$dosenId) return [];

        $periodId = $this->selectedPeriod;
        if ($periodId === 'active') {
            $periodId = $this->activePeriodData?->id;
        }

        if (!$periodId) return [];

        // Show only courses that have classes in the selected period
        $matkuls = MataKuliah::where('dosen_id', $dosenId)
            ->whereHas('kelas', function($q) use ($periodId) {
                $q->where('academic_period_id', $periodId);
            })
            ->with(['kelas' => function($q) use ($periodId) {
                $q->where('academic_period_id', $periodId);
            }])
            ->get();

        return $matkuls->map(function ($mk) {
            return [
                'id' => $mk->mata_kuliah_id,
                'name' => $mk->mata_kuliah,
                'code' => $mk->kode,
                'description' => 'Mata kuliah yang diampu.',
                'classes' => $mk->kelas->map(function ($k) {
                    return [
                        'id' => $k->kelas_id,
                        'name' => $k->kelas,
                        'code' => $k->kode
                    ];
                })->toArray()
            ];
        })->toArray();
    }

    #[Computed]
    public function announcements()
    {
        $dosenId = $this->getDosenId();
        if (!$dosenId) return [];

        $crud = new BaseCrudService();
        $pengumumans = $crud->getAll(Pengumuman::class, ['dosen_id' => $dosenId]);
        return $pengumumans->map(function ($p) {
            return [
                'id' => $p->pengumuman_id,
                'title' => $p->judul,
                'content' => $p->konten,
                'created_at' => $p->created_at->format('Y-m-d'),
                'author_name' => 'Dosen Anda'
            ];
        })->toArray();
    }

    #[Computed]
    public function scheduledNotifications()
    {
        $dosenId = $this->getDosenId();
        if (!$dosenId) return [];

        return NotifikasiTerjadwal::with('kelas.mataKuliah')
            ->where('dosen_id', $dosenId)
            ->orderBy('waktu_kirim', 'asc')
            ->get();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function openCourseModal()
    {
        $this->courseForm = ['name' => '', 'code' => '', 'description' => ''];
        $this->showCourseModal = true;
    }

    public function saveCourse()
    {
        $crud = new BaseCrudService();
        $dosenId = $this->getDosenId();
        if ($dosenId) {
            $crud->create(MataKuliah::class, [
                'mata_kuliah' => $this->courseForm['name'],
                'kode' => $this->courseForm['code'],
                'dosen_id' => $dosenId
            ]);
        }
        $this->showCourseModal = false;
    }

    public function deleteCourse($id)
    {
        $crud = new BaseCrudService();
        $crud->delete(MataKuliah::class, $id);
    }

    public function openClassModal($courseId)
    {
        $this->selectedCourseId = $courseId;
        $this->classForm = ['name' => '', 'code' => ''];
        $this->showClassModal = true;
    }

    public function saveClass()
    {
        $crud = new BaseCrudService();
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        
        $crud->create(Kelas::class, [
            'kelas' => $this->classForm['name'],
            'kode' => $this->classForm['code'],
            'mata_kuliah_id' => $this->selectedCourseId,
            'academic_period_id' => $activePeriod?->id
        ]);
        $this->showClassModal = false;
    }

    public function openAnnouncementModal()
    {
        $this->announcementForm = ['title' => '', 'content' => ''];
        $this->showAnnouncementModal = true;
    }

    public function saveAnnouncement()
    {
        $crud = new BaseCrudService();
        $dosenId = $this->getDosenId();
        if ($dosenId) {
            $pengumuman = $crud->create(Pengumuman::class, [
                'judul' => $this->announcementForm['title'],
                'konten' => $this->announcementForm['content'],
                'dosen_id' => $dosenId
            ]);

            // Kirim push notification ke semua mahasiswa yang diajar dosen ini
            $fcm = app(\App\Services\FirebaseNotificationService::class);
            $dosenData = DB::table('dosens')->where('dosen_id', $dosenId)->first();
            $dosenName = $dosenData ? $dosenData->nama : 'Dosen Anda';

            $fcm->sendToSemuaMahasiswaDosen(
                $dosenId,
                'Pengumuman Baru: ' . $this->announcementForm['title'],
                $dosenName . ': ' . str($this->announcementForm['content'])->limit(100),
                ['type' => 'pengumuman', 'id' => $pengumuman->pengumuman_id]
            );
        }
        $this->showAnnouncementModal = false;
    }

    public function deleteAnnouncement($id)
    {
        $crud = new BaseCrudService();
        $crud->delete(Pengumuman::class, $id);
    }

    public function openExternalMateriModal()
    {
        $this->resetExternalMateriForm();
        $this->showExternalMateriModal = true;
    }

    public function resetExternalMateriForm()
    {
        $this->externalMateriForm = [
            'id' => null,
            'judul' => '',
            'link' => '',
            'deskripsi' => '',
            'mata_kuliah_id' => ''
        ];
    }

    public function saveExternalMateri()
    {
        try {
            $this->validate([
                'externalMateriForm.judul' => 'required|string|max:255',
                'externalMateriForm.link' => 'required|file|max:20480', // 20MB max
                'externalMateriForm.deskripsi' => 'required|string',
                'externalMateriForm.mata_kuliah_id' => 'required|exists:mata_kuliah,mata_kuliah_id',
            ], [
                'externalMateriForm.mata_kuliah_id.required' => 'Pilih mata kuliah terlebih dahulu.',
                'externalMateriForm.link.required' => 'File materi harus diunggah.',
                'externalMateriForm.link.file' => 'Format file tidak valid.',
                'externalMateriForm.link.max' => 'Ukuran file maksimal 20MB.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->validate([
                'externalMateriForm.judul' => 'required|string|max:255',
                'externalMateriForm.link' => 'required|file|max:20480',
                'externalMateriForm.deskripsi' => 'required|string',
                'externalMateriForm.mata_kuliah_id' => 'required|exists:mata_kuliah,mata_kuliah_id',
            ]);
            throw $e;
        }

        $filePath = '';
        if ($this->externalMateriForm['link'] instanceof \Illuminate\Http\UploadedFile) {
            $filePath = $this->externalMateriForm['link']->store('external_materials', 'public');
        } else {
            // Jika bukan file baru, mungkin ini string path lama (jika ada fitur edit)
            $filePath = is_string($this->externalMateriForm['link']) ? $this->externalMateriForm['link'] : '';
        }

        \App\Models\ExternalMateri::create([
            'judul' => $this->externalMateriForm['judul'],
            'link' => $filePath,
            'deskripsi' => $this->externalMateriForm['deskripsi'],
            'mata_kuliah_id' => $this->externalMateriForm['mata_kuliah_id'],
        ]);

        $this->showExternalMateriModal = false;
        $this->resetExternalMateriForm();
        session()->flash('message', 'Materi eksternal berhasil ditambahkan.');
        $this->dispatch('swal', [
            'title' => 'Berhasil!',
            'text' => 'Materi eksternal berhasil ditambahkan.',
            'icon' => 'success'
        ]);
    }

    public function openNotificationModal($classId)
    {
        $this->selectedClassId = $classId;
        $this->notificationForm = [
            'title' => '', 
            'body' => '', 
            'scheduled_at' => now()->format('Y-m-d\TH:i'), 
            'recurrence' => 'none',
            'is_scheduled' => false
        ];
        $this->showNotificationModal = true;
    }

    public function sendClassNotification()
    {
        $rules = [
            'notificationForm.title' => 'required|string|max:255',
            'notificationForm.body' => 'required|string',
        ];

        if ($this->notificationForm['is_scheduled']) {
            $rules['notificationForm.scheduled_at'] = 'required|after:now';
            $rules['notificationForm.recurrence'] = 'required|in:none,daily,weekly';
        }

        $this->validate($rules, [
            'notificationForm.title.required' => 'Judul notifikasi harus diisi.',
            'notificationForm.body.required' => 'Isi notifikasi harus diisi.',
            'notificationForm.scheduled_at.after' => 'Waktu jadwal harus di masa depan.',
        ]);

        if ($this->notificationForm['is_scheduled']) {
            NotifikasiTerjadwal::create([
                'dosen_id' => $this->getDosenId(),
                'kelas_id' => $this->selectedClassId,
                'judul' => $this->notificationForm['title'],
                'isi' => $this->notificationForm['body'],
                'waktu_kirim' => $this->notificationForm['scheduled_at'],
                'perulangan' => $this->notificationForm['recurrence'],
                'status' => 'active',
            ]);
            $this->showNotificationModal = false;
            session()->flash('message', 'Notifikasi terjadwal berhasil dibuat.');
            $this->dispatch('swal', [
                'title' => 'Berhasil!',
                'text' => 'Notifikasi terjadwal berhasil dibuat.',
                'icon' => 'success'
            ]);
        } else {
            $fcm = app(\App\Services\FirebaseNotificationService::class);
            $dosenId = $this->getDosenId();
            $dosenData = DB::table('dosens')->where('dosen_id', $dosenId)->first();
            $dosenName = $dosenData ? $dosenData->nama : 'Dosen Anda';

            $fcm->sendToKelas(
                $this->selectedClassId,
                $this->notificationForm['title'],
                $this->notificationForm['body'],
                [
                    'type' => 'info',
                    'sender' => $dosenName,
                    'url' => '/dashboard'
                ]
            );

            $this->showNotificationModal = false;
            session()->flash('message', 'Notifikasi berhasil dikirim ke kelas.');
            $this->dispatch('swal', [
                'title' => 'Terkirim!',
                'text' => 'Notifikasi berhasil dikirim ke kelas.',
                'icon' => 'success'
            ]);
        }
    }

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
            $kelas = Kelas::with('mataKuliah')->find($classId);
            if ($kelas) {
                $this->ujianForm['kelas_id'] = $classId;
                $this->ujianForm['mata_kuliah_id'] = $kelas->mata_kuliah_id;
            }
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
        $this->validate([
            'ujianForm.mata_kuliah_id' => 'required',
            'ujianForm.kelas_id' => 'required',
            'ujianForm.nama_ujian' => 'required|string|max:255',
            'ujianForm.waktu_mulai' => 'required|date',
            'ujianForm.waktu_selesai' => 'required|date|after:ujianForm.waktu_mulai',
            'ujianForm.jumlah_soal' => 'required|integer|min:1',
        ]);

        $dosenId = $this->getDosenId();
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

    public function deleteScheduledNotification($id)
    {
        NotifikasiTerjadwal::destroy($id);
        session()->flash('message', 'Jadwal notifikasi berhasil dihapus.');
        $this->dispatch('swal', [
            'title' => 'Dihapus!',
            'text' => 'Jadwal notifikasi berhasil dihapus.',
            'icon' => 'success'
        ]);
    }

    public function render()
    {
        return view('livewire.dosen-dashboard')
            ->layout('components.layout');
    }
}
