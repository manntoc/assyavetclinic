<?php

namespace App\Services\Instagram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Instagram Graph API publisher.
 * Supports single image and carousel (multi-slide) posts.
 *
 * Flow carousel:
 *   1. Upload tiap gambar → dapat item_container_id per slide
 *   2. Buat carousel container → dapat carousel_container_id
 *   3. Publish carousel → dapat ig_post_id
 *
 * Flow single image:
 *   1. Upload gambar → dapat container_id
 *   2. Publish → dapat ig_post_id
 */
class InstagramPublisher
{
    private string $accessToken;
    private string $accountId;
    private string $baseUrl;

    public function __construct()
    {
        $this->accessToken = config('services.instagram.access_token', '');
        $this->accountId   = config('services.instagram.account_id', '');
        $version           = config('services.instagram.graph_version', 'v21.0');
        $this->baseUrl     = "https://graph.facebook.com/{$version}";
    }

    /**
     * Cek apakah credentials sudah dikonfigurasi.
     */
    public function isConfigured(): bool
    {
        return !empty($this->accessToken) && !empty($this->accountId);
    }

    /**
     * Publish konten ke Instagram.
     *
     * @param  array  $imageUrls  URL publik gambar setiap slide (harus accessible dari internet)
     * @param  string $caption    Caption post + hashtags
     * @return array{success: bool, status: string, external_post_id: ?string, error_message: ?string}
     */
    public function publish(array $imageUrls, string $caption): array
    {
        if (!$this->isConfigured()) {
            return $this->fail('not_configured',
                'Instagram belum dikonfigurasi. Isi INSTAGRAM_ACCESS_TOKEN dan INSTAGRAM_ACCOUNT_ID di .env');
        }

        // Filter hanya URL yang valid
        $imageUrls = array_values(array_filter($imageUrls, fn($u) => filter_var($u, FILTER_VALIDATE_URL)));

        if (empty($imageUrls)) {
            return $this->fail('no_images', 'Tidak ada gambar valid untuk dipublish. Generate gambar terlebih dahulu.');
        }

        try {
            if (count($imageUrls) === 1) {
                return $this->publishSingle($imageUrls[0], $caption);
            }
            return $this->publishCarousel($imageUrls, $caption);
        } catch (\Throwable $e) {
            Log::error('InstagramPublisher: ' . $e->getMessage());
            return $this->fail('exception', $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Single image post
    // -------------------------------------------------------------------------

    private function publishSingle(string $imageUrl, string $caption): array
    {
        // Step 1: buat media container
        $res = $this->post("/{$this->accountId}/media", [
            'image_url'    => $imageUrl,
            'caption'      => $caption,
            'access_token' => $this->accessToken,
        ]);

        if (!isset($res['id'])) {
            return $this->fail('container_failed', $this->extractError($res));
        }

        $containerId = $res['id'];

        // Step 2: tunggu container selesai diproses
        $this->waitForContainer($containerId);

        // Step 3: publish
        return $this->publishContainer($containerId);
    }

    // -------------------------------------------------------------------------
    // Carousel post (2–10 slides)
    // -------------------------------------------------------------------------

    private function publishCarousel(array $imageUrls, string $caption): array
    {
        // Step 1: upload tiap gambar sebagai item container
        $itemIds = [];
        foreach ($imageUrls as $i => $url) {
            $res = $this->post("/{$this->accountId}/media", [
                'image_url'    => $url,
                'is_carousel_item' => true,
                'access_token' => $this->accessToken,
            ]);

            if (!isset($res['id'])) {
                return $this->fail('item_upload_failed',
                    "Gagal upload slide " . ($i + 1) . ": " . $this->extractError($res));
            }

            $itemIds[] = $res['id'];
        }

        // Step 2: buat carousel container
        $res = $this->post("/{$this->accountId}/media", [
            'media_type'   => 'CAROUSEL',
            'children'     => implode(',', $itemIds),
            'caption'      => $caption,
            'access_token' => $this->accessToken,
        ]);

        if (!isset($res['id'])) {
            return $this->fail('carousel_container_failed', $this->extractError($res));
        }

        $carouselId = $res['id'];

        // Step 3: tunggu proses
        $this->waitForContainer($carouselId);

        // Step 4: publish
        return $this->publishContainer($carouselId);
    }

    // -------------------------------------------------------------------------
    // Publish container (single atau carousel)
    // -------------------------------------------------------------------------

    private function publishContainer(string $containerId): array
    {
        $res = $this->post("/{$this->accountId}/media_publish", [
            'creation_id'  => $containerId,
            'access_token' => $this->accessToken,
        ]);

        if (!isset($res['id'])) {
            return $this->fail('publish_failed', $this->extractError($res));
        }

        $postId = $res['id'];
        Log::info("Instagram published: post_id={$postId}");

        return [
            'success'          => true,
            'status'           => 'published',
            'external_post_id' => $postId,
            'error_message'    => null,
        ];
    }

    // -------------------------------------------------------------------------
    // Tunggu container selesai diproses Meta (polling max 30 detik)
    // -------------------------------------------------------------------------

    private function waitForContainer(string $containerId, int $maxWait = 30): void
    {
        $waited = 0;
        while ($waited < $maxWait) {
            sleep(3);
            $waited += 3;

            $res = $this->get("/{$containerId}", [
                'fields'       => 'status_code',
                'access_token' => $this->accessToken,
            ]);

            $status = $res['status_code'] ?? '';

            if ($status === 'FINISHED') return;
            if ($status === 'ERROR' || $status === 'EXPIRED') {
                throw new \RuntimeException("Container {$containerId} status: {$status}");
            }
            // IN_PROGRESS atau belum ada status → lanjut polling
        }
    }

    // -------------------------------------------------------------------------
    // Refresh long-lived token (call ini sebelum 60 hari)
    // -------------------------------------------------------------------------

    public function refreshToken(): array
    {
        if (!$this->isConfigured()) {
            return $this->fail('not_configured', 'Credentials belum diisi.');
        }

        $appSecret = config('services.instagram.app_secret');
        if (!$appSecret) {
            return $this->fail('no_app_secret', 'INSTAGRAM_APP_SECRET belum diisi di .env');
        }

        $res = Http::get('https://graph.facebook.com/oauth/access_token', [
            'grant_type'        => 'fb_exchange_token',
            'client_id'         => config('services.instagram.app_id'),
            'client_secret'     => $appSecret,
            'fb_exchange_token' => $this->accessToken,
        ])->json();

        if (!isset($res['access_token'])) {
            return $this->fail('refresh_failed', $this->extractError($res));
        }

        return [
            'success'      => true,
            'access_token' => $res['access_token'],
            'expires_in'   => $res['expires_in'] ?? null, // detik
        ];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function post(string $endpoint, array $data): array
    {
        $res = Http::timeout(30)->post($this->baseUrl . $endpoint, $data);
        Log::debug("IG POST {$endpoint}: " . $res->body());
        return $res->json() ?? [];
    }

    private function get(string $endpoint, array $params = []): array
    {
        $res = Http::timeout(15)->get($this->baseUrl . $endpoint, $params);
        return $res->json() ?? [];
    }

    private function extractError(array $res): string
    {
        return $res['error']['message']
            ?? $res['error']['error_user_msg']
            ?? json_encode($res);
    }

    private function fail(string $status, string $message): array
    {
        Log::warning("InstagramPublisher [{$status}]: {$message}");
        return [
            'success'          => false,
            'status'           => $status,
            'external_post_id' => null,
            'error_message'    => $message,
        ];
    }
}
