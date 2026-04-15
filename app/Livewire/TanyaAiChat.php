<?php

namespace App\Livewire;

use App\Services\GeminiAiService;
use App\Services\ClaudeAiService;
use Livewire\Component;

class TanyaAiChat extends Component
{
    public $userInput = '';
    public $messages = [];
    public $currentTab = 'chat';

    public function mount()
    {
        $role = auth()->user()->role;
        $greeting = $role === 'dosen' 
            ? 'Halo! Saya Korektor Assistant Anda. Saya siap membantu memeriksa jawaban essai mahasiswa atau menjawab pertanyaan umum seputar perkuliahan.' 
            : 'Halo! Saya AI Assistant Pro2Lms. Ada yang bisa saya bantu hari ini?';

        $this->messages[] = [
            'role' => 'assistant',
            'content' => $greeting,
            'timestamp' => now()->format('H:i')
        ];
    }

    public function sendMessage()
    {
        if (trim($this->userInput) === '') return;

        // Resolve Service based on config
        $provider = config('ai.provider', 'gemini');
        $aiService = ($provider === 'claude') ? app(ClaudeAiService::class) : app(GeminiAiService::class);

        $userMessage = $this->userInput;
        $this->messages[] = [
            'role' => 'user', 
            'content' => $userMessage,
            'timestamp' => now()->format('H:i')
        ];
        $this->userInput = '';

        // System instructions based on role - REFINE TO HIGHLY ACADEMIC TONE
        $systemPrompt = auth()->user()->role === 'dosen'
            ? 'Anda adalah asisten akademik senior yang bertaraf internasional. Berikan analisis mendalam, logis, objektif, dan menggunakan parameter pedagogis yang ketat. Bantu dosen dalam evaluasi kritis materi dan jawaban mahasiswa.'
            : 'Anda adalah tutor akademik mentor yang membimbing mahasiswa dengan logika deduktif dan induktif yang kuat. Jelaskan konsep dengan kerangka teoretis yang jelas, sistematis, namun tetap dapat dipahami oleh pembelajar.';

        // Ensure we don't send too many messages to keep context reasonable
        $contextMessages = array_slice($this->messages, -8);
        
        // Add system message if not present
        array_unshift($contextMessages, ['role' => 'system', 'content' => $systemPrompt]);

        $response = $aiService->chat($contextMessages);


        if (isset($response['error'])) {
            $this->messages[] = [
                'role' => 'assistant', 
                'content' => 'Maaf, terjadi kesalahan: ' . $response['error'],
                'timestamp' => now()->format('H:i')
            ];
        } else {
            $this->messages[] = [
                'role' => 'assistant', 
                'content' => $response['content'],
                'timestamp' => now()->format('H:i')
            ];
        }

        $this->dispatch('message-received');
    }

    public function clearChat()
    {
        $this->messages = [];
        $this->mount();
    }

    public function render()
    {
        return view('livewire.tanya-ai-chat')
            ->layout('components.layout');
    }
}
