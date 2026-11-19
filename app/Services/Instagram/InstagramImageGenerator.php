<?php

namespace App\Services\Instagram;

use App\Services\NineRouterService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Generate images for Instagram slides via 9Router image generation API.
 * Saves to storage/app/public/instagram/slides/{contentId}/slide_{n}.png
 */
class InstagramImageGenerator
{
    protected NineRouterService $ai;

    // Base URL already set in NineRouterService — we call the raw endpoint
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct(NineRouterService $ai)
    {
        $this->ai      = $ai;
        $this->apiKey  = trim(
            (string) (config('services.ninerouter.api_key') 
            ?: config('ai.providers.ninerouter.api_key') 
            ?: env('AI_API_KEY') 
            ?: env('GERAIKITA_API_KEY') 
            ?: env('GK_API_KEY') 
            ?: env('NINEROUTER_API_KEY', ''))
        );
        $baseUrl       = config('services.ninerouter.base_url') ?: env('AI_BASE_URL', env('NINEROUTER_BASE_URL', 'https://ai.geraikita.com/v1'));
        if (str_contains($baseUrl, 'localhost:20128')) {
            $baseUrl = 'https://ai.geraikita.com/v1';
        }
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Generate an image for a slide and save to public storage.
     * Returns the storage path (relative to public disk) or null on failure.
     */
    public function generateForSlide(
        int $contentId,
        int $slideNumber,
        string $imagePrompt,
        string $size = '1024x1024'
    ): ?string {
        if (empty($this->apiKey)) {
            Log::error('InstagramImageGenerator: AI_API_KEY belum diisi di .env cPanel');
            return null;
        }

        $prompt = $this->sanitizePrompt($imagePrompt);

        try {
            $imageModel = config('ai.image_model', env('AI_IMAGE_MODEL', 'flux-2-flex'));
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'x-api-key'     => $this->apiKey,
                'Content-Type'  => 'application/json',
            ])->timeout(60)->post("{$this->baseUrl}/images/generations", [
                'model'           => $imageModel,
                'prompt'          => $prompt,
                'n'               => 1,
                'size'            => $size,
                'response_format' => 'b64_json',
            ]);

            if (!$response->successful()) {
                Log::warning('InstagramImageGenerator: API error', [
                    'status' => $response->status(),
                    'body'   => substr($response->body(), 0, 300),
                ]);
                return null;
            }

            $data = $response->json();
            $b64  = $data['data'][0]['b64_json'] ?? null;

            if (!$b64) {
                // Try url field if b64_json not present
                $url = $data['data'][0]['url'] ?? null;
                if ($url) return $this->downloadAndStore($contentId, $slideNumber, $url);
                return null;
            }

            return $this->storeB64($contentId, $slideNumber, $b64);

        } catch (\Exception $e) {
            Log::error('InstagramImageGenerator: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate images for all slides in a content record.
     * Returns map of slide_id => storage_path.
     */
    public function generateAllSlides(\App\Models\InstagramContent $content): array
    {
        $results = [];

        foreach ($content->slides as $slide) {
            $prompt = $slide->design_data['image_prompt'] ?? null;
            if (!$prompt) continue;

            $path = $this->generateForSlide($content->id, $slide->slide_number, $prompt);
            if ($path) {
                $slide->update(['image_path' => $path]);
                $results[$slide->id] = $path;
            }
        }

        return $results;
    }

    // -------------------------------------------------------------------------

    /**
     * Sanitize prompt: remove instructions to add text/logo (avoid confusion in image).
     * Add clinic-context suffix for consistent veterinary style.
     */
    private function sanitizePrompt(string $prompt): string
    {
        // Strip any leftover instructions to add text
        $prompt = preg_replace('/\b(add|include|with|showing|display|write|text|logo|caption|headline|title|banner)\b.*?(text|words|label|title|headline|banner)[^.]*\./i', '', $prompt);
        $prompt = trim($prompt);

        // Ensure no text generation instructions
        $prompt .= ', no text, no watermark, no logo, no caption, photorealistic, clean editorial style, veterinary clinic atmosphere, bright and professional lighting';

        return $prompt;
    }

    private function storeB64(int $contentId, int $slideNumber, string $b64): string
    {
        $dir  = "instagram/slides/{$contentId}";
        $file = "slide_{$slideNumber}_" . time() . '.jpg';
        $path = "{$dir}/{$file}";

        Storage::disk('public')->makeDirectory($dir);
        Storage::disk('public')->put($path, base64_decode($b64));

        return $path;
    }

    private function downloadAndStore(int $contentId, int $slideNumber, string $url): ?string
    {
        try {
            $imageData = file_get_contents($url);
            if (!$imageData) return null;

            $dir  = "instagram/slides/{$contentId}";
            $file = "slide_{$slideNumber}_" . time() . '.jpg';
            $path = "{$dir}/{$file}";

            Storage::disk('public')->makeDirectory($dir);
            Storage::disk('public')->put($path, $imageData);

            return $path;
        } catch (\Exception $e) {
            Log::warning('InstagramImageGenerator download failed: ' . $e->getMessage());
            return null;
        }
    }
}
