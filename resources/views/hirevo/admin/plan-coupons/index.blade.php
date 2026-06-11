<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Plan coupon codes — Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width: 1100px;">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h4 mb-1">Plan coupon codes</h1>
            <p class="text-muted small mb-0">Create discount codes employers can apply during plan checkout.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.plan-payments.index') }}" class="btn btn-outline-secondary btn-sm">Plan payments</a>
            <a href="{{ route('admin.plan-coupons.create') }}" class="btn btn-primary btn-sm">Add coupon</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @if($coupons->isEmpty())
                <div class="p-4 text-muted">No coupon codes yet. <a href="{{ route('admin.plan-coupons.create') }}">Create one</a>.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Discount</th>
                                <th>Status</th>
                                <th>Usage</th>
                                <th>Validity</th>
                                <th>Plans</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($coupons as $coupon)
                                <tr>
                                    <td>
                                        <div class="fw-600">{{ $coupon->code }}</div>
                                        @if($coupon->description)
                                            <div class="small text-muted">{{ $coupon->description }}</div>
                                        @endif
                                    </td>
                                    <td>{{ rtrim(rtrim(number_format((float) $coupon->discount_percent, 2), '0'), '.') }}%</td>
                                    <td>
                                        @if($coupon->isValidNow())
                                            <span class="badge text-bg-success">Active</span>
                                        @elseif($coupon->is_active)
                                            <span class="badge text-bg-warning text-dark">Unavailable</span>
                                        @else
                                            <span class="badge text-bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $coupon->uses_count }}
                                        @if($coupon->max_uses)
                                            / {{ $coupon->max_uses }}
                                        @else
                                            <span class="text-muted">/ ∞</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">
                                        @if($coupon->valid_from || $coupon->valid_until)
                                            {{ $coupon->valid_from?->format('d M Y') ?? '—' }}
                                            →
                                            {{ $coupon->valid_until?->format('d M Y') ?? '—' }}
                                        @else
                                            Always
                                        @endif
                                    </td>
                                    <td class="small">
                                        @if(empty($coupon->applicable_plan_slugs))
                                            <span class="text-muted">All plans</span>
                                        @else
                                            {{ implode(', ', $coupon->applicable_plan_slugs) }}
                                        @endif
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('admin.plan-coupons.edit', $coupon) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.plan-coupons.destroy', $coupon) }}" class="d-inline" onsubmit="return confirm('Delete this coupon code?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
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
</div>
</body>
</html>
