<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class NineRouterService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = trim(
            (string) (config('services.ninerouter.api_key') 
            ?: config('ai.providers.ninerouter.api_key') 
            ?: env('AI_API_KEY') 
            ?: env('GERAIKITA_API_KEY') 
            ?: env('GK_API_KEY') 
            ?: env('NINEROUTER_API_KEY', ''))
        );
        $baseUrl = config('services.ninerouter.base_url') ?: env('AI_BASE_URL', env('NINEROUTER_BASE_URL', 'https://ai.geraikita.com/v1'));
        if (str_contains($baseUrl, 'localhost:20128')) {
            $baseUrl = 'https://ai.geraikita.com/v1';
        }
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Send chat completion request to 9Router gateway.
     *
     * @param array $messages Array of messages [['role' => 'user', 'content' => '...']]
     * @param string $model Model name or 'default'
     * @param array $options Additional options (temperature, max_tokens, etc.)
     * @return array
     * @throws Exception
     */
    public function chatCompletion(array $messages, ?string $model = null, array $options = []): array
    {
        if (empty($this->apiKey)) {
            throw new Exception("AI API Key belum diisi di file .env server cPanel. Silakan buka cPanel File Manager > edit file .env dan isi: AI_API_KEY=gk-...");
        }

        $model = $model ?: config('ai.default_model', 'deepseek-v4.1-flash');
        $endpoint = "{$this->baseUrl}/chat/completions";

        $payload = array_merge([
            'model'  => $model,
            'messages' => $messages,
            'stream' => false,   // 9Router defaults to SSE streaming — force JSON response
        ], $options);

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'x-api-key'     => $this->apiKey,
                'Content-Type'  => 'application/json',
            ])->timeout(45)->post($endpoint, $payload);

            if ($response->successful()) {
                $data = $response->json();
                if ($data === null) {
                    throw new Exception('9Router returned non-JSON body. Check stream:false is set.');
                }
                return $data;
            }

            Log::error('9Router Chat API Error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            throw new Exception("9Router API Error ({$response->status()}): " . $response->body());
        } catch (Exception $e) {
            Log::error('9Router Service Exception: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetch list of available models from 9Router gateway.
     *
     * @return array
     */
    public function getModels(): array
    {
        $endpoint = "{$this->baseUrl}/models";

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
            ])->timeout(10)->get($endpoint);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => true, 'status' => $response->status(), 'message' => $response->body()];
        } catch (Exception $e) {
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }

    /**
     * Check connectivity to 9Router service.
     *
     * @return bool
     */
    public function checkHealth(): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
            ])->timeout(5)->get("{$this->baseUrl}/models");

            return $response->successful();
        } catch (Exception $e) {
            return false;
        }
    }
}
