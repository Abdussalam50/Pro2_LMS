<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DiskusiKelompok as DiskusiKelompokModel;
use App\Models\Kelompok;
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\Auth;

class DiskusiKelompok extends Component
{
    public string $kelasId;
    public string $pertemuanId;
    public ?string $tahapanSintaksId = null;
    public string $pesanBaru = '';
    public array $pesan = [];
    public ?string $kelompokId = null;
    public ?array $kelompokData = null;
    public ?string $tahapanTitle = null;

    public function mount(string $kelasId, string $pertemuanId, ?string $kelompokId = null, ?string $tahapanSintaksId = null): void
    {
        $this->kelasId          = $kelasId;
        $this->pertemuanId      = $pertemuanId;
        $this->tahapanSintaksId = $tahapanSintaksId;

        if ($this->tahapanSintaksId) {
            $tahap = \App\Models\TahapanSintaks::find($this->tahapanSintaksId);
            if ($tahap) $this->tahapanTitle = $tahap->nama_tahapan;
        }

        // Jika kelompokId diberikan (Dosen mode), gunakan itu. 
        // Jika tidak, cari kelompok milik user (Mahasiswa mode).
        if ($kelompokId) {
            $this->kelompokId = $kelompokId;
            $kelompok = Kelompok::find($kelompokId);
            if ($kelompok) $this->kelompokData = $kelompok->toArray();
        } else {
            $user = Auth::user();
            $anggota = \App\Models\KelompokAnggota::where('user_id', $user->id)
                ->whereHas('kelompok', fn($q) => $q->where('kelas_id', $this->kelasId))
                ->with('kelompok')
                ->first();

            if ($anggota) {
                $this->kelompokId   = $anggota->kelompok_id;
                $this->kelompokData = $anggota->kelompok->toArray();
            }
        }

        $this->loadPesan();
    }

    public function loadPesan(): void
    {
        if (!$this->kelompokId) return;

        $query = DiskusiKelompokModel::with('user:id,name,role')
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
                'id'          => $p->diskusi_kelompok_id,
                'user_id'     => $p->user_id,
                'user_name'   => $p->user->name ?? '?',
                'role'        => $p->user->role ?? 'mahasiswa',
                'pesan'       => $p->pesan,
                'lampiran'    => $p->lampiran_url,
                'waktu'       => $p->created_at->format('H:i'),
                'tanggal'     => $p->created_at->format('d M'),
                'is_me'       => $p->user_id === Auth::id(),
                'is_dosen'    => ($p->user->role ?? '') === 'dosen',
            ])
            ->toArray();
            
        $this->markAsRead();
    }

    protected function markAsRead(): void
    {
        if (Auth::check()) {
            $unreads = \App\Models\Notifikasi::where('user_id', Auth::id())
                ->where('dibaca', false)
                ->where('tipe', 'diskusi_kelompok')
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
        if (!$this->kelompokId) return;

        $user = Auth::user();

        // Simpan ke MySQL
        $record = DiskusiKelompokModel::create([
            'pertemuan_id'       => $this->pertemuanId,
            'kelompok_id'        => $this->kelompokId,
            'user_id'            => $user->id,
            'pesan'              => $this->pesanBaru,
            'tahapan_sintaks_id' => $this->tahapanSintaksId,
        ]);

        // Kirim push notification ke anggota kelompok (kecuali pengirim)
        $fcm = app(FirebaseNotificationService::class);
        $title = Auth::user()->role === 'dosen' ? 'Pesan Dosen di Kelompok' : 'Diskusi Kelompok';
        
        $fcm->sendToKelompok(
            $this->kelompokId,
            $title,
            $user->name . ': ' . str($this->pesanBaru)->limit(80),
            [
                'type'               => 'diskusi_kelompok', 
                'kelompok_id'        => $this->kelompokId, 
                'pertemuan_id'       => $this->pertemuanId,
                'tahapan_sintaks_id' => $this->tahapanSintaksId,
                'id'                 => $record->diskusi_kelompok_id
            ],
            $user->id // exclude pengirim
        );

        $this->pesanBaru = '';
        $this->loadPesan();
        $this->dispatch('scroll-chat-bottom');
    }

    public function render()
    {
        return view('livewire.diskusi-kelompok')
            ->layout('components.layout');
    }
}
