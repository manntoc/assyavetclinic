{{--
    slide_canvas.blade.php
    ═══════════════════════════════════════════════════════════════════════════
    Dynamic Layout Engine — Assyavet Instagram Studio
    ═══════════════════════════════════════════════════════════════════════════

    ARCHITECTURE:
      Canvas
        └─ Content Layer    (headline, body, image — varies per layout)
        └─ Decorative Layer (shapes, doodles, blobs — varies per layout)
        └─ Brand Layer      (logo + website — locked, from CMS, never hardcoded)
              └─ brand_bar  (logo + slide counter)
              └─ dot_bar    (slide dots + website URL)

    LAYOUTS (10 variants):
      1. hook_center       Cover: big headline, centered square photo
      2. navy_full         Dark navy bg, white text, numbered list
      3. white_top_blue    Top white (headline+photo), blue panel below w/ points
      4. card_grid         2×2 info cards, clean white bg
      5. split_reverse     Photo left 40%, text right 60%
      6. blue_gradient     Sky→navy gradient, circle photo center
      7. cta_full          CTA closing slide (last slide always)
      8. editorial         Big number + editorial headline + photo strip
      9. scrapbook         Sticky note / paper feel, doodle accents, playful
     10. typography_hero   Huge text dominant, small supporting image bottom

    BRAND LAYER:
      - Logo from SiteImage CMS (aspect-ratio preserved, never stretched)
      - Website URL from config/SiteInfo (never hardcoded)
      - AI cannot override brand content, only layout/position variant
    ═══════════════════════════════════════════════════════════════════════════
--}}
@php
    use App\Services\Instagram\InstagramBrandResolver;

    // ── Data ────────────────────────────────────────────────────────────────
    $brand       = InstagramBrandResolver::resolve();
    $purpose     = $slide->purpose     ?? 'hook';
    $layout      = $slide->layout      ?? 'hook_center';
    $hasImage    = !empty($slide->image_path) || !empty($slide->rendered_path);
    $imageUrl    = $slide->image_url ?? null;
    $totalSlides = $content->slides->count() ?: 3;
    $slideNum    = $slide->slide_number ?? 1;
    $headline    = $slide->headline ?? '';
    $body        = $slide->body ?? '';

    // ── Brand strings dari DB (TIDAK hardcode) ────────────────────────────
    $clinicName  = $brand['clinic_name'] ?? '';
    $websiteUrl  = $brand['website_url'] ?? '';

    // ── CTA features: ambil dari layanan.jenis di DB (max 3, no hardcode) ─
    static $ctaFeatures = null;
    if ($ctaFeatures === null) {
        try {
            $jenisList = \App\Models\layanan::select('jenis')
                ->distinct()
                ->whereNotNull('jenis')
                ->pluck('jenis')
                ->take(3)
                ->toArray();
            // Icon mapping per jenis layanan
            $iconMap = [
                'vaksin'    => 'bi-shield-check',
                'grooming'  => 'bi-scissors',
                'operasi'   => 'bi-hospital',
                'steril'    => 'bi-scissors',
                'rawat'     => 'bi-house-medical',
                'house'     => 'bi-house-heart',
                'diagnosa'  => 'bi-clipboard2-pulse',
                'jasa'      => 'bi-person-badge',
                'lainnya'   => 'bi-stars',
            ];
            $ctaFeatures = array_map(function($j) use ($iconMap) {
                $lower = strtolower($j);
                $icon  = 'bi-stars'; // default
                foreach ($iconMap as $keyword => $ico) {
                    if (str_contains($lower, $keyword)) { $icon = $ico; break; }
                }
                return ['label' => $j, 'icon' => $icon];
            }, $jenisList);
        } catch (\Throwable $e) {
            $ctaFeatures = []; // graceful fail
        }
    }

    // ── hook_center badge label dari purpose ────────────────────────────────
    $badgeLabel = match($purpose) {
        'hook'      => 'Yuk Kita Bahas!',
        'problem'   => 'Perlu Diketahui!',
        'tips'      => 'Tips Berguna!',
        'education' => 'Info Penting!',
        default     => 'Selengkapnya →',
    };

    // ── Parse body → bullet points ─────────────────────────────────────────
    $rawPoints = array_values(array_filter(
        array_map('trim', preg_split('/[\r\n]+/', $body)),
        fn($l) => strlen($l) > 0
    ));
    if (count($rawPoints) <= 1 && strlen($body) > 0) {
        $rawPoints = array_values(array_filter(
            array_map('trim', preg_split('/(?<=[.!?])\s+/', $body)),
            fn($l) => strlen($l) > 3
        ));
    }
    if (count($rawPoints) === 0) $rawPoints = $body ? [$body] : [''];

    // Bersihkan prefix nomor kaku ("No. 1:", "1.", "1 -", dll) agar tidak redundant dengan nomor badge template
    $points = array_map(function($pt) {
        return preg_replace('/^(no\.?\s*\d+[\s:\.\-]+|\d+[\s:\.\-]+|poin\s*\d+[\s:\.\-]+|[•\-\*]\s*)/i', '', trim($pt));
    }, $rawPoints);

    // ── Normalize layout — handle legacy values ───────────────────────────
    $legacyMap = [
        'hero'        => 'hook_center',
        'text_focus'  => 'white_top_blue',
        'split'       => 'split_reverse',
        'case_study'  => 'navy_full',
        'educational' => 'card_grid',
        'minimal'     => 'typography_hero',
        'magazine'    => 'editorial',
        'scrapbook'   => 'scrapbook',
        'collage'     => 'card_grid',
        'cta'         => 'cta_full',
    ];
    if (isset($legacyMap[$layout])) $layout = $legacyMap[$layout];

    $validLayouts = [
        'hook_center','navy_full','white_top_blue','card_grid',
        'split_reverse','blue_gradient','cta_full','editorial',
        'scrapbook','typography_hero',
    ];
    if (!in_array($layout, $validLayouts)) {
        $layout = ($purpose === 'cta') ? 'cta_full' : 'hook_center';
    }
