@push('scripts')
<script>
(function () {
    var modalEl = document.getElementById('planCheckoutModal');
    if (!modalEl) return;

    var quoteUrlTemplate = @json(route('employer.plans.quote', ['planKey' => '__PLAN__']));
    var checkoutUrl = @json(route('employer.plans.checkout.cheque'));
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var profileUrl = @json(route('employer.profile'));
    var userName = @json(auth()->user()->name);
    var userEmail = @json(auth()->user()->email);

    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    var state = { planKey: null, step: 1, quote: null };

    var els = {
        notice: document.getElementById('plan-checkout-notice'),
        error: document.getElementById('plan-checkout-error'),
        success: document.getElementById('plan-checkout-success'),
        loading: document.getElementById('plan-checkout-loading'),
        step1: document.getElementById('plan-checkout-step-1'),
        step2: document.getElementById('plan-checkout-step-2'),
        company: document.getElementById('plan-checkout-company'),
        companyHint: document.getElementById('plan-checkout-company-hint'),
        planName: document.getElementById('plan-checkout-plan-name'),
        base: document.getElementById('plan-checkout-base'),
        gstLabel: document.getElementById('plan-checkout-gst-label'),
        gst: document.getElementById('plan-checkout-gst'),
        total: document.getElementById('plan-checkout-total'),
        priceSub: document.getElementById('plan-checkout-price-sub'),
        chequeNumber: document.getElementById('plan-checkout-cheque-number'),
        chequeDate: document.getElementById('plan-checkout-cheque-date'),
        agreementBox: document.getElementById('plan-checkout-agreement-body'),
        agreementCheck: document.getElementById('plan-checkout-agreement'),
        backBtn: document.getElementById('plan-checkout-back-btn'),
        nextBtn: document.getElementById('plan-checkout-next-btn'),
        submitBtn: document.getElementById('plan-checkout-submit-btn'),
        closeBtn: document.getElementById('plan-checkout-close-btn'),
        title: document.getElementById('planCheckoutModalLabel'),
    };

    function formatInr(value) {
        return '₹' + Number(value).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatDisplayDate(isoDate) {
        if (!isoDate) return '—';
        var parts = isoDate.split('-');
        if (parts.length !== 3) return isoDate;
        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return parts[2] + ' ' + months[parseInt(parts[1], 10) - 1] + ' ' + parts[0];
    }

    function showError(message) {
        els.error.textContent = message || 'Something went wrong. Please try again.';
        els.error.classList.remove('d-none');
    }

    function hideError() {
        els.error.classList.add('d-none');
        els.error.textContent = '';
    }

    function showSuccess(message) {
        els.success.textContent = message;
        els.success.classList.remove('d-none');
    }

    function hideSuccess() {
        els.success.classList.add('d-none');
        els.success.textContent = '';
    }

    function setLoading(isLoading) {
        els.loading.classList.toggle('d-none', !isLoading);
        els.step1.hidden = isLoading || state.step !== 1;
        els.step2.hidden = isLoading || state.step !== 2;
        els.nextBtn.disabled = isLoading;
        els.submitBtn.disabled = isLoading;
        els.backBtn.disabled = isLoading;
    }

    function setStep(step) {
        state.step = step;
        els.step1.hidden = step !== 1;
        els.step2.hidden = step !== 2;
        els.backBtn.classList.toggle('d-none', step !== 2);
        els.nextBtn.classList.toggle('d-none', step !== 1);
        els.submitBtn.classList.toggle('d-none', step !== 2);
    }

    function resetModal() {
        state.planKey = null;
        state.quote = null;
        state.step = 1;
        hideError();
        hideSuccess();
        setStep(1);
        els.chequeNumber.value = '';
        els.chequeDate.value = '';
        els.agreementCheck.checked = false;
        els.nextBtn.disabled = false;
        els.submitBtn.disabled = false;
        els.title.textContent = 'Purchase plan';
        els.closeBtn.textContent = 'Close';
    }

    function populateQuote(data) {
        state.quote = data;
        if (data.cheque_notice) {
            els.notice.textContent = data.cheque_notice;
        }
        els.company.value = data.company_name || '';
        els.companyHint.hidden = !!data.company_name;
        els.planName.value = data.plan_name || '';
        els.base.textContent = formatInr(data.base_amount);
        els.gstLabel.textContent = 'GST (' + data.gst_rate + '%)';
        els.gst.textContent = formatInr(data.gst_amount);
        els.total.textContent = formatInr(data.total_amount);
        els.priceSub.textContent = data.price_sub || '';
        els.title.textContent = 'Purchase ' + (data.plan_name || 'plan');
        updateAgreementPreview();
    }

    function updateAgreementPreview() {
        if (!els.agreementBox || !state.quote) return;

        var q = state.quote;
        var chequeNo = els.chequeNumber.value.trim() || '—';
        var chequeDate = els.chequeDate.value || '—';
        var accepted = new Date().toLocaleString('en-IN', { dateStyle: 'medium', timeStyle: 'short' });

        els.agreementBox.innerHTML =
            '<p><strong>Hirevo Employer Subscription Agreement</strong></p>' +
            '<p>This agreement is between <strong>Hirevoo Pvt. Ltd.</strong> ("Hirevo") and <strong>' +
            escapeHtml(q.company_name || 'Employer') + '</strong> ("Customer") for the <strong>' +
            escapeHtml(q.plan_name) + '</strong> plan.</p>' +
            '<p><strong>1. Subscription &amp; access</strong></p>' +
            '<p>Subscription fees provide access to Hirevo platform features for the selected billing period. Payment is for access, not for any guaranteed hiring outcome.</p>' +
            '<p><strong>2. Fees &amp; payment</strong></p>' +
            '<p>Base amount: ' + formatInr(q.base_amount) + '. GST (' + q.gst_rate + '%): ' +
            formatInr(q.gst_amount) + '. Total payable: ' + formatInr(q.total_amount) +
            ' (INR). Payment method: cheque #' + escapeHtml(chequeNo) + ' dated ' +
            escapeHtml(formatDisplayDate(chequeDate)) + '.</p>' +
            '<p>Subscription activation will occur after cheque verification and clearance.</p>' +
            '<p><strong>3. Customer obligations</strong></p>' +
            '<p>Customer will use the platform lawfully and comply with Hirevo Terms &amp; Conditions and Privacy Policy.</p>' +
            '<p><strong>4. Term &amp; suspension</strong></p>' +
            '<p>Access continues for the subscribed period once activated. Hirevo may suspend access for fraud, abuse, or policy violations.</p>' +
            '<p><strong>5. Acknowledgment</strong></p>' +
            '<p>By checking the agreement box, Customer confirms acceptance of this subscription agreement.</p>' +
            '<p class="small text-muted mb-0">Accepted by: ' + escapeHtml(userName) + ' (' + escapeHtml(userEmail) +
            ') on ' + escapeHtml(accepted) + '.</p>';
    }

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function openCheckout(planKey) {
        resetModal();
        state.planKey = planKey;
        hideError();
        setLoading(true);
        modal.show();

        var url = quoteUrlTemplate.replace('__PLAN__', encodeURIComponent(planKey));

        fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (res) {
                return res.json().then(function (data) {
                    if (!res.ok) throw new Error(data.message || 'Unable to load plan quote.');
                    return data;
                });
            })
            .then(function (data) {
                populateQuote(data);
                setLoading(false);
            })
            .catch(function (err) {
                setLoading(false);
                showError(err.message);
                els.step1.hidden = true;
                els.nextBtn.classList.add('d-none');
            });
    }

    document.querySelectorAll('.js-plan-checkout').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var planKey = btn.getAttribute('data-plan-key');
            if (planKey) openCheckout(planKey);
        });
    });

    els.nextBtn.addEventListener('click', function () {
        hideError();

        if (!els.chequeNumber.value.trim()) {
            showError('Please enter the cheque number.');
            return;
        }
        if (!els.chequeDate.value) {
            showError('Please enter the cheque date.');
            return;
        }

        updateAgreementPreview();
        setStep(2);
    });

    els.backBtn.addEventListener('click', function () {
        hideError();
        setStep(1);
    });

    els.submitBtn.addEventListener('click', function () {
        hideError();

        if (!els.agreementCheck.checked) {
            showError('You must accept the subscription agreement to continue.');
            return;
        }

        setLoading(true);

        fetch(checkoutUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                plan_key: state.planKey,
                cheque_number: els.chequeNumber.value.trim(),
                cheque_date: els.chequeDate.value,
                agreement_accepted: '1',
            }),
        })
            .then(function (res) {
                return res.json().then(function (data) {
                    if (!res.ok) {
                        var msg = data.message || 'Unable to submit payment.';
                        if (data.errors) {
                            var first = Object.values(data.errors)[0];
                            if (Array.isArray(first) && first[0]) msg = first[0];
                        }
                        throw new Error(msg);
                    }
                    return data;
                });
            })
            .then(function (data) {
                setLoading(false);
                els.step1.hidden = true;
                els.step2.hidden = true;
                els.backBtn.classList.add('d-none');
                els.nextBtn.classList.add('d-none');
                els.submitBtn.classList.add('d-none');
                showSuccess(data.message || 'Your plan request is received.');
                els.closeBtn.textContent = 'Done';
            })
            .catch(function (err) {
                setLoading(false);
                showError(err.message);
            });
    });

    modalEl.addEventListener('hidden.bs.modal', resetModal);
})();
</script>
@endpush
