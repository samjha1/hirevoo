@php
    $assetController = app(\App\Http\Controllers\HirevoAssetController::class);
    $candidateCssVer = $assetController->candidateCssVersion();
    $hasCandidateCss = $assetController->candidateCssContents() !== null;
@endphp
@if($hasCandidateCss)
    <link rel="stylesheet" href="{{ route('assets.hirevo-candidate-css') }}?v={{ $candidateCssVer }}">
@endif
