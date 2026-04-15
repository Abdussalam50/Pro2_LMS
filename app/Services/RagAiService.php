<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RagAiService
{
    protected $uploadUrl;
    protected $queryUrl;
    protected $listUrl;
    protected $previewUrl;
    protected $deleteUrl;

    public function __construct()
    {
        $this->uploadUrl = config('ai.rag_upload_url');
        $this->queryUrl = config('ai.rag_query_url');
        $this->listUrl = config('ai.rag_list_url');
        $this->previewUrl = config('ai.rag_preview_url');
        $this->deleteUrl = config('ai.rag_delete_url');
    }

    /**
     * Upload a document to the RAG engine.
     * Hits Endpoint 1 (Dosen Only).
     */
    public function uploadDocument($filePath, $metadata = [])
    {
        if (!$this->uploadUrl) {
            return ['error' => 'RAG Upload URL is not configured.'];
        }

        try {
            // The API expects 'file' and 'category'
            $category = $metadata['category'] ?? 'General';
            
            Log::info('RAG Upload Initiated', [
                'url' => $this->uploadUrl,
                'file' => basename($filePath),
                'category' => $category
            ]);

            $response = Http::attach(
                'file', file_get_contents($filePath), basename($filePath)
            )->post($this->uploadUrl, [
                'category' => $category
            ]);

            if ($response->successful()) {
                Log::info('RAG Upload Success', ['response' => $response->json()]);
                return $response->json();
            }

            Log::error('RAG Upload Failed', [
                'status' => $response->status(),
                'response' => $response->body(),
                'url' => $this->uploadUrl
            ]);
            return ['error' => 'Gagal mengunggah ke RAG Engine: ' . $response->status()];

        } catch (\Exception $e) {
            Log::error('RAG Upload Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['error' => 'Terjadi kesalahan sistem saat menghubungi RAG Engine.'];
        }
    }

    /**
     * Query the RAG engine for material questions.
     * Hits Endpoint 2 (Students/Dosen).
     */
    public function queryBot($message, $context = [])
    {
        if (!$this->queryUrl) {
            return ['error' => 'RAG Query URL is not configured.'];
        }

        try {
            Log::info('RAG Query Initiated', [
                'url' => $this->queryUrl,
                'query' => $message,
                'context_count' => count($context)
            ]);

            $response = Http::post($this->queryUrl, array_merge([
                'query' => $message
            ], $context));

            if ($response->successful()) {
                $data = $response->json();
                Log::info('RAG Query Success');
                return [
                    'content' => $data['answer'] ?? $data['content'] ?? 'Maaf, saya tidak menemukan jawaban di materi.',
                ];
            }

            Log::error('RAG Query Failed', [
                'status' => $response->status(),
                'response' => $response->body(),
                'url' => $this->queryUrl
            ]);
            return ['error' => 'Gagal mendapatkan jawaban dari RAG Engine: ' . $response->status()];

        } catch (\Exception $e) {
            Log::error('RAG Query Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['error' => 'Terjadi kesalahan sistem saat menghubungi RAG Engine.'];
        }
    }

    /**
     * Get list of documents from RAG engine.
     * Hits Endpoint 3.
     */
    public function listDocuments()
    {
        if (!$this->listUrl) return [];

        try {
            $response = Http::get($this->listUrl);
            if ($response->successful()) {
                $data = $response->json();
                return $data['documents'] ?? [];
            }
            return [];
        } catch (\Exception $e) {
            Log::error('RAG List Exception: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete document from RAG engine.
     * Hits Endpoint 5.
     */
    public function deleteDocument($fileName)
    {
        if (!$this->deleteUrl) return false;

        try {
            $url = str_replace('{nama_file.pdf}', $fileName, $this->deleteUrl);
            $response = Http::delete($url);
            
            Log::info('RAG Delete Response', ['status' => $response->status(), 'body' => $response->body()]);
            
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('RAG Delete Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get preview URL for a document.
     */
    public function getPreviewUrl($fileName)
    {
        if (!$this->previewUrl) return '#';
        return str_replace('{nama_file.pdf}', $fileName, $this->previewUrl);
    }
}
