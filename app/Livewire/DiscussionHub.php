<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DiskusiKelas;
use App\Models\DiskusiKelompok;
use App\Models\DiskusiDosen;
use App\Models\Pertemuan;
use App\Models\TahapanSintaks;
use App\Models\Kelompok;
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\Auth;

class DiscussionHub extends Component
{
    public $kelasId;
    public $pertemuanId;
    public $kelompokId;
    public $compact = false;
    public $classes = [];
    public $classData = [];

    public function getIsReadonlyProperty()
    {
        return isset($this->classData['academic_period']) && !$this->classData['academic_period']['is_active'];
    }
    
    // UI State
    public $scope = 'class'; // class, group, lecturer
    public $pesanBaru = '';
    public $pesan = [];
    public $rooms = [];
    public $activeRoomId = null;
    
    // Filter Data
    public $selectedPertemuanId;
    public $tahapanSintaksId;

    public function mount($kelasId = null, $pertemuanId = null, $tahapanSintaksId = null, $scope = 'class', $compact = false)
    {
        $this->kelasId = $kelasId;
        $this->pertemuanId = $pertemuanId;
        $this->tahapanSintaksId = $tahapanSintaksId;
        $this->scope = $scope;
        $this->compact = $compact;

        if ($this->kelasId) {
            $service = new \App\Services\ClassroomService();
            $this->classData = $service->getClassData($this->kelasId);
        }

        $this->initializeUserContext();
        
        if (!$this->kelasId) {
            $this->loadClasses();
        }

        $this->selectedPertemuanId = $this->pertemuanId;

        $this->loadRooms();
        $this->loadPesan();
    }

