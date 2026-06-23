<div class="tp-credits-modal-backdrop" id="tp-credits-modal" hidden aria-hidden="true">
    <div class="tp-credits-modal" role="dialog" aria-labelledby="tp-credits-modal-title">
        <button type="button" class="tp-credits-modal-close" id="tp-credits-modal-close" aria-label="Close">&times;</button>
        <div class="tp-credits-modal-icon" aria-hidden="true">
            <i class="mdi mdi-wallet-outline"></i>
        </div>
        <h5 id="tp-credits-modal-title" class="fw-700 text-center mb-2">Insufficient pool tokens</h5>
        <p class="text-muted small text-center mb-3">
            You don't have enough talent pool tokens for this action. Buy a plan with database tokens to continue.
        </p>
        <div class="tp-credits-modal-box small text-muted mb-3">
            <p class="fw-600 text-dark mb-2">How tokens work</p>
            <p class="mb-1"><i class="mdi mdi-phone-outline me-1"></i> View phone / contact = {{ config('hirevo_plans.unlock_credit_cost', 1) }} token</p>
            <p class="mb-0"><i class="mdi mdi-download-outline me-1"></i> Download candidate data = {{ config('hirevo_plans.excel_download_credit_cost', 1) }} token</p>
        </div>
        <a href="{{ route('employer.plans.index') }}" class="btn btn-success w-100 mb-2">Get pool tokens</a>
    </div>
</div>

<style>
    .tp-credits-modal-backdrop {
        position: fixed; inset: 0; z-index: 2000;
        background: rgba(15, 23, 42, .55);
        display: flex; align-items: center; justify-content: center; padding: 1rem;
    }
    .tp-credits-modal-backdrop[hidden] { display: none !important; }
    .tp-credits-modal {
        background: #fff; border-radius: 16px; max-width: 400px; width: 100%;
        padding: 1.5rem; position: relative; box-shadow: 0 24px 60px rgba(0,0,0,.2);
    }
    .tp-credits-modal-close {
        position: absolute; top: .75rem; right: .75rem;
        border: none; background: none; font-size: 1.5rem; line-height: 1; color: #94a3b8;
    }
    .tp-credits-modal-icon {
        width: 64px; height: 64px; margin: 0 auto 1rem;
        background: linear-gradient(135deg, #ede9fe, #e0e7ff);
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-size: 2rem; color: #6366f1;
    }
    .tp-credits-modal-box {
        background: #f8fafc; border-radius: 10px; padding: .875rem 1rem;
        border: 1px solid #e8ecf1;
    }
</style>
