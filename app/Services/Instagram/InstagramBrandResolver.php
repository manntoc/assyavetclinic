<?php

namespace App\Services\Instagram;

use App\Models\Admin\SiteImage;
use App\Models\Admin\SiteInfo;
use App\Models\Admin\Social;

/**
 * Resolves clinic brand data from existing CMS tables.
 * Single source of truth for all Instagram layout brand components.
 * Read-only. No new tables.
 */
class InstagramBrandResolver
{
    protected static ?array $cache = null;

    public static function resolve(): array
    {
        if (self::$cache !== null) return self::$cache;

        $siteImage = SiteImage::first();
        $siteInfo  = SiteInfo::first();

        $clinicName = config('app.name', 'Assyavet Clinic');

        // --- Logo: prefer colored, fallback chain ---
        $logoPath      = null;
        $logoWhitePath = null;
        if ($siteImage) {
            $logoPath      = $siteImage->site_colored_logo_image
                          ?: $siteImage->admin_logo_image
                          ?: null;
            $logoWhitePath = $siteImage->site_white_logo_image ?: null;
        }

        // Resolve to public URL (logo files stored at public/uploads/img/general/)
        $logoUrl      = self::resolveLogoUrl($logoPath);
        $logoWhiteUrl = self::resolveLogoUrl($logoWhitePath);

        // Measure actual aspect ratio from disk to avoid stretch
        $logoAr      = self::measureAspectRatio($logoPath);
        $logoWhiteAr = self::measureAspectRatio($logoWhitePath);

        // --- Clinic info ---
        $tagline = $siteInfo?->short_desc ?? 'Care & Compassion Veterinary';
        $phone   = $siteInfo?->phone ?? null;
        $email   = $siteInfo?->email ?? null;

        // Website URL — always use production domain regardless of APP_URL
        // (APP_URL may be local dev URL like assyavet-prod.test)
        $appUrl     = config('app.url', '');
        $rawDomain  = rtrim(preg_replace('/^https?:\/\//', '', $appUrl), '/');
        // If it looks like a local dev URL, fall back to known production domain
        $isLocalDev = str_contains($rawDomain, '.test') || str_contains($rawDomain, 'localhost')
                   || str_contains($rawDomain, '127.0.0.1');
        $websiteUrl = $isLocalDev ? 'www.assyavetclinic.com' : ($rawDomain ?: 'www.assyavetclinic.com');
        // Ensure www. prefix
        if ($websiteUrl && !str_starts_with($websiteUrl, 'www.') && !str_contains($websiteUrl, '://')) {
            $websiteUrl = 'www.' . $websiteUrl;
        }

        // Instagram social link
        $igLink = Social::where('social_media', 'like', '%instagram%')
                        ->where('status', 1)
                        ->value('link');

        self::$cache = [
            'clinic_name'    => $clinicName,
            // Colored logo (for light backgrounds)
            'logo_url'       => $logoUrl,
            'logo_ar'        => $logoAr,         // float, e.g. 2.67 (width/height)
            // White logo (for dark/blue backgrounds)
            'logo_white_url' => $logoWhiteUrl,
            'logo_white_ar'  => $logoWhiteAr,
            // Website text shown in footer (clean, no protocol)
            'website_url'    => $websiteUrl,
            // Tagline / short desc
            'tagline'        => $tagline,
            'phone'          => $phone,
            'email'          => $email,
            'instagram_url'  => $igLink,
        ];

        return self::$cache;
    }

    /**
     * Resolve logo filename → public URL.
     * Tries public/uploads/img/general/ first, then storage/.
     * If not found, searches for a replacement logo with matching color intent.
     */
    private static function resolveLogoUrl(?string $filename): ?string
    {
        if (!$filename) return null;
        if (filter_var($filename, FILTER_VALIDATE_URL)) return $filename;

        $filename = ltrim($filename, '/');

        // Check primary path
        $uploadsPath = public_path('uploads/img/general/' . $filename);
        if (file_exists($uploadsPath)) {
            return asset('uploads/img/general/' . $filename);
        }

        // Check storage symlink
        $storagePath = public_path('storage/' . $filename);
        if (file_exists($storagePath)) {
            return asset('storage/' . $filename);
        }

        // File not found — try to find fallback logo in uploads dir
        $uploadsDir = public_path('uploads/img/general');
        if (is_dir($uploadsDir)) {
            $isWhite = str_contains(strtolower($filename), 'white');
            // Look for logos matching color intent
            $patterns = $isWhite
                ? ['148white', 'assya148white', 'white148']
                : ['148color', 'assya148', 'assya327', 'fixlogo'];

            foreach ($patterns as $pattern) {
                $glob = glob($uploadsDir . '/*' . $pattern . '*.png');
                if ($glob) {
                    $found = basename($glob[0]);
                    return asset('uploads/img/general/' . $found);
                }
            }

            // Last resort: any PNG in uploads dir that looks like a logo
            $glob = glob($uploadsDir . '/*assya*.png') ?: glob($uploadsDir . '/*logo*.png');
            if ($glob) {
                return asset('uploads/img/general/' . basename($glob[0]));
            }
        }

        // Return original URL anyway (might work if file is served differently)
        return asset('uploads/img/general/' . $filename);
    }

    /**
     * Measure actual width/height from disk → return aspect ratio (w/h).
     * Falls back to searching uploads/img/general/ for any *color*.png or *white*.png
     * that shares the same filename pattern, then to known brand AR (2.67).
     */
    private static function measureAspectRatio(?string $filename): ?float
    {
        if (!$filename) return null;
        $filename = ltrim($filename, '/');

        $paths = [
            public_path('uploads/img/general/' . $filename),
            public_path('storage/' . $filename),
        ];

        foreach ($paths as $path) {
            if (!file_exists($path)) continue;
            $size = @getimagesize($path);
            if ($size && $size[1] > 0) {
                return round($size[0] / $size[1], 4);
            }
        }

        // File not found — search uploads dir for a logo with known name patterns
        $uploadsDir = public_path('uploads/img/general');
        if (is_dir($uploadsDir)) {
            // Pattern: any file with "color" or "148" in name (colored logos have AR ~2.67)
            foreach (['148color', '148white', 'assya148', 'fixlogo', 'assya327'] as $pattern) {
                $glob = glob($uploadsDir . '/*' . $pattern . '*.png');
                if ($glob) {
                    $size = @getimagesize($glob[0]);
                    if ($size && $size[1] > 0) return round($size[0] / $size[1], 4);
                }
            }
        }

        // Last resort: return known brand aspect ratio (horizontally wide logo ~2.67:1)
        return 2.67;
    }

    /**
     * Footer website text for brand bottom bar.
     */
    public static function websiteText(): string
    {
        return self::resolve()['website_url'];
    }

    /**
     * Short footer line (legacy compat).
     */
    public static function footerLine(): string
    {
        $b = self::resolve();
        return $b['clinic_name'];
    }
}