    public function loadClasses()
    {
        $user = Auth::user();
        if ($user->role === 'dosen') {
            $dosenId = $user->dosenData->dosen_id;
            $this->classes = \App\Models\Kelas::with('mataKuliah')->whereHas('mataKuliah', function($q) use ($dosenId) {
                $q->where('dosen_id', $dosenId);
            })->get();
        } else {
            $this->classes = \App\Models\Kelas::with('mataKuliah')->whereHas('mahasiswas', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->get();
        }

        if ($this->classes->count() === 1) {
            $this->kelasId = $this->classes->first()->kelas_id;
        }
    }

    public function selectKelas($id)
    {
        $this->kelasId = $id;
        $service = new \App\Services\ClassroomService();
        $this->classData = $service->getClassData($this->kelasId);
        
        $this->initializeUserContext();
        $this->loadRooms();
        $this->loadPesan();
    }

    protected function initializeUserContext()
    {
        if (!$this->kelasId) return;
        $user = Auth::user();
        if ($user->role === 'mahasiswa') {
            $anggota = \App\Models\KelompokAnggota::where('user_id', $user->id)
                ->whereHas('kelompok', fn($q) => $q->where('kelas_id', $this->kelasId))
                ->first();
            if ($anggota) {
                $this->kelompokId = $anggota->kelompok_id;
            }
        }
    }

    public function loadRooms()
    {
        if (!$this->kelasId) return $this->rooms = [];
        
        $pertemuanList = Pertemuan::with(['sintaksBelajar.tahapanSintaks'])
            ->where('kelas_id', $this->kelasId)
            ->orderBy('pertemuan')
            ->get();
            
        $this->rooms = [];

        $groups = [];
        if (Auth::user()->role === 'dosen') {
            $groups = Kelompok::where('kelas_id', $this->kelasId)->get()->map(fn($k) => [
                'id' => $k->kelompok_id,
                'nama' => $k->nama_kelompok
            ])->toArray();
        }

        foreach ($pertemuanList as $p) {
            $tahapan = collect();
            if ($p->sintaksBelajar) {
                // Use the already loaded relation
                $tahapan = $p->sintaksBelajar->tahapanSintaks;
            }

            $roomData = [
                'pertemuan_id' => $p->pertemuan_id,
                'judul' => $p->pertemuan,
                'tahapan' => $tahapan->map(fn($t) => [
                    'id' => $t->tahapan_sintaks_id,
                    'nama' => $t->nama_tahapan
                ])->toArray(),
                'groups' => $groups
            ];

            $this->rooms[] = $roomData;
        }
    }

    public function setScope($newScope)
    {
        $this->scope = $newScope;
        $this->loadPesan();
    }

    public function selectRoom($pertemuanId, $tahapanId = null, $kelompokId = null)
    {
        $this->selectedPertemuanId = $pertemuanId;
        $this->tahapanSintaksId = $tahapanId;
        if ($kelompokId) {
            $this->kelompokId = $kelompokId;
        }
        $this->loadPesan();
    }

    public function loadPesan()
    {
        $query = null;
        
        if ($this->scope === 'class') {
            $query = DiskusiKelas::with('user:id,name,role')->where('pertemuan_id', $this->selectedPertemuanId);
            if ($this->tahapanSintaksId) {
                $query->where('tahapan_sintaks_id', $this->tahapanSintaksId);
            } else {
                $query->whereNull('tahapan_sintaks_id');
            }
        } elseif ($this->scope === 'group') {
            if (!$this->kelompokId) return $this->pesan = [];
            $query = DiskusiKelompok::with('user:id,name,role')
                ->where('pertemuan_id', $this->selectedPertemuanId)
                ->where('kelompok_id', $this->kelompokId);
            if ($this->tahapanSintaksId) {
                $query->where('tahapan_sintaks_id', $this->tahapanSintaksId);
            }
        } elseif ($this->scope === 'lecturer') {
            if (!$this->kelompokId) return $this->pesan = [];
            $query = DiskusiDosen::with('user:id,name,role')
                ->where('pertemuan_id', $this->selectedPertemuanId)
                ->where('kelompok_id', $this->kelompokId);
        }

        if ($query) {
            $this->pesan = $query->orderBy('created_at')
                ->get()
                ->map(fn($p) => [
                    'id'        => $p->diskusi_kelas_id ?? $p->diskusi_kelompok_id ?? $p->diskusi_dosen_id,
                    'user_id'   => $p->user_id,
                    'user_name' => $p->user->name ?? '?',
                    'role'      => $p->user->role ?? 'mahasiswa',
                    'pesan'     => $p->pesan,
                    'lampiran'  => $p->lampiran_url,
                    'waktu'     => $p->created_at->format('H:i'),
                    'is_me'     => $p->user_id === Auth::id(),
                    'is_dosen'  => ($p->user->role ?? '') === 'dosen',
                ])
                ->toArray();
        } else {
            $this->pesan = [];
        }
        
        $this->markAsRead();
        $this->dispatch('scroll-chat-bottom');
    }

    protected function markAsRead()
    {
        $typeMap = [
            'class' => 'diskusi_kelas',
            'group' => 'diskusi_kelompok',
            'lecturer' => 'diskusi_dosen'
        ];
        $type = $typeMap[$this->scope] ?? null;

        if ($type && Auth::check()) {
            $unreads = \App\Models\Notifikasi::where('user_id', Auth::id())
                ->where('dibaca', false)
                ->where('tipe', $type)
                ->get();
                
            $marked = false;
            foreach ($unreads as $notif) {
                /** @var \App\Models\Notifikasi $notif */
                $data = $notif->data;
                // Match the context ID
                $match = false;
                if ($this->scope === 'class' && isset($data['pertemuan_id']) && $data['pertemuan_id'] == $this->selectedPertemuanId) {
                    $match = true;
                } elseif (in_array($this->scope, ['group', 'lecturer']) && isset($data['pertemuan_id']) && $data['pertemuan_id'] == $this->selectedPertemuanId) {
                    // It should ideally match kelompok_id too, but for scope it's fine just marking the pertemuan
                    $match = true;
                }

                if ($match) {
                    $notif->update(['dibaca' => true]);
                    $marked = true;
                }
            }

            if ($marked) {
                $this->dispatch('discussion_read');
            }
        }
    }

    public function kirimPesan()
    {
        if ($this->isReadonly) return;
        
        $this->validate(['pesanBaru' => 'required|string|min:1']);
        $user = Auth::user();
        $record = null;
        $fcm = app(FirebaseNotificationService::class);

        if (in_array($this->scope, ['group', 'lecturer']) && empty($this->kelompokId)) {
            $this->dispatch('swal', ['type' => 'error', 'title' => 'Akses Ditolak', 'text' => 'Pilih kelompok studi di menu kiri terlebih dahulu sebelum mengirim pesan.']);
            return;
        }

        if ($this->scope === 'class') {
            $record = DiskusiKelas::create([
                'pertemuan_id' => $this->selectedPertemuanId,
                'user_id' => $user->id,
                'pesan' => $this->pesanBaru,
                'tahapan_sintaks_id' => $this->tahapanSintaksId,
            ]);

            $fcm->sendToKelas(
                $this->kelasId,
                $user->role === 'dosen' ? 'Pesan Dosen di Kelas' : 'Pesan di Diskusi Kelas',
                $user->name . ': ' . str($this->pesanBaru)->limit(80),
                ['type' => 'diskusi_kelas', 'kelas_id' => $this->kelasId, 'pertemuan_id' => $this->selectedPertemuanId, 'id' => $record->diskusi_kelas_id],
                $user->id
            );
        } elseif ($this->scope === 'group') {
            $record = DiskusiKelompok::create([
                'pertemuan_id' => $this->selectedPertemuanId,
                'kelompok_id' => $this->kelompokId,
                'user_id' => $user->id,
                'pesan' => $this->pesanBaru,
                'tahapan_sintaks_id' => $this->tahapanSintaksId,
            ]);

            $fcm->sendToKelompok(
                $this->kelompokId,
                'Pesan di Kelompok',
                $user->name . ': ' . str($this->pesanBaru)->limit(80),
                ['type' => 'diskusi_kelompok', 'kelas_id' => $this->kelasId, 'pertemuan_id' => $this->selectedPertemuanId, 'id' => $record->diskusi_kelompok_id],
                $user->id
            );
        } elseif ($this->scope === 'lecturer') {
            $record = DiskusiDosen::create([
                'pertemuan_id' => $this->selectedPertemuanId,
                'kelompok_id' => $this->kelompokId,
                'user_id' => $user->id,
                'pesan' => $this->pesanBaru,
                'tahapan_sintaks_id' => $this->tahapanSintaksId,
            ]);

            // Dosen & Mahasiswa chat (Lecturer to specific group)
            $kelompok = Kelompok::with('kelas')->find($this->kelompokId);
            if ($user->role === 'dosen') {
                // Notif to group members
                $fcm->sendToKelompok(
                    $this->kelompokId,
                    'Konsultasi Dosen ('.($kelompok->nama ?? '-').')',
                    $user->name . ': ' . str($this->pesanBaru)->limit(80),
                    ['type' => 'diskusi_dosen', 'kelas_id' => $this->kelasId, 'id' => $record->diskusi_dosen_id],
                    $user->id
                );
            } else {
                // Notif to Lecturer
                if ($kelompok && $kelompok->kelas && $kelompok->kelas->mataKuliah && $kelompok->kelas->mataKuliah->dosen) {
                    $dosenUserId = $kelompok->kelas->mataKuliah->dosen->user_id;
                    $fcm->sendToUser(
                        $dosenUserId,
                        'Konsultasi dari '.($kelompok->nama ?? 'Kelompok'),
                        $user->name . ': ' . str($this->pesanBaru)->limit(80),
                        ['type' => 'diskusi_dosen', 'kelas_id' => $this->kelasId, 'id' => $record->diskusi_dosen_id]
                    );
                }
            }
        }

        $this->pesanBaru = '';
        $this->loadPesan();
    }

    public function render()
    {
        return view('livewire.discussion-hub')
            ->layout('components.layout');
    }
}
