@php
    $variant = $variant ?? 'topbar';
    $unread = (int) ($navUnreadCount ?? 0);
@endphp

@if($variant === 'navbar')
    <a href="javascript:void(0)" class="nav-link hirevo-nav-icon position-relative cp-notify-trigger" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
        <i class="mdi mdi-bell fs-20"></i>
        @if($unread > 0)
            <span class="cp-notify-badge cp-notify-badge--navbar">{{ $unread > 9 ? '9+' : $unread }}</span>
        @endif
    </a>
    <div class="dropdown-menu dropdown-menu-end cp-notify-menu shadow border-0">
        @include('hirevo.partials._candidate-notifications-menu')
    </div>
@else
    <div class="dropdown cp-notify-wrap">
        <button type="button" class="cp-icon-btn cp-notify-trigger" data-bs-toggle="dropdown" aria-label="Notifications">
            <i class="mdi mdi-bell-outline"></i>
            @if($unread > 0)
                <span class="cp-notify-badge">{{ $unread > 9 ? '9+' : $unread }}</span>
            @endif
        </button>
        <div class="dropdown-menu dropdown-menu-end cp-notify-menu shadow border-0">
            @include('hirevo.partials._candidate-notifications-menu')
        </div>
    </div>
@endif
