<?php

namespace App\AI\Providers;

use App\AI\Contracts\AIProviderInterface;
use Illuminate\Support\Facades\Http;
use Exception;

class OpenAIProvider implements AIProviderInterface
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('ai.providers.openai.api_key', env('OPENAI_API_KEY', ''));
        $this->baseUrl = config('ai.providers.openai.base_url', 'https://api.openai.com/v1');
    }

    public function checkHealth(): bool
    {
        if (empty($this->apiKey)) return false;
        try {
            $response = Http::withToken($this->apiKey)->timeout(5)->get("{$this->baseUrl}/models");
            return $response->successful();
        } catch (Exception $e) {
            return false;
        }
    }

    public function chatCompletion(array $messages, array $tools = [], array $options = []): array
    {
        $model = $options['model'] ?? 'gpt-4o';
        $payload = array_merge([
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.2,
        ], $options);

        if (!empty($tools)) {
            $payload['tools'] = $tools;
        }

        $response = Http::withToken($this->apiKey)->timeout(30)->post("{$this->baseUrl}/chat/completions", $payload);

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception("OpenAI API Error: " . $response->body());
    }
}
