@php
    $razorpayKey = $razorpayKeyId ?? config('razorpay.key_id');
@endphp
<div class="modal fade" id="employerPlanCheckoutModal" tabindex="-1" aria-labelledby="employerPlanCheckoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content plan-checkout-modal">
            <div class="plan-checkout-modal__accent"></div>
            <div class="modal-header border-0 pb-0">
                <div>
                    <div class="plan-checkout-modal__eyebrow">Secure checkout · Razorpay</div>
                    <h5 class="modal-title fw-700 mb-0" id="employerPlanCheckoutModalLabel">Purchase plan</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div id="employer-checkout-error" class="alert alert-danger d-none py-2 px-3 mb-3 small" role="alert"></div>
                <div id="employer-checkout-success" class="alert alert-success d-none py-2 px-3 mb-3 small" role="status"></div>

                <p class="text-muted small mb-3">Review your order summary. GST is included in the total payable amount.</p>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-500">Company name</label>
                        <input type="text" class="form-control plan-checkout-input" id="employer-checkout-company" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-500">Selected plan</label>
                        <input type="text" class="form-control plan-checkout-input" id="employer-checkout-plan-name" readonly>
                    </div>
                </div>

                <div class="mb-3" id="employer-checkout-duration-wrap" hidden>
                    <label class="form-label fw-500 mb-2">Subscription duration</label>
                    <div class="btn-group w-100" role="group" aria-label="Subscription duration" id="employer-checkout-duration-options"></div>
                </div>

                <div class="plan-checkout-coupon mb-3">
                    <label for="employer-checkout-coupon-code" class="form-label fw-500 mb-2">Coupon code</label>
                    <div class="input-group">
                        <input type="text" class="form-control plan-checkout-input" id="employer-checkout-coupon-code" maxlength="64" autocomplete="off" placeholder="Optional">
                        <button type="button" class="btn btn-outline-secondary" id="employer-checkout-coupon-apply">Apply</button>
                    </div>
                    <div class="small text-success mt-1 d-none" id="employer-checkout-coupon-success"></div>
                </div>

                <div class="plan-checkout-summary mb-3">
                    <div class="plan-checkout-summary__title">Order summary</div>
                    <div class="d-flex justify-content-between small mb-1" id="employer-checkout-original-row" hidden>
                        <span>List price</span>
                        <span id="employer-checkout-original-base">—</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-1 text-success" id="employer-checkout-discount-row" hidden>
                        <span id="employer-checkout-discount-label">Discount</span>
                        <span id="employer-checkout-discount">—</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span>Base amount</span>
                        <span id="employer-checkout-base">—</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span id="employer-checkout-gst-label">GST</span>
                        <span id="employer-checkout-gst">—</span>
                    </div>
                    <div class="d-flex justify-content-between fw-700 mt-2 pt-2 plan-checkout-summary__total">
                        <span>Total payable</span>
                        <span id="employer-checkout-total">—</span>
                    </div>
                    <p class="text-muted small mb-0 mt-2" id="employer-checkout-price-sub"></p>
                </div>

                <div class="small text-muted mb-3">
                    <i class="mdi mdi-shield-check-outline"></i>
                    Pay securely via card, UPI, or net banking. Plan activates immediately after payment.
                </div>

                <div class="d-flex flex-wrap gap-2 justify-content-end">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn plan-checkout-btn-primary" id="employer-checkout-pay-btn" disabled>
                        <span class="employer-checkout-pay-label">Pay now</span>
                        <span class="spinner-border spinner-border-sm d-none" id="employer-checkout-spinner" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
