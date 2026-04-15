<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\RagAiService; // Assuming RagAiService is in this namespace or needs to be imported

class AiChatWidget extends Component
{
    use \Livewire\WithFileUploads;

    public $userInput = '';
    public $messages = [];
    public $isOpen = false;
    
    // Knowledge Base properties (Dosen Only)
    public $kbFile;
    public $kbCategory = 'General';
    public $uploadedFiles = [];
    public $showSettings = false;

    public function mount()
    {
        $this->messages[] = [
            'role' => 'assistant',
            'content' => 'Halo! Saya Chatbot Materi. Tanyakan apa saja seputar materi perkuliahan yang sudah diunggah oleh Dosen.',
            'timestamp' => now()->format('H:i')
        ];

        // Fetch uploaded RAG documents if dosen
        if (auth()->check() && auth()->user()->role === 'dosen') {
            $this->loadUploadedFiles();
        }
    }

    public function loadUploadedFiles(RagAiService $ragService = null)
    {
        $ragService = $ragService ?: app(RagAiService::class);
        $this->uploadedFiles = $ragService->listDocuments();
    }

    public function getPreviewUrl($filename, RagAiService $ragService = null)
    {
        $ragService = $ragService ?: app(RagAiService::class);
        return $ragService->getPreviewUrl($filename);
    }

    public function toggleChat()
    {
        $this->isOpen = !$this->isOpen;
        if ($this->isOpen) {
             $this->dispatch('chat-opened');
        }
    }

    public function toggleSettings()
    {
        $this->showSettings = !$this->showSettings;
    }

    public function uploadKbFile(RagAiService $ragService)
    {
        $this->validate([
            'kbFile' => 'required|mimes:pdf,docx,txt|max:10240',
        ]);

        $path = $this->kbFile->store('temp_rag', 'local');
        $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($path);

        $response = $ragService->uploadDocument($fullPath, [
            'name' => $this->kbFile->getClientOriginalName(),
            'uploaded_by' => auth()->id(),
            'category' => $this->kbCategory
        ]);

        if (isset($response['error'])) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Gagal Sinkronasi',
                'message' => $response['error']
            ]);
        } else {
            $this->loadUploadedFiles($ragService);
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Sinkronasi Berhasil',
                'message' => 'Dokumen berhasil dikirim ke RAG Engine eksternal.',
                'timer' => 3000
            ]);
        }

        $this->kbFile = null;
        
        // Hapus file sementara di server kita setelah berhasil dikirim ke Hugging Face
        // agar tidak memenuhi penyimpanan hosting Anda.
        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($path)) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($path);
        }
    }

    public function deleteKbFile($fileName, RagAiService $ragService = null)
    {
        $ragService = $ragService ?: app(RagAiService::class);
        $success = $ragService->deleteDocument($fileName);

        if ($success) {
            $this->loadUploadedFiles($ragService);
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Terhapus',
                'message' => 'Dokumen dihapus dari index RAG.',
                'timer' => 2000
            ]);
        } else {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Gagal Menghapus',
                'message' => 'Gagal menghapus dokumen dari RAG Engine.'
            ]);
        }
    }

    public function sendMessage(RagAiService $ragService)
    {
        if (trim($this->userInput) === '') return;

        $message = $this->userInput;
        $this->messages[] = [
            'role' => 'user', 
            'content' => $message,
            'timestamp' => now()->format('H:i')
        ];
        $this->userInput = '';

        $response = $ragService->queryBot($message);


        if (isset($response['error'])) {
            $this->messages[] = [
                'role' => 'assistant', 
                'content' => 'Maaf, saya sedang kesulitan mengakses database materi: ' . $response['error'],
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

    public function render()
    {
        return view('livewire.ai-chat-widget');
    }
}
