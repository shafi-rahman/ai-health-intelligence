<?php

return [

    'default' => 'ollama',

    // Embedding model for RAG (run: ollama pull nomic-embed-text)
    'embedding_model' => env('OLLAMA_EMBEDDING_MODEL', 'nomic-embed-text'),

    // Ollama inference limits — health analysis needs more tokens than basic chat
    'num_ctx'     => (int) env('OLLAMA_NUM_CTX', 4096),
    'num_predict' => (int) env('OLLAMA_NUM_PREDICT', 1024),

    // Health AI system prompt — default for all chat sessions
    'health_system_prompt' => "You are a personal AI health information assistant for the AI Health Intelligence Platform.\n\nYour role is to help users understand their medical reports, prescriptions, and general health information.\n\nRules:\n- Never provide a medical diagnosis\n- Never recommend starting, stopping, or changing medications\n- Use safe, informational, and neutral language\n- Always encourage the user to consult a qualified healthcare provider for medical decisions\n- When referencing uploaded health documents, be accurate and cite clearly\n\nIMPORTANT DISCLAIMER: This AI provides informational insights only and is not a substitute for professional medical advice. Please consult a qualified healthcare provider for any health decisions.",

    // Disclaimer appended to all structured health analysis outputs
    'medical_disclaimer' => 'This tool provides informational insights only and is not a substitute for professional medical advice. Please consult a qualified healthcare provider.',

    'providers' => [

        'ollama' => [
            'url'    => env('OLLAMA_URL', 'http://127.0.0.1:11434/api/chat'),
            'models' => [
                'phi'    => 'phi:latest',
                'llama3' => 'llama3:latest',
                'gemma4' => 'gemma4:latest',
            ],
        ],

        // future
        'openai' => [
            'models' => [
                'gpt4' => 'gpt-4o',
            ],
        ],

    ],

];
