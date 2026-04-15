<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\Auth;

class SidebarNotifications extends Component
{
    public $unreadCount = 0;
    public $notifications = [];
    public $showDropdown = false;

    protected $listeners = ['fcm-message' => 'refreshNotifications', 'refreshNotifications' => 'refreshNotifications'];

    public function mount()
    {
        $this->refreshNotifications();
    }

    public function refreshNotifications()
    {
        if (Auth::check()) {
            $userId = Auth::id();
            $this->unreadCount = Notifikasi::where('user_id', $userId)->where('dibaca', false)->count();
            // Hanya ambil yang belum dibaca agar list bersih sesuai permintaan user
            $this->notifications = Notifikasi::where('user_id', $userId)
                ->where('dibaca', false)
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();
        }
    }

    public function markAllAsRead()
    {
        if (Auth::check()) {
            Notifikasi::where('user_id', Auth::id())
                ->where('dibaca', false)
                ->update(['dibaca' => true]);
            
            $this->refreshNotifications();
        }
    }

    public function markAsRead($id)
    {
        $notification = Notifikasi::find($id);
        if ($notification && $notification->user_id === Auth::id()) {
            $notification->markAsRead();
            $this->refreshNotifications();
        }
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
        if ($this->showDropdown) {
            $this->refreshNotifications();
        }
    }

    public function render()
    {
        return view('livewire.sidebar-notifications');
    }
}
