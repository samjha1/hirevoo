<script>
(function () {
    var DISMISS_MS = 2000;
    function dismissBootstrapAlert(el) {
        if (!el || !el.parentNode) return;
        if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
            try {
                bootstrap.Alert.getOrCreateInstance(el).close();
                return;
            } catch (e) { /* fall through */ }
        }
        el.style.transition = 'opacity .25s ease';
        el.style.opacity = '0';
        setTimeout(function () { if (el.parentNode) el.remove(); }, 250);
    }
    function fadeRemove(el) {
        if (!el || !el.parentNode) return;
        el.style.transition = 'opacity .25s ease';
        el.style.opacity = '0';
        setTimeout(function () { if (el.parentNode) el.remove(); }, 250);
    }
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.ec-alert.success, .ec-alert.info').forEach(function (el) {
            setTimeout(function () { fadeRemove(el); }, DISMISS_MS);
        });
        document.querySelectorAll(
            '.alert.alert-success.alert-dismissible[role="alert"], .alert.alert-info.alert-dismissible[role="alert"]'
        ).forEach(function (el) {
            setTimeout(function () { dismissBootstrapAlert(el); }, DISMISS_MS);
        });
    });
})();
</script>
