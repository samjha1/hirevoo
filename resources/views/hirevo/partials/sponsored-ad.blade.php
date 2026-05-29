@if(!empty($ad))
    @php
        $variant = $variant ?? 'default';
        $hasImage = !empty($ad['image_url']);
        $bodyText = trim((string) ($ad['body'] ?? ''));
    @endphp

    @if($variant === 'home')
        @php $tags = $ad['tags'] ?? []; @endphp
        {{-- Homepage: Naukri-style horizontal promo banner --}}
        <div class="hv-sp-banner hv2-reveal hv2-revealed" role="complementary" aria-label="Sponsored promotion">
            <span class="hv-sp-banner__ribbon">Live on Hirevo</span>
            <a href="{{ $ad['click_url'] }}"
               class="hv-sp-banner__card"
               target="_blank"
               rel="noopener sponsored"
               data-sponsored-ad>
                <div class="hv-sp-banner__brand" aria-hidden="true">
                    @if($hasImage)
                        <img src="{{ $ad['image_url'] }}" alt="" class="hv-sp-banner__logo" loading="lazy" decoding="async">
                    @else
                        <span class="hv-sp-banner__logo hv-sp-banner__logo--icon"><i class="uil uil-briefcase"></i></span>
                    @endif
                </div>
                <div class="hv-sp-banner__content">
                    <p class="hv-sp-banner__eyebrow">Sponsored</p>
                    <h3 class="hv-sp-banner__title">{{ $ad['headline'] }}</h3>
                    @if($bodyText !== '')
                        <p class="hv-sp-banner__desc">{{ $bodyText }}</p>
                    @endif
                    @if(count($tags) > 0)
                        <div class="hv-sp-banner__pills" aria-label="Topics">
                            @foreach($tags as $tag)
                                <span class="hv-sp-banner__pill">{{ $tag }}<i class="uil uil-angle-right-b" aria-hidden="true"></i></span>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="hv-sp-banner__action">
                    <span class="hv-sp-banner__btn">{{ $ad['cta_label'] }}</span>
                </div>
            </a>
            <img src="{{ $ad['impression_url'] }}" width="1" height="1" alt="" class="visually-hidden" aria-hidden="true">
        </div>
    @else
        @php
            $wrapClass = match ($variant) {
                'sidebar' => 'hv-sponsored-wrap--sidebar',
                'dashboard' => 'hv-sponsored-wrap--dashboard',
                'inline' => 'hv-sponsored-wrap--inline',
                'strip' => 'hv-sponsored-wrap--strip',
                default => '',
            };
            $cardClass = match ($variant) {
                'sidebar' => 'hv-sponsored--sidebar',
                'inline' => 'hv-sponsored--inline',
                'strip' => 'hv-sponsored--strip',
                default => '',
            };
        @endphp
        <div class="hv-sponsored-wrap {{ $wrapClass }}" role="complementary" aria-label="Sponsored">
            <a href="{{ $ad['click_url'] }}"
               class="hv-sponsored {{ $cardClass }}"
               target="_blank"
               rel="noopener sponsored"
               data-sponsored-ad>
                @if($hasImage)
                    <div class="hv-sponsored__media">
                        <img src="{{ $ad['image_url'] }}" alt="" loading="lazy" decoding="async">
                    </div>
                @endif
                <div class="hv-sponsored__body">
                    <span class="hv-sponsored__label">Sponsored</span>
                    <h3 class="hv-sponsored__headline">{{ $ad['headline'] }}</h3>
                    @if($bodyText !== '')
                        <p class="hv-sponsored__text">{{ $bodyText }}</p>
                    @endif
                    <span class="hv-sponsored__cta">
                        {{ $ad['cta_label'] }}
                        <i class="uil uil-arrow-right" aria-hidden="true"></i>
                    </span>
                </div>
            </a>
            <img src="{{ $ad['impression_url'] }}" width="1" height="1" alt="" class="visually-hidden" aria-hidden="true">
        </div>
    @endif
@endif
