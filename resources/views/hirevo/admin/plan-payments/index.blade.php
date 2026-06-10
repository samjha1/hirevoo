<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Employer plan payments — Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width: 1100px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">Employer plan payments</h1>
            <p class="text-muted small mb-0">Approve cheque and net banking payments to activate subscriptions on employer profiles.</p>
        </div>
        <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm">Back to site</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @if($payments->isEmpty())
                <div class="p-4 text-muted">No pending employer subscription payments.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Employer</th>
                                <th>Plan</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Submitted</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                                @php
                                    $meta = $payment->meta ?? [];
                                    $profile = $payment->user?->referrerProfile;
                                @endphp
                                <tr>
                                    <td>#{{ $payment->id }}</td>
                                    <td>
                                        <div class="fw-600">{{ $payment->user?->name ?? '—' }}</div>
                                        <div class="small text-muted">{{ $meta['company_name'] ?? $profile?->company_name ?? $payment->user?->email }}</div>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-primary">{{ $meta['plan_name'] ?? $meta['plan_key'] ?? '—' }}</span>
                                        @if(!empty($meta['job_credits_included']))
                                            <div class="small text-muted">+{{ $meta['job_credits_included'] }} job credits</div>
                                        @endif
                                    </td>
                                    <td>₹{{ number_format((float) $payment->amount, 2) }}</td>
                                    <td>
                                        <div class="small text-muted text-uppercase">{{ str_replace('_', ' ', $payment->payment_gateway) }}</div>
                                        <div>{{ $payment->payment_reference }}</div>
                                        <div class="small text-muted">{{ $meta['cheque_date'] ?? $meta['payment_date'] ?? '' }}</div>
                                    </td>
                                    <td class="small text-muted">{{ $payment->created_at?->format('d M Y, H:i') }}</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('admin.plan-payments.approve', $payment) }}" onsubmit="return confirm('Activate this plan on the employer profile?');">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">Approve &amp; activate</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <p class="text-muted small mt-3 mb-0">
        CLI alternative: <code>php artisan hirevo:complete-payment {payment_id}</code>
    </p>
</div>
</body>
</html>
