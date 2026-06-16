<div class="modal fade" id="candidatePlanCheckoutModal" tabindex="-1" aria-labelledby="candidatePlanCheckoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content plan-checkout-modal">
            <div class="plan-checkout-modal__accent"></div>
            <div class="modal-header border-0 pb-0">
                <div>
                    <div class="plan-checkout-modal__eyebrow">Secure checkout · Razorpay</div>
                    <h5 class="modal-title fw-700 mb-0" id="candidatePlanCheckoutModalLabel">Purchase plan</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div id="candidate-checkout-error" class="alert alert-danger d-none py-2 px-3 mb-3 small" role="alert"></div>
                <div id="candidate-checkout-success" class="alert alert-success d-none py-2 px-3 mb-3 small" role="status"></div>

                <p class="text-muted small mb-3">Review your order summary. GST is included in the total payable amount.</p>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-500">Your name</label>
                        <input type="text" class="form-control plan-checkout-input" id="candidate-checkout-name" readonly value="{{ auth()->user()->name ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-500">Selected plan</label>
                        <input type="text" class="form-control plan-checkout-input" id="candidate-checkout-plan-name" readonly>
                    </div>
                </div>

                <div class="plan-checkout-summary mb-3">
                    <div class="plan-checkout-summary__title">Order summary</div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span>Base amount</span>
                        <span id="candidate-checkout-base">—</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span id="candidate-checkout-gst-label">GST</span>
                        <span id="candidate-checkout-gst">—</span>
                    </div>
                    <div class="d-flex justify-content-between fw-700 mt-2 pt-2 plan-checkout-summary__total">
                        <span>Total payable</span>
                        <span id="candidate-checkout-total">—</span>
                    </div>
                    <p class="text-muted small mb-0 mt-2" id="candidate-checkout-tagline"></p>
                </div>

                <div class="small text-muted mb-3">
                    <i class="mdi mdi-shield-check-outline"></i>
                    Pay securely via card, UPI, or net banking.
                </div>

                <div class="d-flex flex-wrap gap-2 justify-content-end">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="candidate-checkout-pay-btn" disabled>
                        <span class="candidate-checkout-pay-label">Pay now</span>
                        <span class="spinner-border spinner-border-sm d-none" id="candidate-checkout-spinner" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .plan-checkout-modal {
        border: none;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(15,23,42,0.18);
    }
    .plan-checkout-modal__accent {
        height: 4px;
        background: linear-gradient(90deg, #2EC4B6, #3A7DFF, #7C3AED);
    }
    .plan-checkout-modal__eyebrow {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #2EC4B6;
        margin-bottom: 4px;
    }
    .plan-checkout-input {
        border-radius: 10px;
        border-color: #E2E8F0;
    }
    .plan-checkout-summary {
        background: linear-gradient(135deg, #F8FAFC, #F1F5F9);
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 16px 18px;
    }
    .plan-checkout-summary__title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748B;
        margin-bottom: 12px;
    }
    .plan-checkout-summary__total {
        border-top: 1px solid #E2E8F0;
        font-size: 1rem;
        color: #0F172A;
    }
    .plan-checkout-summary__total span:last-child {
        color: #1AA399;
        font-weight: 700;
    }
</style>
