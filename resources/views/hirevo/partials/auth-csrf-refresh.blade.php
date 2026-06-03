@push('scripts')
<script>
(function () {
    var meta = document.querySelector('meta[name="csrf-token"]');
    if (!meta) return;

    var tokenUrl = @json(route('csrf-token'));

    function syncToken(token) {
        if (!token) return;
        meta.setAttribute('content', token);
        document.querySelectorAll('input[name="_token"]').forEach(function (input) {
            input.value = token;
        });
    }

    function refreshCsrfToken() {
        return fetch(tokenUrl, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (data && data.token) {
                    syncToken(data.token);
                }
            });
    }

    document.querySelectorAll('.auth-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (form.getAttribute('data-csrf-refreshed') === '1') {
                form.removeAttribute('data-csrf-refreshed');
                return;
            }
            e.preventDefault();
            refreshCsrfToken()
                .catch(function () {})
                .finally(function () {
                    form.setAttribute('data-csrf-refreshed', '1');
                    form.submit();
                });
        });
    });

    window.setInterval(function () {
        refreshCsrfToken().catch(function () {});
    }, 5 * 60 * 1000);
})();
</script>
@endpush
