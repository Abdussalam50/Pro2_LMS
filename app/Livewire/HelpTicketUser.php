<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\HelpTicket;
use Illuminate\Support\Facades\Auth;

class HelpTicketUser extends Component
{
    public $kategori = 'lainnya';
    public $subjek;
    public $pesan;
    public $prioritas = 'sedang';
    
    public $showCreateForm = false;
    public $selectedTicket = null;

    protected $rules = [
        'subjek' => 'required|min:5|max:255',
        'pesan' => 'required|min:10',
        'kategori' => 'required|in:bug,akun,kelas,ujian,lainnya',
        'prioritas' => 'required|in:rendah,sedang,tinggi',
    ];

    public function createTicket()
    {
        $this->validate();

        HelpTicket::create([
            'user_id' => Auth::id(),
            'kategori' => $this->kategori,
            'subjek' => $this->subjek,
            'pesan' => $this->pesan,
            'prioritas' => $this->prioritas,
            'status' => 'open',
        ]);

        $this->reset(['subjek', 'pesan', 'kategori', 'prioritas', 'showCreateForm']);
        
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Tiket Terkirim',
            'text' => 'Tiket bantuan Anda telah berhasil dikirim ke Admin.',
        ]);
    }

    public function selectTicket($id)
    {
        $this->selectedTicket = HelpTicket::where('user_id', Auth::id())->findOrFail($id);
    }

    public function closeTicket($id)
    {
        $ticket = HelpTicket::where('user_id', Auth::id())->findOrFail($id);
        $ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
        
        if ($this->selectedTicket && $this->selectedTicket->id == $id) {
            $this->selectedTicket = $ticket;
        }

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Tiket Ditutup',
            'text' => 'Tiket bantuan telah ditutup.',
        ]);
    }

    public function render()
    {
        $tickets = HelpTicket::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('livewire.help-ticket-user', [
            'tickets' => $tickets
        ])->layout('components.layout');
    }
}
