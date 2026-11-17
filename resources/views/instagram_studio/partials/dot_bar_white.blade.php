{{--
    dot_bar_white: Bottom Brand Component — dark/blue bg version (white text)
    $brand, $slideNum, $totalSlides required.
--}}
@php
    $brandData  = $brand ?? \App\Services\Instagram\InstagramBrandResolver::resolve();
    $websiteStr = $brandData['website_url'] ?? 'www.assyavetclinic.com';
@endphp
<div class="ig-bottom-bar ig-bottom-dark">
    <div class="ig-slide-dots">
        @for($i = 1; $i <= $totalSlides; $i++)
            <span class="ig-dot ig-dot-white {{ $i === $slideNum ? 'active' : '' }}"></span>
        @endfor
    </div>
    <div class="ig-footer-url ig-footer-url-white">{{ $websiteStr }}</div>
</div>
