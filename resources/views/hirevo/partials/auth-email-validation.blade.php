@push('scripts')
<script>
(function () {
    var form = document.querySelector('.auth-form');
    if (!form) return;
    var input = form.querySelector('#email[name="email"]');
    if (!input) return;

    var pattern = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/;
    var message = 'Please enter a valid email address (e.g. name@company.com).';

    function isValid(value) {
        var v = (value || '').trim();
        if (v === '' || v.indexOf('@') < 1) {
            return false;
        }
        return pattern.test(v);
    }

    function syncValidity() {
        input.setCustomValidity(isValid(input.value) ? '' : message);
    }

    input.addEventListener('input', syncValidity);
    input.addEventListener('blur', syncValidity);

    form.addEventListener('submit', function (e) {
        syncValidity();
        if (!isValid(input.value)) {
            e.preventDefault();
            input.reportValidity();
        }
    });

    syncValidity();
})();
</script>
@endpush
