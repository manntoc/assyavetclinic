{{--
    brand_bar_white: Brand Component — dark/blue background version (white logo)
    Logo loaded from SiteImage CMS white logo. Aspect ratio preserved.
    Falls back to colored logo with CSS filter if white logo not available.
    $brand, $slideNum, $totalSlides required.
--}}
@php
    $brandData  = $brand ?? \App\Services\Instagram\InstagramBrandResolver::resolve();
    // Prefer white logo for dark bg; fall back to colored with invert filter
    $logoSrc    = $brandData['logo_white_url'] ?? $brandData['logo_url'] ?? null;
    $logoAr     = $brandData['logo_white_ar']  ?? $brandData['logo_ar'] ?? 2.67;
    $useFilter  = !$brandData['logo_white_url'] && $brandData['logo_url'];
    $clinicName = $brandData['clinic_name'] ?? 'Assyavet Clinic';
    $websiteStr = $brandData['website_url'] ?? 'www.assyavetclinic.com';
@endphp
<div class="ig-top-bar ig-brand-dark">
    <div class="ig-brand-lockup">
        @if($logoSrc)
            <img
                src="{{ $logoSrc }}"
                alt="{{ $clinicName }}"
                class="ig-brand-logo"
                style="aspect-ratio: {{ $logoAr }}; {{ $useFilter ? 'filter:brightness(0) invert(1);' : '' }}"
                loading="eager"
            >
        @else
            <div class="ig-brand-text-fallback ig-brand-text-white">
                <span class="ig-brand-title">{{ $clinicName }}</span>
            </div>
        @endif
    </div>
    <div class="ig-slide-pill ig-slide-pill-white">{{ $slideNum }}/{{ $totalSlides }}</div>
</div>
