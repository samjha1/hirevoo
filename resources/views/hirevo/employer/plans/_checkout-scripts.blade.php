@push('scripts')
<script>
(function () {
    var modalEl = document.getElementById('planCheckoutModal');
    if (!modalEl) return;

    var quoteUrlTemplate = @json(route('employer.plans.quote', ['planKey' => '__PLAN__']));
    var checkoutUrl = @json(route('employer.plans.checkout.cheque'));
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var userName = @json(auth()->user()->name);
    var userEmail = @json(auth()->user()->email);
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    var state = { planKey: null, step: 1, quote: null, appliedCoupon: null, billingMonths: null };

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
        couponCode: document.getElementById('plan-checkout-coupon-code'),
        couponApply: document.getElementById('plan-checkout-coupon-apply'),
        couponHint: document.getElementById('plan-checkout-coupon-hint'),
        couponSuccess: document.getElementById('plan-checkout-coupon-success'),
        originalRow: document.getElementById('plan-checkout-original-row'),
        originalBase: document.getElementById('plan-checkout-original-base'),
        discountRow: document.getElementById('plan-checkout-discount-row'),
        discountLabel: document.getElementById('plan-checkout-discount-label'),
        discount: document.getElementById('plan-checkout-discount'),
        base: document.getElementById('plan-checkout-base'),
        gstLabel: document.getElementById('plan-checkout-gst-label'),
        gst: document.getElementById('plan-checkout-gst'),
        total: document.getElementById('plan-checkout-total'),
        priceSub: document.getElementById('plan-checkout-price-sub'),
        durationWrap: document.getElementById('plan-checkout-duration-wrap'),
        durationOptions: document.getElementById('plan-checkout-duration-options'),
        utr: document.getElementById('plan-checkout-utr'),
        paymentDate: document.getElementById('plan-checkout-payment-date'),
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
        state.appliedCoupon = null;
        state.billingMonths = null;
        state.step = 1;
        hideError();
        hideSuccess();
        setStep(1);
        if (els.utr) els.utr.value = '';
        if (els.paymentDate) els.paymentDate.value = '';
        if (els.couponCode) els.couponCode.value = '';
        if (els.couponSuccess) {
            els.couponSuccess.textContent = '';
            els.couponSuccess.classList.add('d-none');
        }
        if (els.couponApply) els.couponApply.textContent = 'Apply';
        els.agreementCheck.checked = false;
        els.nextBtn.disabled = false;
        els.submitBtn.disabled = false;
        els.title.textContent = 'Purchase plan';
        els.closeBtn.textContent = 'Close';
    }

    function renderDurationOptions(options, selectedMonths) {
        if (!els.durationOptions || !els.durationWrap) return;
        if (!options || options.length <= 1) {
            els.durationWrap.hidden = true;
            els.durationOptions.innerHTML = '';
            return;
        }

        els.durationWrap.hidden = false;
        els.durationOptions.innerHTML = options.map(function (months) {
            var active = Number(months) === Number(selectedMonths);
            return '<button type="button" class="btn btn-outline-secondary' + (active ? ' active' : '') + '" data-billing-months="' + months + '">' + months + ' month' + (months > 1 ? 's' : '') + '</button>';
        }).join('');

        els.durationOptions.querySelectorAll('[data-billing-months]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var months = parseInt(btn.getAttribute('data-billing-months') || '1', 10);
                state.billingMonths = months;
                setLoading(true);
                hideError();
                fetchQuote(state.planKey, state.appliedCoupon, months)
                    .then(function (data) {
                        populateQuote(data);
                        setLoading(false);
                    })
                    .catch(function (err) {
                        setLoading(false);
                        showError(err.message);
                    });
            });
        });
    }

    function populateQuote(data) {
        state.quote = data;
        state.billingMonths = data.billing_months || state.billingMonths || 1;
        renderDurationOptions(data.billing_duration_options, state.billingMonths);
        if (data.payment_notice) {
            els.notice.querySelector('span').textContent = data.payment_notice;
        }
        els.company.value = data.company_name || '';
        els.companyHint.hidden = !!data.company_name;
        els.planName.value = data.plan_name || '';

        var hasDiscount = !!data.coupon_applied && Number(data.discount_amount) > 0;
        if (els.originalRow) els.originalRow.hidden = !hasDiscount;
        if (els.discountRow) els.discountRow.hidden = !hasDiscount;
        if (hasDiscount) {
            els.originalBase.textContent = formatInr(data.original_base_amount);
            els.discountLabel.textContent = 'Discount (' + data.discount_percent + '%)';
            els.discount.textContent = '−' + formatInr(data.discount_amount);
            state.appliedCoupon = data.coupon_code || null;
            if (els.couponCode) els.couponCode.value = data.coupon_code || '';
            if (els.couponSuccess) {
                els.couponSuccess.textContent = 'Coupon "' + data.coupon_code + '" applied.';
                els.couponSuccess.classList.remove('d-none');
            }
            if (els.couponApply) els.couponApply.textContent = 'Applied';
        } else {
            state.appliedCoupon = null;
            if (els.couponSuccess) {
                els.couponSuccess.textContent = '';
                els.couponSuccess.classList.add('d-none');
            }
            if (els.couponApply) els.couponApply.textContent = 'Apply';
        }

        els.base.textContent = formatInr(data.base_amount);
        els.gstLabel.textContent = 'GST (' + data.gst_rate + '%)';
        els.gst.textContent = formatInr(data.gst_amount);
        els.total.textContent = formatInr(data.total_amount);
        els.priceSub.textContent = data.price_sub || '';
        els.title.textContent = 'Purchase ' + (data.plan_name || 'plan');
        updateAgreementPreview();
    }

    function paymentDetailLine() {
        var utr = els.utr.value.trim() || '—';
        var payDate = els.paymentDate.value || '—';
        return 'Payment method: net banking transfer. UTR / reference: ' + escapeHtml(utr) +
            ' dated ' + escapeHtml(formatDisplayDate(payDate)) + '.';
    }

    function updateAgreementPreview() {
        if (!els.agreementBox || !state.quote) return;

        var q = state.quote;
        var accepted = new Date().toLocaleString('en-IN', { dateStyle: 'medium', timeStyle: 'short' });

        els.agreementBox.innerHTML =
            '<p><strong>Hirevo Employer Subscription Agreement</strong></p>' +
            '<p>This agreement is between <strong>Hirevoo Pvt. Ltd.</strong> ("Hirevo") and <strong>' +
            escapeHtml(q.company_name || 'Employer') + '</strong> ("Customer") for the <strong>' +
            escapeHtml(q.plan_name) + '</strong> plan.</p>' +
            '<p><strong>1. Subscription &amp; access</strong></p>' +
            '<p>Subscription fees provide access to Hirevo platform features for the selected billing period. Payment is for access, not for any guaranteed hiring outcome.</p>' +
            '<p><strong>2. Fees &amp; payment</strong></p>' +
            '<p>' +
            (q.coupon_applied && Number(q.discount_amount) > 0
                ? 'List price: ' + formatInr(q.original_base_amount) + '. Discount (' + q.discount_percent + '%): −' +
                    formatInr(q.discount_amount) + '. Coupon: ' + escapeHtml(q.coupon_code || '') + '. '
                : '') +
            'Base amount: ' + formatInr(q.base_amount) + '. GST (' + q.gst_rate + '%): ' +
            formatInr(q.gst_amount) + '. Total payable: ' + formatInr(q.total_amount) +
            ' (INR). ' + paymentDetailLine() + '</p>' +
            '<p>Subscription activation will occur after payment verification.</p>' +
            '<p><strong>3. Customer obligations</strong></p>' +
            '<p>Customer will use the platform lawfully and comply with Hirevo Terms &amp; Conditions and Privacy Policy.</p>' +
            '<p><strong>4. Term &amp; suspension</strong></p>' +
            '<p>Access continues for the subscribed period once activated. Hirevo may suspend access for fraud, abuse, or policy violations.</p>' +
            '<p><strong>5. Acknowledgment</strong></p>' +
            '<p>By checking the agreement box, Customer confirms acceptance of this subscription agreement.</p>' +
            '<p class="small text-muted mb-0">Accepted by: ' + escapeHtml(userName) + ' (' + escapeHtml(userEmail) +
            ') on ' + escapeHtml(accepted) + '. Payment via Net banking (NEFT/RTGS/IMPS).</p>';
    }

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function validateStep1() {
        if (!els.utr.value.trim()) {
            showError('Please enter the UTR or transaction reference.');
            return false;
        }
        if (!els.paymentDate.value) {
            showError('Please enter the payment date.');
            return false;
        }
        return true;
    }

    function buildCheckoutPayload() {
        var payload = {
            plan_key: state.planKey,
            payment_method: 'netbanking',
            agreement_accepted: '1',
            utr_reference: els.utr.value.trim(),
            payment_date: els.paymentDate.value,
        };

        if (state.appliedCoupon) {
            payload.coupon_code = state.appliedCoupon;
        }
        if (state.billingMonths) {
            payload.billing_months = state.billingMonths;
        }

        return payload;
    }

    function fetchQuote(planKey, couponCode, billingMonths) {
        var url = quoteUrlTemplate.replace('__PLAN__', encodeURIComponent(planKey));
        var params = [];
        if (couponCode) params.push('coupon_code=' + encodeURIComponent(couponCode));
        if (billingMonths) params.push('billing_months=' + encodeURIComponent(billingMonths));
        if (params.length) url += (url.indexOf('?') >= 0 ? '&' : '?') + params.join('&');

        return fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        }).then(function (res) {
            return res.json().then(function (data) {
                if (!res.ok) throw new Error(data.message || 'Unable to load plan quote.');
                return data;
            });
        });
    }

    function openCheckout(planKey, billingMonths) {
        resetModal();
        state.planKey = planKey;
        state.billingMonths = billingMonths || null;
        hideError();
        setLoading(true);
        modal.show();

        fetchQuote(planKey, null, state.billingMonths)
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

    function applyCoupon() {
        if (!state.planKey || !els.couponCode) return;

        hideError();
        var code = els.couponCode.value.trim();
        if (!code) {
            showError('Please enter a coupon code.');
            return;
        }

        setLoading(true);
        fetchQuote(state.planKey, code, state.billingMonths)
            .then(function (data) {
                populateQuote(data);
                setLoading(false);
            })
            .catch(function (err) {
                setLoading(false);
                state.appliedCoupon = null;
                if (els.couponSuccess) {
                    els.couponSuccess.textContent = '';
                    els.couponSuccess.classList.add('d-none');
                }
                if (els.couponApply) els.couponApply.textContent = 'Apply';
                showError(err.message);
            });
    }

    if (els.couponApply) {
        els.couponApply.addEventListener('click', applyCoupon);
    }

    if (els.couponCode) {
        els.couponCode.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                applyCoupon();
            }
        });
    }

    document.querySelectorAll('.js-plan-checkout').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var planKey = btn.getAttribute('data-plan-key');
            var billingMonths = parseInt(btn.getAttribute('data-billing-months') || '0', 10) || null;
            if (planKey) openCheckout(planKey, billingMonths);
        });
    });

    document.querySelectorAll('.plan-checkout-copy-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = btn.getAttribute('data-copy-target');
            var target = targetId ? document.getElementById(targetId) : null;
            if (!target) return;

            var text = target.textContent.trim();
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function () {
                    btn.textContent = 'Copied';
                    setTimeout(function () { btn.textContent = 'Copy'; }, 1500);
                }).catch(function () {});
            }
        });
    });

    els.nextBtn.addEventListener('click', function () {
        hideError();

        if (!validateStep1()) {
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
            body: JSON.stringify(buildCheckoutPayload()),
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

    [els.utr, els.paymentDate].forEach(function (input) {
        if (!input) return;
        input.addEventListener('input', updateAgreementPreview);
        input.addEventListener('change', updateAgreementPreview);
    });

    modalEl.addEventListener('hidden.bs.modal', resetModal);
})();
</script>
@endpush
