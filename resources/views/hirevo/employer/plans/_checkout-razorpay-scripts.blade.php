@php
    $razorpayKey = $razorpayKeyId ?? config('razorpay.key_id');
@endphp
@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
(function () {
    var keyId = @json($razorpayKey);
    var quoteUrlTemplate = @json(route('employer.plans.quote', ['planKey' => '__PLAN__']));
    var createOrderUrl = @json(route('employer.plans.create-order'));
    var verifyUrl = @json(route('employer.plans.verify-payment'));
    var syncUrl = @json(route('employer.plans.sync-payment'));
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var pendingOrderKey = 'hirevo_employer_pending_order';
    var userName = @json(auth()->user()->name ?? '');
    var userEmail = @json(auth()->user()->email ?? '');

    var modalEl = document.getElementById('employerPlanCheckoutModal');
    if (!modalEl) return;

    var modal = typeof bootstrap !== 'undefined' ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;
    var state = { planKey: '', quote: null, orderId: '', appliedCoupon: null };
    var paymentConfigured = Boolean(keyId);

    var els = {
        company: document.getElementById('employer-checkout-company'),
        planName: document.getElementById('employer-checkout-plan-name'),
        couponCode: document.getElementById('employer-checkout-coupon-code'),
        couponApply: document.getElementById('employer-checkout-coupon-apply'),
        couponSuccess: document.getElementById('employer-checkout-coupon-success'),
        originalRow: document.getElementById('employer-checkout-original-row'),
        originalBase: document.getElementById('employer-checkout-original-base'),
        discountRow: document.getElementById('employer-checkout-discount-row'),
        discountLabel: document.getElementById('employer-checkout-discount-label'),
        discount: document.getElementById('employer-checkout-discount'),
        base: document.getElementById('employer-checkout-base'),
        gstLabel: document.getElementById('employer-checkout-gst-label'),
        gst: document.getElementById('employer-checkout-gst'),
        total: document.getElementById('employer-checkout-total'),
        priceSub: document.getElementById('employer-checkout-price-sub'),
        error: document.getElementById('employer-checkout-error'),
        success: document.getElementById('employer-checkout-success'),
        payBtn: document.getElementById('employer-checkout-pay-btn'),
        spinner: document.getElementById('employer-checkout-spinner'),
        payLabel: document.querySelector('.employer-checkout-pay-label'),
    };

    function formatInr(amount) {
        return '₹' + Number(amount).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function showError(msg) {
        if (!els.error) return;
        els.error.textContent = msg;
        els.error.classList.remove('d-none');
        if (els.success) els.success.classList.add('d-none');
    }

    function showSuccess(msg) {
        if (!els.success) return;
        els.success.textContent = msg;
        els.success.classList.remove('d-none');
        if (els.error) els.error.classList.add('d-none');
    }

    function clearAlerts() {
        if (els.error) els.error.classList.add('d-none');
        if (els.success) els.success.classList.add('d-none');
    }

    function setLoading(loading) {
        if (!els.payBtn) return;
        els.payBtn.disabled = loading || !state.quote;
        if (els.spinner) els.spinner.classList.toggle('d-none', !loading);
        if (els.payLabel) els.payLabel.textContent = loading ? 'Processing…' : 'Pay now';
    }

    function storePendingOrder(orderId) {
        state.orderId = orderId || '';
        try {
            if (orderId) sessionStorage.setItem(pendingOrderKey, orderId);
            else sessionStorage.removeItem(pendingOrderKey);
        } catch (e) {}
    }

    function readPendingOrder() {
        try { return sessionStorage.getItem(pendingOrderKey) || ''; } catch (e) { return ''; }
    }

    function parseJsonResponse(r) {
        var contentType = r.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            return r.text().then(function () {
                throw new Error('Unexpected server response. Please refresh and try again.');
            });
        }
        return r.json().then(function (d) { return { ok: r.ok, data: d }; });
    }

    function applyQuote(q) {
        state.quote = q;
        if (els.company) els.company.value = q.company_name || '';
        if (els.planName) els.planName.value = q.plan_name || '';
        var hasDiscount = !!q.coupon_applied && Number(q.discount_amount) > 0;
        if (els.originalRow) els.originalRow.hidden = !hasDiscount;
        if (els.discountRow) els.discountRow.hidden = !hasDiscount;
        if (hasDiscount) {
            els.originalBase.textContent = formatInr(q.original_base_amount);
            els.discountLabel.textContent = 'Discount (' + q.discount_percent + '%)';
            els.discount.textContent = '−' + formatInr(q.discount_amount);
            state.appliedCoupon = q.coupon_code || null;
            if (els.couponCode) els.couponCode.value = q.coupon_code || '';
            if (els.couponSuccess) {
                els.couponSuccess.textContent = 'Coupon "' + q.coupon_code + '" applied.';
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
        if (els.base) els.base.textContent = formatInr(q.base_amount);
        if (els.gstLabel) els.gstLabel.textContent = 'GST (' + q.gst_rate + '%)';
        if (els.gst) els.gst.textContent = formatInr(q.gst_amount);
        if (els.total) els.total.textContent = formatInr(q.total_amount);
        if (els.priceSub) els.priceSub.textContent = q.price_sub || '';
        if (els.payBtn) els.payBtn.disabled = false;
    }

    function fetchQuote(planKey, couponCode) {
        var url = quoteUrlTemplate.replace('__PLAN__', encodeURIComponent(planKey));
        if (couponCode) url += (url.indexOf('?') >= 0 ? '&' : '?') + 'coupon_code=' + encodeURIComponent(couponCode);
        return fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        }).then(parseJsonResponse).then(function (res) {
            if (!res.ok) throw new Error(res.data.message || 'Unable to load quote.');
            return res.data;
        });
    }

    function handlePaymentSuccess(data) {
        storePendingOrder('');
        showSuccess(data.message || 'Payment successful!');
        setTimeout(function () {
            window.location.href = data.redirect || @json(route('employer.dashboard'));
        }, 1200);
    }

    function syncPayment(orderId, silent) {
        if (!orderId) return Promise.resolve();
        if (!silent) setLoading(true);
        return fetch(syncUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ razorpay_order_id: orderId }),
        })
            .then(parseJsonResponse)
            .then(function (res) {
                if (!res.ok) throw new Error(res.data.message || 'Payment is still processing.');
                handlePaymentSuccess(res.data);
            })
            .catch(function (e) {
                if (!silent) showError(e.message || 'Could not confirm payment yet.');
            })
            .finally(function () { if (!silent) setLoading(false); });
    }

    function openCheckout(planKey) {
        if (!paymentConfigured) {
            showError('Online payment is not configured yet.');
            return;
        }
        state.planKey = planKey;
        state.quote = null;
        state.appliedCoupon = null;
        clearAlerts();
        if (els.payBtn) els.payBtn.disabled = true;
        if (els.couponCode) els.couponCode.value = '';
        if (!modal) return;
        modal.show();
        setLoading(true);
        fetchQuote(planKey, null)
            .then(function (data) { applyQuote(data); })
            .catch(function (e) { showError(e.message); })
            .finally(function () { setLoading(false); });
    }

    function openRazorpay(order) {
        if (typeof Razorpay === 'undefined') {
            showError('Payment gateway failed to load. Please refresh and try again.');
            return;
        }
        var options = {
            key: order.key_id || keyId,
            amount: order.amount,
            currency: order.currency || 'INR',
            name: 'Hirevoo',
            description: (state.quote && state.quote.plan_name) ? state.quote.plan_name + ' Plan' : 'Employer Plan',
            order_id: order.order_id,
            prefill: { name: userName, email: userEmail },
            theme: { color: '#2563eb' },
            handler: function (response) { verifyPayment(response); },
            modal: {
                ondismiss: function () {
                    var pending = state.orderId || readPendingOrder();
                    if (pending) syncPayment(pending, false);
                },
            },
        };
        var rzp = new Razorpay(options);
        rzp.on('payment.failed', function (resp) {
            showError((resp.error && resp.error.description) ? resp.error.description : 'Payment failed.');
            var pending = state.orderId || readPendingOrder();
            if (pending) syncPayment(pending, true);
        });
        rzp.open();
    }

    function verifyPayment(response) {
        setLoading(true);
        fetch(verifyUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                razorpay_order_id: response.razorpay_order_id,
                razorpay_payment_id: response.razorpay_payment_id,
                razorpay_signature: response.razorpay_signature,
            }),
        })
            .then(parseJsonResponse)
            .then(function (res) {
                if (!res.ok) throw new Error(res.data.message || 'Payment verification failed.');
                handlePaymentSuccess(res.data);
            })
            .catch(function (e) {
                var orderId = response.razorpay_order_id || state.orderId || readPendingOrder();
                if (orderId) syncPayment(orderId, false);
                else showError(e.message || 'Payment verification failed.');
            })
            .finally(function () { setLoading(false); });
    }

    document.querySelectorAll('.js-plan-checkout').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var planKey = btn.getAttribute('data-plan-key');
            if (planKey) openCheckout(planKey);
        });
    });

    if (els.couponApply) {
        els.couponApply.addEventListener('click', function () {
            if (!state.planKey || !els.couponCode) return;
            var code = els.couponCode.value.trim();
            if (!code) { showError('Please enter a coupon code.'); return; }
            setLoading(true);
            clearAlerts();
            fetchQuote(state.planKey, code)
                .then(function (data) { applyQuote(data); })
                .catch(function (e) { showError(e.message); })
                .finally(function () { setLoading(false); });
        });
    }

    if (els.payBtn) {
        els.payBtn.addEventListener('click', function () {
            if (!state.planKey || !state.quote) return;
            setLoading(true);
            clearAlerts();
            var body = { plan_key: state.planKey };
            if (state.appliedCoupon) body.coupon_code = state.appliedCoupon;
            fetch(createOrderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify(body),
            })
                .then(parseJsonResponse)
                .then(function (res) {
                    if (!res.ok) throw new Error(res.data.message || 'Could not start payment.');
                    storePendingOrder(res.data.order_id);
                    openRazorpay(res.data);
                })
                .catch(function (e) { showError(e.message || 'Payment could not be started.'); })
                .finally(function () { setLoading(false); });
        });
    }

    var resumeOrderId = readPendingOrder();
    if (resumeOrderId) syncPayment(resumeOrderId, true);

    modalEl.addEventListener('hidden.bs.modal', function () {
        var pending = state.orderId || readPendingOrder();
        if (pending) syncPayment(pending, true);
    });
})();
</script>
@endpush
