<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Provider & Model
    |--------------------------------------------------------------------------
    */
    'default_provider' => env('AI_PROVIDER', 'ninerouter'),

    'default_model' => env('AI_MODEL', 'gpt-4o'),

    'timeout' => env('AI_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Providers Configuration
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'ninerouter' => [
            'class' => \App\AI\Providers\NineRouterProvider::class,
            'api_key' => env('NINEROUTER_API_KEY', ''),
            'base_url' => env('NINEROUTER_BASE_URL', 'http://localhost:20128/v1'),
        ],
        'openai' => [
            'class' => \App\AI\Providers\OpenAIProvider::class,
            'api_key' => env('OPENAI_API_KEY', ''),
            'base_url' => 'https://api.openai.com/v1',
        ],
        'gemini' => [
            'class' => \App\AI\Providers\GeminiProvider::class,
            'api_key' => env('GEMINI_API_KEY', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tool Confirmation & Security
    |--------------------------------------------------------------------------
    */
    'require_confirmation_tools' => [
        'create_owner',
        'update_owner',
        'create_pet',
        'update_pet',
        'create_appointment',
        'update_appointment',
        'cancel_appointment',
        'create_temporary_invoice',
        'update_temporary_invoice',
        'cancel_temporary_invoice',
    ],
];
