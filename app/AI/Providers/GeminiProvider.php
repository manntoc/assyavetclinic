<?php

namespace App\AI\Providers;

use App\AI\Contracts\AIProviderInterface;
use Illuminate\Support\Facades\Http;
use Exception;

class GeminiProvider implements AIProviderInterface
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('ai.providers.gemini.api_key', env('GEMINI_API_KEY', ''));
    }

    public function checkHealth(): bool
    {
        return !empty($this->apiKey);
    }

    public function chatCompletion(array $messages, array $tools = [], array $options = []): array
    {
        if (empty($this->apiKey)) {
            throw new Exception("Gemini API Key is not configured.");
        }

        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$this->apiKey}";

        $contents = [];
        foreach ($messages as $m) {
            $role = ($m['role'] === 'user') ? 'user' : 'model';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $m['content'] ?? '']]
            ];
        }

        $response = Http::timeout(30)->post($endpoint, ['contents' => $contents]);

        if ($response->successful()) {
            $json = $response->json();
            $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
            return [
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => $text
                        ]
                    ]
                ]
            ];
        }

        throw new Exception("Gemini API Error: " . $response->body());
    }
}
