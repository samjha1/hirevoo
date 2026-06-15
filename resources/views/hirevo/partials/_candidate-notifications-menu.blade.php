@php $unread = (int) ($navUnreadCount ?? 0); @endphp
<div class="cp-notify-head">
    <div>
        <strong>Notifications</strong>
        <p class="cp-notify-sub mb-0">
            @if($unread > 0)
                {{ $unread }} unread
            @else
                You're all caught up
            @endif
        </p>
    </div>
    @if($unread > 0)
        <form action="{{ route('notifications.read-all') }}" method="post" class="mb-0">
            @csrf
            <button type="submit" class="cp-notify-mark-all">Mark all read</button>
        </form>
    @endif
</div>
<div class="cp-notify-list">
    @forelse($navNotifications ?? [] as $note)
        @php $payload = is_array($note->data) ? $note->data : []; @endphp
        <form action="{{ route('notifications.read', $note->id) }}" method="post" class="mb-0">
            @csrf
            <button type="submit" class="cp-notify-item {{ $note->read_at ? 'is-read' : '' }}">
                <strong>{{ $payload['title'] ?? 'Update' }}</strong>
                <span>{{ \Illuminate\Support\Str::limit($payload['body'] ?? '', 140) }}</span>
                <em>{{ $note->created_at?->diffForHumans() }}</em>
            </button>
        </form>
    @empty
        <div class="cp-notify-empty">No notifications yet. When an employer updates your application stage, you'll see it here.</div>
    @endforelse
</div>
