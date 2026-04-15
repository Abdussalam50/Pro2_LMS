<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeAiService
{
    protected $apiKey;
    protected $model;
    protected $baseUrl = 'https://api.anthropic.com/v1/messages';
    protected $version = '2023-06-01';

    public function __construct()
    {
        $this->apiKey = config('ai.claude.api_key');
        $this->model = config('ai.claude.chat_model', 'claude-opus-4-6');
    }

    /**
     * Chat with Claude using Anthropic Messages API.
     */
    public function chat(array $messages)
    {
        if (!$this->apiKey) {
            return ['error' => 'Claude API Key is not configured in .env.'];
        }

        $systemPrompt = '';
        $formattedMessages = [];

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $systemPrompt .= $msg['content'] . ' ';
            } else {
                // Anthropic only accepts 'user' and 'assistant' roles in the messages array
                $role = ($msg['role'] === 'user') ? 'user' : 'assistant';
                $formattedMessages[] = [
                    'role' => $role,
                    'content' => $msg['content'],
                ];
            }
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => $this->version,
                'content-type' => 'application/json',
            ])->timeout(config('ai.timeout', 120))
              ->post($this->baseUrl, [
                'model' => $this->model,
                'max_tokens' => (int) config('ai.max_tokens', 4096),
                'temperature' => (float) config('ai.temperature', 0.2), // Lower temperature for more academic/logical responses
                'system' => trim($systemPrompt),
                'messages' => $formattedMessages,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Per user reference: content is an array
                return [
                    'content' => $data['content'][0]['text'] ?? 'Maaf, saya tidak bisa memproses permintaan Anda saat ini.',
                ];
            }

            Log::error('Claude API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'error' => 'Gagal terhubung ke Claude API: ' . $response->status(),
                'details' => $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('Claude Service Exception', [
                'message' => $e->getMessage()
            ]);
            return ['error' => 'Terjadi kesalahan sistem saat menghubungi Claude.'];
        }
    }

    /**
     * Logic for essay review using Claude.
     */
    public function reviewEssay($question, $reference, $studentAnswer, $maxScore)
    {
        $systemPrompt = "Anda adalah Asisten Akademik Senior khusus Koreksi Jawaban Essay. 
        Tujuan Anda adalah memberikan penilaian yang logis, adil, mendalam, dan menggunakan terminologi akademik yang tepat.
        
        PENDEKATAN: Berikan evaluasi komprehensif terhadap akurasi konseptual, kedalaman argumentasi, dan relevansi jawaban terhadap kunci jawaban yang diberikan.";

        $userPrompt = "Evaluasilah jawaban mahasiswa berikut berdasarkan parameter di bawah:
        
        PERTANYAAN: \"$question\"
        REFERENSI AKADEMIK (KUNCI): \"$reference\"
        JAWABAN MAHASISWA: \"$studentAnswer\"
        BOBOT NILAI MAKSIMAL: $maxScore
        
        SKEMA PENILAIAN:
        1. Akurasi Konsep (Sejauh mana jawaban sesuai dengan referensi).
        2. Kelengkapan (Apakah seluruh poin penting dalam pertanyaan terjawab).
        3. Logika (Kesesuaian alur berpikir).
        
        HASIL: Keluarkan dalam format JSON murni tanpa pembuka/penutup atau blok markdown:
        {
          \"suggested_score\": (angka 0 sampai $maxScore),
          \"feedback\": \"(Analisis akademik mendalam dalam Bahasa Indonesia)\"
        }";

        $response = $this->chat([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt]
        ]);

        if (isset($response['error'])) return $response;

        $content = trim($response['content']);
        $content = str_replace(['```json', '```'], '', $content);
        $content = trim($content);

        return json_decode($content, true) ?: ['error' => 'Gagal menguraikan penilaian akademik.'];
    }
}
