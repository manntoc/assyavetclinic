{{--
    dot_bar: Bottom Brand Component — light bg version
    Website text from CMS/config, not hardcoded.
    $brand, $slideNum, $totalSlides required.
--}}
@php
    $brandData  = $brand ?? \App\Services\Instagram\InstagramBrandResolver::resolve();
    $websiteStr = $brandData['website_url'] ?? 'www.assyavetclinic.com';
@endphp
<div class="ig-bottom-bar ig-bottom-light">
    <div class="ig-slide-dots">
        @for($i = 1; $i <= $totalSlides; $i++)
            <span class="ig-dot {{ $i === $slideNum ? 'active' : '' }}"></span>
        @endfor
    </div>
    <div class="ig-footer-url">{{ $websiteStr }}</div>
</div>
