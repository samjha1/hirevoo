@php
    $candidateCssPath = public_path('css/hirevo-candidate.css');
    $candidateCssVer = is_file($candidateCssPath) ? (string) filemtime($candidateCssPath) : '1';
@endphp
<link href="{{ asset('css/hirevo-candidate.css') }}?v={{ $candidateCssVer }}" rel="stylesheet">
