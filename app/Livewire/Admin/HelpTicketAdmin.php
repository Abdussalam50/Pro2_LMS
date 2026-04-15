<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\HelpTicket;
use Livewire\WithPagination;

class HelpTicketAdmin extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $kategoriFilter = '';
    
    public $selectedTicket = null;
    public $balasan_admin = '';
    public $newStatus = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'kategoriFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function selectTicket($id)
    {
        $this->selectedTicket = HelpTicket::with('user')->findOrFail($id);
        $this->balasan_admin = $this->selectedTicket->balasan_admin;
        $this->newStatus = $this->selectedTicket->status;
    }

    public function updateTicket()
    {
        $this->validate([
            'balasan_admin' => 'required|min:5',
            'newStatus' => 'required|in:open,in_progress,closed',
        ]);

        $this->selectedTicket->update([
            'balasan_admin' => $this->balasan_admin,
            'status' => $this->newStatus,
            'closed_at' => ($this->newStatus == 'closed') ? now() : null,
        ]);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Tiket Diupdate',
            'text' => 'Balasan telah disimpan dan status tiket diperbarui.',
        ]);

        $this->selectedTicket->refresh();
    }

    public function deleteTicket($id)
    {
        HelpTicket::findOrFail($id)->delete();
        if ($this->selectedTicket && $this->selectedTicket->id == $id) {
            $this->selectedTicket = null;
        }
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Terhapus',
            'text' => 'Tiket telah dihapus permanen.',
        ]);
    }

    public function render()
    {
        $query = HelpTicket::with('user')
            ->when($this->search, function($q) {
                $q->where(function($sq) {
                    $sq->where('subjek', 'like', '%' . $this->search . '%')
                       ->orWhere('pesan', 'like', '%' . $this->search . '%')
                       ->orWhereHas('user', function($uq) {
                           $uq->where('name', 'like', '%' . $this->search . '%');
                       });
                });
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->kategoriFilter, fn($q) => $q->where('kategori', $this->kategoriFilter))
            ->latest();

        return view('livewire.admin.help-ticket-admin', [
            'tickets' => $query->paginate(10)
        ])->layout('components.layout');
    }
}
