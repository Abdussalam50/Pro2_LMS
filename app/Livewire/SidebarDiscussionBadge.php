<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\Auth;

class SidebarDiscussionBadge extends Component
{
    public $unreadCount = 0;

    protected $listeners = [
        'fcm-message' => 'refreshCount', 
        'refreshNotifications' => 'refreshCount',
        'discussion_read' => 'refreshCount'
    ];

    public function mount()
    {
        $this->refreshCount();
    }

    public function refreshCount()
    {
        if (Auth::check()) {
            $this->unreadCount = Notifikasi::where('user_id', Auth::id())
                ->where('dibaca', false)
                ->whereIn('tipe', ['diskusi_kelas', 'diskusi_kelompok', 'diskusi_dosen'])
                ->count();
        }
    }

    public function render()
    {
        return view('livewire.sidebar-discussion-badge');
    }
}
