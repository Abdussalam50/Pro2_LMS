<?php

return [
    'enabled' => env('AI_CHATBOT_ENABLED', false),
    'provider' => env('AI_PROVIDER', 'gemini'),
    
    // Gemini Config
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'chat_model' => env('GEMINI_CHAT_MODEL', 'gemini-3-flash-preview'),
        'embed_model' => env('GEMINI_EMBED_MODEL', 'text-embedding-004'),
    ],

    // Claude Config
    'claude' => [
        'api_key' => env('CLAUDE_API_KEY'),
        'chat_model' => env('CLAUDE_CHAT_MODEL', 'claude-opus-4-6'),
    ],

    'timeout' => env('AI_TIMEOUT', 120),
    'temperature' => env('AI_TEMPERATURE', 0.1),
    'max_tokens' => env('AI_MAX_TOKENS', 750),
    'retry_max' => env('AI_RETRY_MAX', 5),
    'retry_base' => env('AI_RETRY_BASE', 0.8),
    'rag_upload_url' => env('RAG_UPLOAD_URL', 'https://api.example.com/rag/upload'),
    'rag_query_url' => env('RAG_QUERY_URL', 'https://api.example.com/rag/query'),
    'rag_list_url' => env('RAG_SHOW_DOCUMENTS'),
    'rag_preview_url' => env('RAG_DOCUMENT_PREVIEW'),
    'rag_delete_url' => env('RAC_DELETE_DOCUMENT_ACTION'),
];
