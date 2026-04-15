<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiAiService
{
    protected $apiKey;
    protected $model;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('ai.api_key');
        $this->model = config('ai.chat_model', 'gemini-3-flash-preview');
        
        // Determine base URL based on model name
        // Preview models often require v1beta
        if (str_contains($this->model, '-preview') || str_contains($this->model, 'gemini-3')) {
            $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';
        } else {
            $this->baseUrl = 'https://generativelanguage.googleapis.com/v1/models/';
        }
    }

    public function chat(array $messages)
    {
        if (!$this->apiKey) {
            return ['error' => 'Gemini API Key is not configured.'];
        }

        $contents = [];
        foreach ($messages as $msg) {
            $contents[] = [
                'role' => ($msg['role'] === 'user' || $msg['role'] === 'system') ? 'user' : 'model',
                'parts' => [['text' => $msg['content']]],
            ];
        }

        try {
            $apiUrl = $this->baseUrl . $this->model . ':generateContent?key=' . $this->apiKey;
            
            // Dedicated logging to track exact request
            $logPath = storage_path('logs/gemini.log');
            $logEntry = "[" . now()->toDateTimeString() . "] REQUEST: " . $apiUrl . "\n" .
                        "MODEL: " . $this->model . "\n" .
                        "CONTENTS: " . json_encode($contents) . "\n\n";
            file_put_contents($logPath, $logEntry, FILE_APPEND);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(config('ai.timeout', 120))
              ->post($apiUrl, [
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => (float) config('ai.temperature', 0.1),
                    'maxOutputTokens' => (int) config('ai.max_tokens', 750),
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                file_put_contents($logPath, "[" . now()->toDateTimeString() . "] SUCCESS\n\n", FILE_APPEND);
                return [
                    'content' => $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Maaf, saya tidak bisa memproses permintaan Anda saat ini.',
                ];
            }

            $errorEntry = "[" . now()->toDateTimeString() . "] ERROR: " . $response->status() . "\n" .
                          "BODY: " . $response->body() . "\n\n";
            file_put_contents($logPath, $errorEntry, FILE_APPEND);
            
            return [
                'error' => 'Gagal terhubung ke Gemini API: ' . $response->status(),
                'raw_response' => $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('Gemini Service Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['error' => 'Terjadi kesalahan sistem saat menghubungi AI.'];
        }
    }

    public function reviewEssay($question, $reference, $studentAnswer, $maxScore)
    {
        $prompt = "Anda adalah Asisten Korektor Jawaban (Essay). 
        Tugas Anda adalah menilai jawaban mahasiswa berdasarkan kunci jawaban/referensi yang diberikan.
        
        PERTANYAAN: \"$question\"
        KUNCI JAWABAN/REFERENSI: \"$reference\"
        JAWABAN MAHASISWA: \"$studentAnswer\"
        BOBOT MAKSIMAL: $maxScore
        
        Berikan penilaian obyektif. Bandingkan inti jawaban mahasiswa dengan referensi.
        Keluarkan hasil dalam format JSON murni tanpa markdown: 
        {
          \"suggested_score\": (angka 0 sampai $maxScore),
          \"feedback\": \"(penjelasan singkat kenapa skor tersebut diberikan dalam Bahasa Indonesia)\"
        }";

        $response = $this->chat([
            ['role' => 'user', 'content' => $prompt]
        ]);

        if (isset($response['error'])) return $response;

        // Clean up markdown if AI returns it despite the prompt
        $content = trim($response['content']);
        $content = str_replace(['```json', '```'], '', $content);
        $content = trim($content);

        $data = json_decode($content, true);
        if (!$data) {
             return ['error' => 'Gagal menguraikan penilaian AI. Isi: ' . $content];
        }

        return $data;
    }
}
