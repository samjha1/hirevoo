@php
    $dashCssPath = public_path('css/hirevo-candidate-dashboard.css');
    $dashCss = is_file($dashCssPath) ? file_get_contents($dashCssPath) : null;
@endphp
@if($dashCss)
<style id="hirevo-candidate-dashboard-styles">{!! $dashCss !!}</style>
@endif
