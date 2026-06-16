@php

    $razorpayKey = $razorpayKeyId ?? config('razorpay.key_id');

@endphp

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>

(function () {

    var keyId = @json($razorpayKey);

    var quoteUrlTemplate = @json(url('/api/candidate/plans/__PLAN__/quote'));

    var createOrderUrl = @json(route('candidate.api.create-order'));

    var verifyUrl = @json(route('candidate.api.verify-payment'));

    var syncUrl = @json(route('candidate.api.sync-payment'));

    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    var pendingOrderKey = 'hirevo_candidate_pending_order';



    var modalEl = document.getElementById('candidatePlanCheckoutModal');

    if (!modalEl) return;



    var modal = typeof bootstrap !== 'undefined' ? new bootstrap.Modal(modalEl) : null;

    var state = { planKey: '', quote: null, orderId: '' };

    var paymentConfigured = Boolean(keyId);



    var els = {

        planName: document.getElementById('candidate-checkout-plan-name'),

        base: document.getElementById('candidate-checkout-base'),

        gstLabel: document.getElementById('candidate-checkout-gst-label'),

        gst: document.getElementById('candidate-checkout-gst'),

        total: document.getElementById('candidate-checkout-total'),

        tagline: document.getElementById('candidate-checkout-tagline'),

        error: document.getElementById('candidate-checkout-error'),

        success: document.getElementById('candidate-checkout-success'),

        payBtn: document.getElementById('candidate-checkout-pay-btn'),

        spinner: document.getElementById('candidate-checkout-spinner'),

        payLabel: document.querySelector('.candidate-checkout-pay-label'),

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

            if (orderId) {

                sessionStorage.setItem(pendingOrderKey, orderId);

            } else {

                sessionStorage.removeItem(pendingOrderKey);

            }

        } catch (e) {}

    }



    function readPendingOrder() {

        try {

            return sessionStorage.getItem(pendingOrderKey) || '';

        } catch (e) {

            return '';

        }

    }



    function applyQuote(q) {

        state.quote = q;

        if (els.planName) els.planName.value = q.plan_name || '';

        if (els.base) els.base.textContent = formatInr(q.base_amount);

        if (els.gstLabel) els.gstLabel.textContent = 'GST (' + q.gst_rate + '%)';

        if (els.gst) els.gst.textContent = formatInr(q.gst_amount);

        if (els.total) els.total.textContent = formatInr(q.total_amount);

        if (els.tagline) els.tagline.textContent = q.tagline || '';

        if (els.payBtn) els.payBtn.disabled = false;

    }



    function parseJsonResponse(r) {

        var contentType = r.headers.get('content-type') || '';

        if (!contentType.includes('application/json')) {

            return r.text().then(function (text) {

                throw new Error('Unexpected server response. Please refresh and try again.');

            });

        }

        return r.json().then(function (d) {

            return { ok: r.ok, data: d };

        });

    }



    function handlePaymentSuccess(data) {

        storePendingOrder('');

        showSuccess(data.message || 'Payment successful!');

        setTimeout(function () {

            window.location.href = data.redirect || @json(route('candidate.dashboard'));

        }, 1200);

    }



    function fetchQuote(planKey) {

        setLoading(true);

        clearAlerts();

        return fetch(quoteUrlTemplate.replace('__PLAN__', encodeURIComponent(planKey)), {

            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },

            credentials: 'same-origin',

        })

            .then(parseJsonResponse)

            .then(function (res) {

                if (!res.ok) throw new Error(res.data.message || 'Unable to load quote.');

                applyQuote(res.data);

            })

            .catch(function (e) {

                showError(e.message || 'Unable to load plan details.');

                state.quote = null;

                if (els.payBtn) els.payBtn.disabled = true;

            })

            .finally(function () { setLoading(false); });

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

            .finally(function () {

                if (!silent) setLoading(false);

            });

    }



    document.querySelectorAll('.candidate-plan-checkout-btn').forEach(function (btn) {

        btn.addEventListener('click', function () {

            state.planKey = btn.dataset.planKey || '';

            state.quote = null;

            clearAlerts();

            if (els.payBtn) els.payBtn.disabled = true;

            if (!modal) {

                showError('Checkout could not open. Please refresh the page.');

                return;

            }

            modal.show();

            if (!paymentConfigured) {

                showError('Online payment is not configured yet. Please contact support or try again later.');

                return;

            }

            fetchQuote(state.planKey);

        });

    });



    if (modalEl) {

        modalEl.addEventListener('hidden.bs.modal', function () {

            var pending = state.orderId || readPendingOrder();

            if (pending) {

                syncPayment(pending, true);

            }

        });

    }



    var resumeOrderId = readPendingOrder();

    if (resumeOrderId) {

        syncPayment(resumeOrderId, true);

    }



    if (els.payBtn) {

        els.payBtn.addEventListener('click', function () {

            if (!paymentConfigured) {

                showError('Online payment is not configured yet.');

                return;

            }

            if (!state.planKey || !state.quote) return;

            setLoading(true);

            clearAlerts();



            fetch(createOrderUrl, {

                method: 'POST',

                headers: {

                    'Content-Type': 'application/json',

                    'Accept': 'application/json',

                    'X-CSRF-TOKEN': csrf,

                    'X-Requested-With': 'XMLHttpRequest',

                },

                credentials: 'same-origin',

                body: JSON.stringify({ plan_key: state.planKey }),

            })

                .then(parseJsonResponse)

                .then(function (res) {

                    if (!res.ok) throw new Error(res.data.message || 'Could not start payment.');

                    storePendingOrder(res.data.order_id);

                    openRazorpay(res.data);

                })

                .catch(function (e) {

                    showError(e.message || 'Payment could not be started.');

                })

                .finally(function () { setLoading(false); });

        });

    }



    function openRazorpay(order) {

        if (typeof Razorpay === 'undefined') {

            showError('Payment gateway failed to load. Please refresh and try again.');

            return;

        }



        var userName = document.getElementById('candidate-checkout-name')?.value || '';

        var userEmail = @json(auth()->user()->email ?? '');

        var options = {

            key: order.key_id || keyId,

            amount: order.amount,

            currency: order.currency || 'INR',

            name: 'Hirevoo',

            description: (state.quote && state.quote.plan_name) ? state.quote.plan_name + ' Plan' : 'Premium Plan',

            order_id: order.order_id,

            prefill: { name: userName, email: userEmail },

            theme: { color: '#0d6efd' },

            handler: function (response) {

                verifyPayment(response);

            },

            modal: {

                ondismiss: function () {

                    var pending = state.orderId || readPendingOrder();

                    if (pending) {

                        syncPayment(pending, false);

                    } else {

                        showError('Payment cancelled. You can try again when ready.');

                    }

                },

            },

        };



        var rzp = new Razorpay(options);

        rzp.on('payment.failed', function (resp) {

            var msg = (resp.error && resp.error.description) ? resp.error.description : 'Payment failed. Please try again.';

            showError(msg);

            var pending = state.orderId || readPendingOrder();

            if (pending) {

                syncPayment(pending, true);

            }

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

                if (orderId) {

                    syncPayment(orderId, false);

                } else {

                    showError(e.message || 'Payment verification failed.');

                }

            })

            .finally(function () { setLoading(false); });

    }

})();

</script>

