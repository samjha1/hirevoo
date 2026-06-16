@php
    $candidateCssPath = resource_path('css/hirevo-candidate.css');
    $publicCssPath = public_path('css/hirevo-candidate.css');
    $hasPublicCss = is_file($publicCssPath);
    $candidateCss = is_file($candidateCssPath) ? file_get_contents($candidateCssPath) : null;

    if ($candidateCss === null && $hasPublicCss) {
        $candidateCss = file_get_contents($publicCssPath);
    }

    $candidateCssVer = $hasPublicCss
        ? (string) filemtime($publicCssPath)
        : (is_file($candidateCssPath) ? (string) filemtime($candidateCssPath) : '1');
@endphp
@if($hasPublicCss)
    <link href="{{ asset('css/hirevo-candidate.css') }}?v={{ $candidateCssVer }}" rel="stylesheet">
@elseif($candidateCss)
    <style id="hirevo-candidate-styles">{!! $candidateCss !!}</style>
@endif
