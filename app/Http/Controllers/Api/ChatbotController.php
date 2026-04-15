<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    /**
     * Send message to AI Assistant
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        return response()->json([
            'status' => 'success',
            'response' => 'Halo! Saya adalah asisten AI Anda. Ini adalah respon simulasi dari endpoint API.',
        ]);
    }

    /**
     * Upload document for RAG processing
     */
    public function uploadDocument(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,docx,txt|max:10240',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('rag_documents');

            return response()->json([
                'status' => 'success',
                'message' => 'Document uploaded successfully',
                'file_path' => $path,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'No file uploaded',
        ], 400);
    }

    /**
     * Receive RAG data from external service
     */
    public function receiveRagResponse(Request $request)
    {
        // Validate external payload structure
        $request->validate([
            'source_id' => 'required|string',
            'content' => 'required|string',
            'metadata' => 'nullable|array',
        ]);

        // Here you would typically store this in a database or cache
        // for retrieval during the next AI chat session.
        \Illuminate\Support\Facades\Log::info('RAG Response Received', $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'RAG data received and logged',
        ]);
    }
}
