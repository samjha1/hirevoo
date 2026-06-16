@php
    $assetController = app(\App\Http\Controllers\HirevoAssetController::class);
    $candidateCss = $assetController->candidateCssContents();
@endphp
@if($candidateCss)
    <style id="hirevo-candidate-styles">{!! $candidateCss !!}</style>
@endif
