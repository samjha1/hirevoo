@php
    $seoTitle = trim($__env->yieldContent('title'));
    if ($seoTitle === '' || $seoTitle === 'Home') {
        $resolvedTitle = $seo['title'] ?? config('seo.site_name');
    } else {
        $resolvedTitle = $seoTitle;
    }
    $metaDescription = trim($__env->yieldContent('meta_description'));
    if ($metaDescription === '') {
        $metaDescription = trim($__env->yieldContent('description'));
    }
    if ($metaDescription === '') {
        $metaDescription = $seo['description'] ?? config('seo.default_description');
    }
    $metaRobots = trim($__env->yieldContent('robots'));
    if ($metaRobots === '') {
        $metaRobots = $seo['robots'] ?? 'index, follow';
    }
    $canonical = trim($__env->yieldContent('canonical'));
    if ($canonical === '') {
        $canonical = $seo['canonical'] ?? url()->current();
    }
    $ogTitle = trim($__env->yieldContent('og_title'));
    if ($ogTitle === '') {
        $ogTitle = $resolvedTitle . ' | ' . config('seo.title_suffix');
    }
    $ogDescription = trim($__env->yieldContent('og_description'));
    if ($ogDescription === '') {
        $ogDescription = $metaDescription;
    }
    $ogImage = trim($__env->yieldContent('og_image'));
    if ($ogImage === '') {
        $ogImage = $seo['og_image'] ?? asset(config('seo.default_og_image'));
    }
    $ogType = trim($__env->yieldContent('og_type')) ?: ($seo['og_type'] ?? 'website');
    $ogUrl = trim($__env->yieldContent('og_url')) ?: ($seo['og_url'] ?? $canonical);
    $fullTitle = $resolvedTitle . ' | ' . config('seo.title_suffix');
    $twitterHandle = ltrim((string) config('seo.twitter_handle'), '@');
    $extraStructured = $__env->yieldContent('structured_data');
@endphp
<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
<meta name="robots" content="{{ $metaRobots }}">
<link rel="canonical" href="{{ $canonical }}">
<meta property="og:site_name" content="{{ config('seo.site_name') }}">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:url" content="{{ $ogUrl }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $ogDescription }}">
<meta name="twitter:image" content="{{ $ogImage }}">
@if($twitterHandle !== '')
<meta name="twitter:site" content="@{{ $twitterHandle }}">
@endif
@foreach($seo['structured_data'] ?? [] as $schema)
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endforeach
@if($extraStructured !== '')
{!! $extraStructured !!}
@endif
