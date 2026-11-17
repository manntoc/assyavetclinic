{{--
    brand_bar: Brand Component — light background version (dark logo)
    Logo loaded from SiteImage CMS. Aspect ratio preserved via object-fit:contain.
    Website text from brand config.
    $brand = optional passed brand array; falls back to resolver.
    $slideNum, $totalSlides required.
--}}
@php
    $brandData  = $brand ?? \App\Services\Instagram\InstagramBrandResolver::resolve();
    $logoSrc    = $brandData['logo_url'] ?? null;
    $logoAr     = $brandData['logo_ar'] ?? 2.67;   // fallback AR if not measured
    $clinicName = $brandData['clinic_name'] ?? 'Assyavet Clinic';
    $websiteStr = $brandData['website_url'] ?? 'www.assyavetclinic.com';
    // Logo container: height fixed (7% canvas), width = height × AR
    // In CSS we use height:100%;aspect-ratio:logoAr so no stretch occurs
@endphp
<div class="ig-top-bar ig-brand-light">
    <div class="ig-brand-lockup">
        @if($logoSrc)
            <img
                src="{{ $logoSrc }}"
                alt="{{ $clinicName }}"
                class="ig-brand-logo"
                style="aspect-ratio: {{ $logoAr }};"
                loading="eager"
            >
        @else
            {{-- Fallback: text logo dari DB clinic_name --}}
            <div class="ig-brand-text-fallback">
                <span class="ig-brand-title">{{ $clinicName }}</span>
            </div>
        @endif
    </div>
    <div class="ig-slide-pill">{{ $slideNum }}/{{ $totalSlides }}</div>
</div>
