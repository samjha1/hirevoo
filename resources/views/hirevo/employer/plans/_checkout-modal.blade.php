<div class="modal fade" id="planCheckoutModal" tabindex="-1" aria-labelledby="planCheckoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content plan-checkout-modal">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-700" id="planCheckoutModalLabel">Purchase plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="alert alert-warning py-2 px-3 mb-3 small" id="plan-checkout-notice" role="status">
                    {{ config('hirevo_plans.checkout.cheque_notice') }}
                </div>

                <div id="plan-checkout-error" class="alert alert-danger d-none py-2 px-3 mb-3 small" role="alert"></div>
                <div id="plan-checkout-success" class="alert alert-success d-none py-2 px-3 mb-3 small" role="status"></div>

                <div id="plan-checkout-step-1">
                    <p class="text-muted small mb-3">Review your company details and enter cheque information to continue.</p>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-500">Company name</label>
                            <input type="text" class="form-control" id="plan-checkout-company" readonly>
                            <div class="form-text" id="plan-checkout-company-hint" hidden>
                                <a href="{{ route('employer.profile') }}">Update company name in profile</a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Selected plan</label>
                            <input type="text" class="form-control" id="plan-checkout-plan-name" readonly>
                        </div>
                    </div>

                    <div class="plan-checkout-summary mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Base amount</span>
                            <span id="plan-checkout-base">—</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span id="plan-checkout-gst-label">GST</span>
                            <span id="plan-checkout-gst">—</span>
                        </div>
                        <div class="d-flex justify-content-between fw-700 mt-2 pt-2 border-top">
                            <span>Total payable</span>
                            <span id="plan-checkout-total">—</span>
                        </div>
                        <p class="text-muted small mb-0 mt-2" id="plan-checkout-price-sub"></p>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="plan-checkout-cheque-number" class="form-label fw-500">Cheque number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="plan-checkout-cheque-number" maxlength="191" autocomplete="off" required>
                        </div>
                        <div class="col-md-6">
                            <label for="plan-checkout-cheque-date" class="form-label fw-500">Cheque date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="plan-checkout-cheque-date" max="{{ now()->format('Y-m-d') }}" required>
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
                <button type="button" class="btn btn-primary" id="plan-checkout-next-btn">Continue to agreement</button>
                <button type="button" class="btn btn-primary d-none" id="plan-checkout-submit-btn">Submit cheque payment</button>
            </div>
        </div>
    </div>
</div>

<style>
    .plan-checkout-modal .plan-checkout-summary {
        background: var(--surface-2, #f5f7fa);
        border: 1px solid var(--border, #e5e8ee);
        border-radius: 12px;
        padding: 14px 16px;
    }
    .plan-checkout-modal .plan-checkout-agreement {
        max-height: 280px;
        overflow-y: auto;
        font-size: 0.875rem;
        line-height: 1.55;
        background: #fafafa;
    }
    .plan-checkout-modal .plan-checkout-agreement p {
        margin-bottom: 0.75rem;
    }
</style>
