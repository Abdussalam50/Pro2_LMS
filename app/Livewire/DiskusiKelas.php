<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DiskusiKelas as DiskusiKelasModel;
use App\Models\Pertemuan;
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\Auth;

class DiskusiKelas extends Component
{
    public string $kelasId;
    public string $pertemuanId;
    public ?string $tahapanSintaksId = null;
    public bool $compact = false;
    public string $pesanBaru = '';
    public array $pesan = [];
    public ?array $pertemuanData = null;
    public ?string $tahapanTitle = null;

    public function mount(string $kelasId, string $pertemuanId, ?string $tahapanSintaksId = null, bool $compact = false): void
    {
        $this->kelasId          = $kelasId;
        $this->pertemuanId      = $pertemuanId;
        $this->tahapanSintaksId = $tahapanSintaksId;
        $this->compact          = $compact;

        $pertemuan = Pertemuan::with('kelas')->find($pertemuanId);
        if ($pertemuan) {
            $this->pertemuanData = $pertemuan->toArray();
        }

        if ($this->tahapanSintaksId) {
            $tahap = \App\Models\TahapanSintaks::find($this->tahapanSintaksId);
            if ($tahap) $this->tahapanTitle = $tahap->nama_tahapan;
        }

        $this->loadPesan();
    }

    public function loadPesan(): void
    {
        $query = DiskusiKelasModel::with('user:id,name,role')
            ->where('pertemuan_id', $this->pertemuanId);

        if ($this->tahapanSintaksId) {
            $query->where('tahapan_sintaks_id', $this->tahapanSintaksId);
        } else {
            $query->whereNull('tahapan_sintaks_id');
        }

        $this->pesan = $query->orderBy('created_at')
            ->get()
            ->map(fn($p) => [
                'id'          => $p->diskusi_kelas_id,
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
                ->where('tipe', 'diskusi_kelas')
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

        // Simpan ke MySQL
        $record = DiskusiKelasModel::create([
            'pertemuan_id'       => $this->pertemuanId,
            'user_id'            => $user->id,
            'pesan'              => $this->pesanBaru,
            'tahapan_sintaks_id' => $this->tahapanSintaksId,
        ]);

        // Kirim push notification ke seluruh anggota kelas (kecuali pengirim)
        $fcm = app(FirebaseNotificationService::class);
        $title = $user->role === 'dosen' ? 'Pesan Dosen di Kelas' : 'Pesan di Diskusi Kelas';

        $fcm->sendToKelas(
            $this->kelasId,
            $title,
            $user->name . ': ' . str($this->pesanBaru)->limit(80),
            [
                'type' => 'diskusi_kelas', 
                'kelas_id' => $this->kelasId, 
                'pertemuan_id' => $this->pertemuanId,
                'tahapan_sintaks_id' => $this->tahapanSintaksId,
                'id' => $record->diskusi_kelas_id
            ],
            $user->id // exclude pengirim
        );

        $this->pesanBaru = '';
        $this->loadPesan();
        $this->dispatch('scroll-chat-bottom');
    }

    public function render()
    {
        return view('livewire.diskusi-kelas')
            ->layout('components.layout');
    }
}
