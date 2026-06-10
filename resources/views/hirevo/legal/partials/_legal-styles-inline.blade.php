@php
    $legalCssPath = public_path('css/hirevo-legal-terms.css');
    $legalCss = is_file($legalCssPath) ? file_get_contents($legalCssPath) : null;
@endphp
@if($legalCss)
<style id="hirevo-legal-styles">{!! $legalCss !!}</style>
@else
{{-- Fallback if public_path differs on server — keeps grid + nav styled on live --}}
<style id="hirevo-legal-styles-fallback">
.hirevo-legal-apna-page .hirevo-footer { margin-top: 0; }
.hirevo-legal-apna { background: #fff; min-height: calc(100vh - 72px); padding: 24px 0 64px; }
.hirevo-legal-apna > .container-fluid.custom-container { max-width: 90%; margin-left: auto; margin-right: auto; }
.hirevo-legal-apna__layout { display: grid; grid-template-columns: 260px minmax(0, 1fr); gap: 32px; align-items: start; }
.hirevo-legal-apna__sidebar { position: sticky; top: 88px; }
.hirevo-legal-apna__nav-heading { font-size: 0.75rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #80868b; margin: 0 0 12px; }
.hirevo-legal-apna__nav { list-style: none; padding-left: 0; margin: 0; }
.hirevo-legal-apna__nav li { margin-bottom: 10px; }
.hirevo-legal-apna__nav a { display: block; color: #3c4043; text-decoration: none; font-size: 0.9375rem; line-height: 1.45; font-weight: 500; padding: 4px 0 4px 12px; border-left: 2px solid transparent; }
.hirevo-legal-apna__nav a:hover, .hirevo-legal-apna__nav a.is-active { color: #1aa399; }
.hirevo-legal-apna__nav a.is-active { font-weight: 700; border-left-color: #1aa399; }
.hirevo-legal-apna__page-title { font-size: 1.75rem; font-weight: 700; color: #202124; margin: 0 0 24px; line-height: 1.25; }
.hirevo-legal-apna__doc-body { font-size: 0.9375rem; line-height: 1.7; color: #3c4043; }
.hirevo-legal-apna__updated { font-size: 0.8125rem; color: #80868b; margin-bottom: 16px !important; }
@media (max-width: 991.98px) { .hirevo-legal-apna__layout { grid-template-columns: 1fr; gap: 0; } }
</style>
@endif