@endphp

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- LAYOUT 1: hook_center — Cover slide: big headline + hero photo              --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@if($layout === 'hook_center')
<div class="ig-canvas ig-lyt-hook-center">
    {{-- Decorative blobs --}}
    <div class="ig-deco-blob ig-blob-tl"></div>
    <div class="ig-deco-blob ig-blob-br"></div>

    {{-- Brand Layer: Top --}}
    @include('instagram_studio.partials.brand_bar', compact('brand','slideNum','totalSlides'))

    {{-- Content Layer --}}
    <div class="ig-hc-body">
        <div class="ig-hc-header-zone">
            <div class="ig-hc-badge"><i class="bi bi-sparkles me-1"></i>{{ $badgeLabel }}</div>
            <h1 class="ig-hc-headline">{{ $headline }}</h1>
        </div>
        <div class="ig-hc-photo-card">
            @if($hasImage)
                <img src="{{ $imageUrl }}" alt="" class="ig-photo-fill">
            @else
                @include('instagram_studio.partials.photo_placeholder')
            @endif
            @if(!empty($body))
                <div class="ig-hc-photo-caption">
                    <span>{{ Str::limit($body, 110) }}</span>
                </div>
            @endif
        </div>
        <div class="ig-hc-footer-hint">
            <span class="ig-hc-swipe-pill">Geser untuk info lengkap <i class="bi bi-arrow-right ms-1"></i></span>
        </div>
    </div>

    {{-- Brand Layer: Bottom --}}
    @include('instagram_studio.partials.dot_bar', compact('brand','slideNum','totalSlides'))
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- LAYOUT 2: navy_full — Navy full-bleed, white text, clinical card points     --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@elseif($layout === 'navy_full')
<div class="ig-canvas ig-lyt-navy-full">
    {{-- Decorative: subtle circle rings --}}
    <div class="ig-deco-ring ig-ring-tr"></div>
    <div class="ig-deco-ring ig-ring-bl"></div>

    {{-- Brand Layer: Top (white logo) --}}
    @include('instagram_studio.partials.brand_bar_white', compact('brand','slideNum','totalSlides'))

    {{-- Content Layer --}}
    <div class="ig-nf-body">
        <div class="ig-nf-header">
            <div class="ig-nf-tag">
                <i class="bi bi-shield-check me-1"></i>{{ $purpose === 'problem' ? 'Gejala & Penyebab' : ($purpose === 'tips' ? 'Tips Kesehatan' : 'Info Medis') }}
            </div>
            <h2 class="ig-nf-headline">{{ $headline }}</h2>
        </div>

        @if($hasImage)
            <div class="ig-nf-photo-card">
                <img src="{{ $imageUrl }}" alt="" class="ig-photo-fill">
            </div>
        @endif

        <div class="ig-nf-points">
            @foreach(array_slice($points, 0, 3) as $i => $pt)
                @php
                    $parts = explode(':', $pt, 2);
                    $hasKey = count($parts) === 2 && strlen($parts[0]) <= 35;
                @endphp
                <div class="ig-nf-card">
                    <div class="ig-nf-badge">{{ $i + 1 }}</div>
                    <div class="ig-nf-card-content">
                        @if($hasKey)
                            <strong class="ig-nf-key">{{ trim($parts[0]) }}:</strong>
                            <span class="ig-nf-val">{{ trim($parts[1]) }}</span>
                        @else
                            <span class="ig-nf-val">{{ $pt }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Brand Layer: Bottom (white) --}}
    @include('instagram_studio.partials.dot_bar_white', compact('brand','slideNum','totalSlides'))
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- LAYOUT 3: white_top_blue — Top white hero, structured blue cards            --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@elseif($layout === 'white_top_blue')
<div class="ig-canvas ig-lyt-white-top-blue">
    {{-- Brand Layer: Top --}}
    @include('instagram_studio.partials.brand_bar', compact('brand','slideNum','totalSlides'))

    {{-- Content Layer --}}
    <div class="ig-wtb-body">
        <div class="ig-wtb-hero">
            <div class="ig-wtb-hero-text">
                <div class="ig-wtb-tag">Edukasi Anabul</div>
                <h2 class="ig-wtb-headline">{{ $headline }}</h2>
            </div>
            @if($hasImage)
                <div class="ig-wtb-photo-thumb">
                    <img src="{{ $imageUrl }}" alt="" class="ig-photo-fill">
                </div>
            @endif
        </div>

        <div class="ig-wtb-cards">
            @foreach(array_slice($points, 0, 3) as $i => $pt)
                @php
                    $parts = explode(':', $pt, 2);
                    $hasKey = count($parts) === 2 && strlen($parts[0]) <= 35;
                @endphp
                <div class="ig-wtb-card">
                    <div class="ig-wtb-icon"><i class="bi bi-check-circle-fill"></i></div>
                    <div class="ig-wtb-card-text">
                        @if($hasKey)
                            <strong class="ig-wtb-key">{{ trim($parts[0]) }}</strong>
                            <div class="ig-wtb-val">{{ trim($parts[1]) }}</div>
                        @else
                            <div class="ig-wtb-val">{{ $pt }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Brand Layer: Bottom --}}
    @include('instagram_studio.partials.dot_bar', compact('brand','slideNum','totalSlides'))
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- LAYOUT 4: card_grid — 2×2 info cards, clean white bg                       --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@elseif($layout === 'card_grid')
<div class="ig-canvas ig-lyt-card-grid">
    {{-- Decorative: light bg pattern --}}
    <div class="ig-deco-dots-bg"></div>

    {{-- Brand Layer: Top --}}
    @include('instagram_studio.partials.brand_bar', compact('brand','slideNum','totalSlides'))

    {{-- Content Layer --}}
    <div class="ig-cg-body">
        <div class="ig-cg-headline">{{ $headline }}</div>

        @php
            $cardPoints = array_slice($points, 0, min(count($points), 4));
            $cardColors = ['#1a58ad','#009beb','#0078c8','#3d9fd3'];
            $cardIcons  = ['bi-shield-check','bi-clipboard2-pulse','bi-emoji-smile','bi-award'];
        @endphp
        <div class="ig-cg-grid">
            @foreach($cardPoints as $i => $pt)
                @php
                    $parts = explode(':', $pt, 2);
                    $hasKey = count($parts) === 2 && strlen($parts[0]) <= 30;
                @endphp
                <div class="ig-cg-card" style="--card-accent: {{ $cardColors[$i % 4] }};">
                    <div class="ig-cg-card-icon" style="color: {{ $cardColors[$i % 4] }};">
                        <i class="bi {{ $cardIcons[$i % 4] }}"></i>
                    </div>
                    <div class="ig-cg-card-text">
                        @if($hasKey)
                            <strong style="color:var(--ig-navy);display:block;margin-bottom:2px;font-size:0.75rem;">{{ trim($parts[0]) }}</strong>
                            <span>{{ trim($parts[1]) }}</span>
                        @else
                            {{ $pt }}
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($hasImage)
            <div class="ig-cg-photo-strip">
                <img src="{{ $imageUrl }}" alt="" class="ig-photo-fill">
            </div>
        @endif
    </div>

    {{-- Brand Layer: Bottom --}}
    @include('instagram_studio.partials.dot_bar', compact('brand','slideNum','totalSlides'))
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- LAYOUT 5: split_reverse — Photo left 44%, text right 56%                   --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@elseif($layout === 'split_reverse')
<div class="ig-canvas ig-lyt-split">
    {{-- Brand Layer: Top --}}
    @include('instagram_studio.partials.brand_bar', compact('brand','slideNum','totalSlides'))

    {{-- Content Layer: Split --}}
    <div class="ig-sr-content">
        <div class="ig-sr-photo-wrap">
            @if($hasImage)
                <img src="{{ $imageUrl }}" alt="" class="ig-photo-fill">
            @else
                @include('instagram_studio.partials.photo_placeholder')
            @endif
        </div>
        <div class="ig-sr-text-zone">
            <div class="ig-sr-tag">{{ strtoupper($purpose === 'problem' ? 'Gejala' : ($purpose === 'tips' ? 'Tips' : 'Edukasi')) }}</div>
            <h2 class="ig-sr-headline">{{ $headline }}</h2>
            <div class="ig-sr-points">
                @foreach(array_slice($points, 0, 3) as $pt)
                    @php
                        $parts = explode(':', $pt, 2);
                        $hasKey = count($parts) === 2 && strlen($parts[0]) <= 35;
                    @endphp
                    <div class="ig-sr-point-card">
                        <i class="bi bi-check2-circle ig-sr-icon"></i>
                        <div class="ig-sr-pt-desc">
                            @if($hasKey)
                                <strong>{{ trim($parts[0]) }}:</strong> {{ trim($parts[1]) }}
                            @else
                                {{ $pt }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Brand Layer: Bottom --}}
    @include('instagram_studio.partials.dot_bar', compact('brand','slideNum','totalSlides'))
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- LAYOUT 6: blue_gradient — Sky→navy gradient, circle photo center           --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@elseif($layout === 'blue_gradient')
<div class="ig-canvas ig-lyt-blue-gradient">
    {{-- Decorative: soft light rings --}}
    <div class="ig-deco-glow ig-glow-top"></div>

    {{-- Brand Layer: Top (white) --}}
    @include('instagram_studio.partials.brand_bar_white', compact('brand','slideNum','totalSlides'))

    {{-- Content Layer --}}
    <div class="ig-bg-body">
        <div class="ig-bg-headline">{{ $headline }}</div>

        @if($hasImage)
            <div class="ig-bg-photo-circle">
                <img src="{{ $imageUrl }}" alt="" class="ig-photo-fill">
            </div>
        @else
            <div class="ig-bg-icon-circle">
                <i class="bi bi-clipboard2-pulse"></i>
            </div>
        @endif

        <div class="ig-bg-points">
            @foreach(array_slice($points, 0, 3) as $pt)
                <div class="ig-bg-point">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ $pt }}
                </div>
            @endforeach
        </div>
    </div>

    {{-- Brand Layer: Bottom (white) --}}
    @include('instagram_studio.partials.dot_bar_white', compact('brand','slideNum','totalSlides'))
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- LAYOUT 7: editorial — Big slide number + editorial headline + photo strip   --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@elseif($layout === 'editorial')
<div class="ig-canvas ig-lyt-editorial">
    {{-- Brand Layer: Top --}}
    @include('instagram_studio.partials.brand_bar', compact('brand','slideNum','totalSlides'))

    {{-- Content Layer --}}
    <div class="ig-ed-body">
        <div class="ig-ed-number">0{{ $slideNum }}</div>
        <div class="ig-ed-rule"></div>
        <div class="ig-ed-headline">{{ $headline }}</div>

        @if($hasImage)
            <div class="ig-ed-photo">
                <img src="{{ $imageUrl }}" alt="" class="ig-photo-fill">
            </div>
        @endif

        <div class="ig-ed-copy">{{ Str::limit($body, 120) }}</div>

        @if(count($points) > 1)
            <div class="ig-ed-bullets">
                @foreach(array_slice($points, 0, 3) as $pt)
                    <div class="ig-ed-bullet"><span class="ig-ed-dash">—</span> {{ $pt }}</div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Brand Layer: Bottom --}}
    @include('instagram_studio.partials.dot_bar', compact('brand','slideNum','totalSlides'))
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- LAYOUT 8: scrapbook — Sticky note / paper texture, playful, doodle accents --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@elseif($layout === 'scrapbook')
<div class="ig-canvas ig-lyt-scrapbook">
    {{-- Decorative: paper texture blobs + doodle lines --}}
    <div class="ig-deco-tape ig-tape-tl"></div>
    <div class="ig-deco-tape ig-tape-tr"></div>
    <div class="ig-deco-paw ig-paw-1">🐾</div>
    <div class="ig-deco-paw ig-paw-2">🐾</div>

    {{-- Brand Layer: Top --}}
    @include('instagram_studio.partials.brand_bar', compact('brand','slideNum','totalSlides'))

    {{-- Content Layer: Main sticky note --}}
    <div class="ig-sb-body">
        <div class="ig-sb-note">
            <div class="ig-sb-note-headline">{{ $headline }}</div>
            <div class="ig-sb-rule"></div>
            @foreach(array_slice($points, 0, 3) as $i => $pt)
                <div class="ig-sb-note-point">
                    <span class="ig-sb-bullet">{{ ['🐶','🐱','🐰'][$i % 3] }}</span>
                    <span class="ig-sb-pt-text">{{ $pt }}</span>
                </div>
            @endforeach
        </div>

        @if($hasImage)
            <div class="ig-sb-photo">
                <img src="{{ $imageUrl }}" alt="" class="ig-photo-fill">
            </div>
        @else
            <div class="ig-sb-photo-placeholder">
                <span class="ig-sb-ph-emoji">📸</span>
            </div>
        @endif
    </div>

    {{-- Brand Layer: Bottom --}}
    @include('instagram_studio.partials.dot_bar', compact('brand','slideNum','totalSlides'))
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- LAYOUT 9: typography_hero — Huge text dominant, small image bottom-right   --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@elseif($layout === 'typography_hero')
<div class="ig-canvas ig-lyt-typo-hero">
    {{-- Decorative: large faint initial letter --}}
    <div class="ig-deco-bigchar">{{ mb_strtoupper(mb_substr($headline, 0, 1)) }}</div>

    {{-- Brand Layer: Top --}}
    @include('instagram_studio.partials.brand_bar', compact('brand','slideNum','totalSlides'))

    {{-- Content Layer --}}
    <div class="ig-th-body">
        <div class="ig-th-headline">{{ $headline }}</div>
        <div class="ig-th-rule"></div>
        <div class="ig-th-body-text">{{ Str::limit($body, 150) }}</div>

        @if(count($points) > 1)
            <div class="ig-th-points">
                @foreach(array_slice($points, 0, 3) as $pt)
                    <div class="ig-th-point">→ {{ $pt }}</div>
                @endforeach
            </div>
        @endif

        @if($hasImage)
            <div class="ig-th-photo-corner">
                <img src="{{ $imageUrl }}" alt="" class="ig-photo-fill">
            </div>
        @endif
    </div>

    {{-- Brand Layer: Bottom --}}
    @include('instagram_studio.partials.dot_bar', compact('brand','slideNum','totalSlides'))
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- LAYOUT 10: cta_full — CTA closing slide (Penutup & Reservasi)                --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@else{{-- cta_full --}}
<div class="ig-canvas ig-lyt-cta">
    {{-- Decorative: paw prints --}}
    <div class="ig-deco-paw ig-paw-cta-1">🐾</div>
    <div class="ig-deco-paw ig-paw-cta-2">🐾</div>

    {{-- Brand Layer: Top --}}
    @include('instagram_studio.partials.brand_bar', compact('brand','slideNum','totalSlides'))

    {{-- Content Layer --}}
    <div class="ig-cf-body">
        <div class="ig-cf-tag"><i class="bi bi-heart-pulse-fill me-1"></i>{{ $brand['tagline'] ?? 'Klinik Hewan Terpercaya' }}</div>
        <h2 class="ig-cf-headline">{{ !empty($headline) ? $headline : 'Yuk, Jadwalkan Perawatan Anabul Hari Ini!' }}</h2>

        @if($hasImage)
            <div class="ig-cf-photo-card">
                <img src="{{ $imageUrl }}" alt="" class="ig-photo-fill">
            </div>
        @endif

        <div class="ig-cf-clinic-box">
            <div class="ig-cf-box-left">
                <i class="bi bi-hospital-fill"></i>
            </div>
            <div class="ig-cf-box-right">
                <div class="ig-cf-box-name">{{ $brand['clinic_name'] }}</div>
                <div class="ig-cf-box-sub">{{ !empty($body) ? Str::limit($body, 85) : 'Kesehatan dan kenyamanan anabul adalah prioritas utama kami.' }}</div>
            </div>
        </div>

        <div class="ig-cf-chips">
            @if($ctaFeatures)
                @foreach(array_slice($ctaFeatures, 0, 3) as $feat)
                    <div class="ig-cf-chip">
                        <i class="bi {{ $feat['icon'] }}"></i>
                        <span>{{ $feat['label'] }}</span>
                    </div>
                @endforeach
            @else
                <div class="ig-cf-chip"><i class="bi bi-clipboard2-pulse"></i><span>Konsultasi</span></div>
                <div class="ig-cf-chip"><i class="bi bi-shield-check"></i><span>Vaksinasi</span></div>
                <div class="ig-cf-chip"><i class="bi bi-scissors"></i><span>Grooming</span></div>
            @endif
        </div>
    </div>

    {{-- Brand Layer: Bottom --}}
    @include('instagram_studio.partials.dot_bar', compact('brand','slideNum','totalSlides'))
</div>
@endif
