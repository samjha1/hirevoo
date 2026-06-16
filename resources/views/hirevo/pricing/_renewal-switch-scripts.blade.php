<script>
(function () {
    var scheduleUrl = @json(route('candidate.api.schedule-renewal-plan'));
    var clearUrl = @json(route('candidate.api.clear-renewal-plan'));
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var expiresAt = @json(isset($candidatePlanExpiresAt) && $candidatePlanExpiresAt ? $candidatePlanExpiresAt->format('d M Y') : null);
    var alertEl = document.getElementById('candidate-renewal-alert');

    function showAlert(message, type) {
        if (!alertEl) return;
        alertEl.textContent = message;
        alertEl.className = 'alert border-0 shadow-sm rounded-3 mt-3 alert-' + (type || 'success');
        alertEl.classList.remove('d-none');
        alertEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function parseJsonResponse(r) {
        var contentType = r.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            return r.text().then(function () {
                throw new Error('Unexpected server response. Please refresh and try again.');
            });
        }
        return r.json().then(function (d) {
            return { ok: r.ok, data: d };
        });
    }

    function postJson(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify(body || {}),
        }).then(parseJsonResponse);
    }

    document.querySelectorAll('.candidate-renewal-switch-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var planKey = btn.dataset.planKey || '';
            var planName = btn.dataset.planName || 'this plan';
            if (!planKey) return;

            var whenText = expiresAt ? (' when your current plan ends on ' + expiresAt) : ' at your next renewal';
            var confirmed = window.confirm('Switch to ' + planName + whenText + '? You can change this anytime before renewal.');
            if (!confirmed) return;

            btn.disabled = true;
            postJson(scheduleUrl, { plan_key: planKey })
                .then(function (res) {
                    if (!res.ok) throw new Error(res.data.message || 'Could not schedule plan switch.');
                    showAlert(res.data.message || 'Plan switch scheduled.');
                    setTimeout(function () { window.location.reload(); }, 900);
                })
                .catch(function (e) {
                    showAlert(e.message || 'Could not schedule plan switch.', 'danger');
                    btn.disabled = false;
                });
        });
    });

    document.querySelectorAll('.candidate-renewal-cancel-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!window.confirm('Cancel the scheduled plan switch?')) return;

            btn.disabled = true;
            postJson(clearUrl, {})
                .then(function (res) {
                    if (!res.ok) throw new Error(res.data.message || 'Could not cancel scheduled switch.');
                    showAlert(res.data.message || 'Scheduled switch cancelled.');
                    setTimeout(function () { window.location.reload(); }, 900);
                })
                .catch(function (e) {
                    showAlert(e.message || 'Could not cancel scheduled switch.', 'danger');
                    btn.disabled = false;
                });
        });
    });
})();
</script>
