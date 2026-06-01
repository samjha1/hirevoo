@php
    $perPage = $perPage ?? 20;
    $currentPage = $paginator->currentPage();
    $hasMore = $paginator->hasMorePages();
@endphp
<div class="tp-toolbar">
    <div class="tp-toolbar-left">
        <span class="text-muted small">Showing {{ $perPage }} per page</span>
    </div>
    <div class="tp-toolbar-right">
        <div class="tp-page-nav" aria-label="Pagination">
            @if($currentPage <= 1)
                <button type="button" class="tp-page-btn" disabled><i class="mdi mdi-chevron-left"></i></button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="tp-page-btn tp-page-link" data-page="{{ $currentPage - 1 }}"><i class="mdi mdi-chevron-left"></i></a>
            @endif
            <span class="tp-page-label">Page {{ $currentPage }}</span>
            @if($hasMore)
                <a href="{{ $paginator->nextPageUrl() }}" class="tp-page-btn tp-page-link" data-page="{{ $currentPage + 1 }}"><i class="mdi mdi-chevron-right"></i></a>
            @else
                <button type="button" class="tp-page-btn" disabled><i class="mdi mdi-chevron-right"></i></button>
            @endif
        </div>
    </div>
</div>
