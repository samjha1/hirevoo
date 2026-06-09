<div class="modal fade" id="planCheckoutModal" tabindex="-1" aria-labelledby="planCheckoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content plan-checkout-modal">
            <div class="plan-checkout-modal__accent"></div>
            <div class="modal-header border-0 pb-0">
                <div>
                    <div class="plan-checkout-modal__eyebrow">Secure checkout</div>
                    <h5 class="modal-title fw-700 mb-0" id="planCheckoutModalLabel">Purchase plan</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="plan-checkout-notice" id="plan-checkout-notice" role="status">
                    <i class="mdi mdi-information-outline"></i>
                    <span>{{ config('hirevo_plans.checkout.cheque_notice') }}</span>
                </div>

                <div id="plan-checkout-error" class="alert alert-danger d-none py-2 px-3 mb-3 small" role="alert"></div>
                <div id="plan-checkout-success" class="alert alert-success d-none py-2 px-3 mb-3 small" role="status"></div>

                <div id="plan-checkout-step-1">
                    <p class="text-muted small mb-3">Review your company details and enter cheque information to continue.</p>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-500">Company name</label>
                            <input type="text" class="form-control plan-checkout-input" id="plan-checkout-company" readonly>
                            <div class="form-text" id="plan-checkout-company-hint" hidden>
                                <a href="{{ route('employer.profile') }}">Update company name in profile</a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Selected plan</label>
                            <input type="text" class="form-control plan-checkout-input" id="plan-checkout-plan-name" readonly>
                        </div>
                    </div>

                    <div class="plan-checkout-summary mb-3">
                        <div class="plan-checkout-summary__title">Order summary</div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Base amount</span>
                            <span id="plan-checkout-base">—</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span id="plan-checkout-gst-label">GST</span>
                            <span id="plan-checkout-gst">—</span>
                        </div>
                        <div class="d-flex justify-content-between fw-700 mt-2 pt-2 plan-checkout-summary__total">
                            <span>Total payable</span>
                            <span id="plan-checkout-total">—</span>
                        </div>
                        <p class="text-muted small mb-0 mt-2" id="plan-checkout-price-sub"></p>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="plan-checkout-cheque-number" class="form-label fw-500">Cheque number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control plan-checkout-input" id="plan-checkout-cheque-number" maxlength="191" autocomplete="off" required>
                        </div>
                        <div class="col-md-6">
                            <label for="plan-checkout-cheque-date" class="form-label fw-500">Cheque date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control plan-checkout-input" id="plan-checkout-cheque-date" max="{{ now()->format('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>

                <div id="plan-checkout-step-2" hidden>
                    <p class="text-muted small mb-2">Please read and accept the subscription agreement below.</p>
                    <div class="plan-checkout-agreement border rounded p-3 mb-3" id="plan-checkout-agreement-body">
                        <p class="text-muted mb-0 small">Agreement details will appear here after you enter cheque information.</p>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="plan-checkout-agreement" value="1">
                        <label class="form-check-label" for="plan-checkout-agreement">
                            I agree to the Hirevo subscription agreement and Terms &amp; Conditions.
                        </label>
                    </div>
                </div>

                <div id="plan-checkout-loading" class="text-center py-4 d-none" aria-live="polite">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="ms-2 text-muted small">Loading…</span>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="plan-checkout-close-btn">Close</button>
                <button type="button" class="btn btn-outline-secondary d-none" id="plan-checkout-back-btn">Back</button>
                <button type="button" class="btn plan-checkout-btn-primary" id="plan-checkout-next-btn">Continue to agreement</button>
                <button type="button" class="btn plan-checkout-btn-primary d-none" id="plan-checkout-submit-btn">Submit cheque payment</button>
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
    .plan-checkout-notice {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        background: linear-gradient(135deg, #FFFBEB, #FEF3C7);
        border: 1px solid rgba(217,119,6,0.2);
        border-radius: 12px;
        padding: 12px 14px;
        margin-bottom: 16px;
        font-size: 0.8125rem;
        color: #92400E;
        line-height: 1.5;
    }
    .plan-checkout-notice i {
        font-size: 1.1rem;
        color: #D97706;
        flex-shrink: 0;
        margin-top: 1px;
    }
    .plan-checkout-input {
        border-radius: 10px;
        border-color: #E2E8F0;
    }
    .plan-checkout-input:focus {
        border-color: #2EC4B6;
        box-shadow: 0 0 0 3px rgba(46,196,182,0.15);
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
        font-family: 'Sora', sans-serif;
    }
    .plan-checkout-agreement {
        max-height: 280px;
        overflow-y: auto;
        font-size: 0.875rem;
        line-height: 1.55;
        background: #FAFBFC;
        border-color: #E2E8F0 !important;
        border-radius: 12px !important;
    }
    .plan-checkout-agreement p {
        margin-bottom: 0.75rem;
    }
    .plan-checkout-btn-primary {
        background: linear-gradient(135deg, #2EC4B6, #3A7DFF);
        border: none;
        color: #fff;
        font-weight: 600;
        padding: 10px 22px;
        border-radius: 10px;
        box-shadow: 0 4px 14px rgba(46,196,182,0.3);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .plan-checkout-btn-primary:hover {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(46,196,182,0.4);
    }
</style>
