<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Faq;
use App\Models\Feedback;
use Illuminate\Support\Facades\Auth;

class FaqPage extends Component
{
    public $search = '';
    public $rating = 0;
    public $komentar = '';
    public $kategoriFeedback = 'umum';
    
    public $hasSubmittedToday = false;

    public function mount()
    {
        $this->checkFeedbackStatus();
    }

    public function checkFeedbackStatus()
    {
        $this->hasSubmittedToday = Feedback::where('user_id', Auth::id())
            ->whereDate('created_at', now())
            ->exists();
    }

    public function setRating($val)
    {
        if ($this->hasSubmittedToday) return;
        $this->rating = $val;
    }

    public function submitFeedback()
    {
        if ($this->hasSubmittedToday) return;

        $this->validate([
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string|max:500',
            'kategoriFeedback' => 'required|in:umum,tampilan,fitur,performa',
        ]);

        Feedback::create([
            'user_id' => Auth::id(),
            'rating' => $this->rating,
            'komentar' => $this->komentar,
            'kategori' => $this->kategoriFeedback,
        ]);

        $this->reset(['rating', 'komentar', 'kategoriFeedback']);
        $this->hasSubmittedToday = true;

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Terima Kasih!',
            'text' => 'Feedback Anda sangat berharga bagi kami.',
        ]);
    }

    public function render()
    {
        $faqs = Faq::where('is_active', true)
            ->when($this->search, function($q) {
                $q->where('pertanyaan', 'like', '%' . $this->search . '%')
                  ->orWhere('jawaban', 'like', '%' . $this->search . '%');
            })
            ->orderBy('urutan')
            ->get()
            ->groupBy('kategori');

        return view('livewire.faq-page', [
            'faqGroups' => $faqs
        ])->layout('components.layout');
    }
}
