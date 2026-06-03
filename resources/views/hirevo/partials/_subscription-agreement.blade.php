@php
    $planName = (string) ($plan['name'] ?? 'Subscription plan');
    $acceptedAt = (string) ($payment->meta['agreement_accepted_at'] ?? now()->toIso8601String());
@endphp
<div class="subscription-agreement">
    <p><strong>Hirevo Employer Subscription Agreement</strong></p>
    <p>
        This agreement is between <strong>Hirevoo Pvt. Ltd.</strong> ("Hirevo") and
        <strong>{{ $profile->company_name ?? 'Employer' }}</strong> ("Customer") for the
        <strong>{{ $planName }}</strong> plan.
    </p>

    <p><strong>1. Subscription &amp; access</strong></p>
    <p>
        Subscription fees provide access to Hirevo platform features for the selected billing period.
        Payment is for access, not for any guaranteed hiring outcome, placement, or candidate response.
    </p>

    <p><strong>2. Fees &amp; payment</strong></p>
    <p>
        Base amount: ₹{{ number_format((float) ($amounts['base_amount'] ?? 0), 2) }}.
        GST ({{ number_format((float) ($amounts['gst_rate'] ?? 18), 0) }}%):
        ₹{{ number_format((float) ($amounts['gst_amount'] ?? 0), 2) }}.
        Total payable: ₹{{ number_format((float) ($amounts['total_amount'] ?? 0), 2) }} (INR).
        Payment method: cheque #{{ $chequeNumber }} dated {{ \Illuminate\Support\Carbon::parse($chequeDate)->format('d M Y') }}.
    </p>
    <p>
        Subscription activation will occur after cheque verification and clearance.
        Fees may be non-refundable except where required by applicable law or approved by Hirevo policy.
    </p>

    <p><strong>3. Customer obligations</strong></p>
    <p>
        Customer will use the platform lawfully, provide accurate company information, and comply with
        Hirevo Terms &amp; Conditions, Privacy Policy, and acceptable use rules.
    </p>

    <p><strong>4. Term &amp; suspension</strong></p>
    <p>
        Access continues for the subscribed period once activated. Hirevo may suspend or terminate access
        for fraud, abuse, non-payment, or violation of platform policies.
    </p>

    <p><strong>5. Acknowledgment</strong></p>
    <p>
        By checking the agreement box, Customer confirms acceptance of this subscription agreement and
        Hirevo's Terms &amp; Conditions (Section 8 — Payments and Subscriptions).
    </p>

    <p class="small text-muted mb-0">
        Accepted by: {{ $user->name }} ({{ $user->email }}) on {{ \Illuminate\Support\Carbon::parse($acceptedAt)->format('d M Y, h:i A T') }}.
        Payment reference: #{{ $payment->id }}.
    </p>
</div>
