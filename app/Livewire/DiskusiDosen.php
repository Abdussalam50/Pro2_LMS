<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DiskusiDosen as DiskusiDosenModel;
use App\Models\Kelompok;
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\Auth;

class DiskusiDosen extends Component
{
    public string $kelasId;
    public string $pertemuanId;
    public string $kelompokId;
    public ?string $tahapanSintaksId = null;
    public string $pesanBaru = '';
    public array $pesan = [];
    public ?array $kelompokData = null;
    public ?string $tahapanTitle = null;

    public function mount(string $kelasId, string $pertemuanId, string $kelompokId, ?string $tahapanSintaksId = null): void
    {
        $this->kelasId          = $kelasId;
        $this->pertemuanId      = $pertemuanId;
        $this->kelompokId       = $kelompokId;
        $this->tahapanSintaksId = $tahapanSintaksId;

        if ($this->tahapanSintaksId) {
            $tahap = \App\Models\TahapanSintaks::find($this->tahapanSintaksId);
            if ($tahap) $this->tahapanTitle = $tahap->nama_tahapan;
        }

        $kelompok = Kelompok::find($kelompokId);
        if ($kelompok) $this->kelompokData = $kelompok->toArray();

        $this->loadPesan();
    }

    public function loadPesan(): void
    {
        $query = DiskusiDosenModel::with('user:id,name,role')
            ->where('pertemuan_id', $this->pertemuanId)
            ->where('kelompok_id', $this->kelompokId);

        if ($this->tahapanSintaksId) {
            $query->where('tahapan_sintaks_id', $this->tahapanSintaksId);
        } else {
            $query->whereNull('tahapan_sintaks_id');
        }

        $this->pesan = $query->orderBy('created_at')
            ->get()
            ->map(fn($p) => [
                'id'        => $p->diskusi_dosen_id,
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
            
        $this->markAsRead();
    }

    protected function markAsRead(): void
    {
        if (Auth::check()) {
            $unreads = \App\Models\Notifikasi::where('user_id', Auth::id())
                ->where('dibaca', false)
                ->where('tipe', 'diskusi_dosen')
                ->get();
                
            $marked = false;
            foreach ($unreads as $notif) {
                if (isset($notif->data['pertemuan_id']) && $notif->data['pertemuan_id'] == $this->pertemuanId) {
                    $notif->update(['dibaca' => true]);
                    $marked = true;
                }
            }
            if ($marked) {
                $this->dispatch('discussion_read');
            }
        }
    }

    public function kirimPesan(): void
    {
        $this->validate(['pesanBaru' => 'required|string|min:1']);

        $user = Auth::user();

        $diskusi = DiskusiDosenModel::create([
            'pertemuan_id'       => $this->pertemuanId,
            'kelompok_id'        => $this->kelompokId,
            'user_id'            => $user->id,
            'pesan'              => $this->pesanBaru,
            'tahapan_sintaks_id' => $this->tahapanSintaksId,
        ]);

        $fcm   = app(FirebaseNotificationService::class);
        $isDosen = $user->role === 'dosen';

        if ($isDosen) {
            // Dosen kirim → notif ke anggota kelompok
            $fcm->sendToKelompok(
                $this->kelompokId,
                'Pesan dari Dosen',
                $user->name . ': ' . str($this->pesanBaru)->limit(80),
                [
                    'type'               => 'diskusi_dosen', 
                    'kelompok_id'        => $this->kelompokId, 
                    'pertemuan_id'       => $this->pertemuanId,
                    'tahapan_sintaks_id' => $this->tahapanSintaksId,
                    'id'                 => $diskusi->diskusi_dosen_id
                ],
                $user->id
            );
        } else {
            // Mahasiswa kirim → cari user_id dosen dari pertemuan → kirim
            $pertemuan = \App\Models\Pertemuan::with('kelas.mataKuliah.dosen')->find($this->pertemuanId);
            if ($pertemuan && $pertemuan->kelas && $pertemuan->kelas->mataKuliah && $pertemuan->kelas->mataKuliah->dosen) {
                $dosenUserId = $pertemuan->kelas->mataKuliah->dosen->user_id;
                if ($dosenUserId) {
                    $fcm->sendToUser(
                        $dosenUserId,
                        'Diskusi dari Kelompok',
                        $user->name . ': ' . str($this->pesanBaru)->limit(80),
                        [
                            'type'               => 'diskusi_dosen', 
                            'kelompok_id'        => $this->kelompokId, 
                            'pertemuan_id'       => $this->pertemuanId,
                            'tahapan_sintaks_id' => $this->tahapanSintaksId,
                            'id'                 => $diskusi->diskusi_dosen_id
                        ]
                    );
                }
            }
        }

        $this->pesanBaru = '';
        $this->loadPesan();
        $this->dispatch('scroll-chat-bottom');
    }

    public function render()
    {
        return view('livewire.diskusi-dosen')
            ->layout('components.layout');
    }
}
