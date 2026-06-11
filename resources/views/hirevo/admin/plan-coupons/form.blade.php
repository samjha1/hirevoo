@php
    $isEdit = $coupon->exists;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $isEdit ? 'Edit' : 'Create' }} coupon — Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width: 720px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">{{ $isEdit ? 'Edit coupon code' : 'Create coupon code' }}</h1>
            <p class="text-muted small mb-0">Employers can apply this code on the plans checkout screen.</p>
        </div>
        <a href="{{ route('admin.plan-coupons.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ $isEdit ? route('admin.plan-coupons.update', $coupon) : route('admin.plan-coupons.store') }}">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label for="code" class="form-label">Coupon code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control text-uppercase" id="code" name="code" value="{{ old('code', $coupon->code) }}" maxlength="64" required>
                    <div class="form-text">Stored in uppercase. Example: LAUNCH10</div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <input type="text" class="form-control" id="description" name="description" value="{{ old('description', $coupon->description) }}" maxlength="255">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="discount_percent" class="form-label">Discount percent <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="discount_percent" name="discount_percent" value="{{ old('discount_percent', $coupon->discount_percent ?? 10) }}" min="0.01" max="100" step="0.01" required>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="max_uses" class="form-label">Max uses</label>
                        <input type="number" class="form-control" id="max_uses" name="max_uses" value="{{ old('max_uses', $coupon->max_uses) }}" min="1" placeholder="Unlimited">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="valid_from" class="form-label">Valid from</label>
                        <input type="datetime-local" class="form-control" id="valid_from" name="valid_from" value="{{ old('valid_from', optional($coupon->valid_from)->format('Y-m-d\TH:i')) }}">
                    </div>
                    <div class="col-md-6">
                        <label for="valid_until" class="form-label">Valid until</label>
                        <input type="datetime-local" class="form-control" id="valid_until" name="valid_until" value="{{ old('valid_until', optional($coupon->valid_until)->format('Y-m-d\TH:i')) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Applicable plans</label>
                    <div class="form-text mb-2">Leave all unchecked to allow the coupon on every purchasable plan.</div>
                    @php
                        $selectedPlans = old('applicable_plan_slugs', $coupon->applicable_plan_slugs ?? []);
                    @endphp
                    @forelse($plans as $slug => $name)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="applicable_plan_slugs[]" value="{{ $slug }}" id="plan-{{ $slug }}" @checked(in_array($slug, $selectedPlans ?? [], true))>
                            <label class="form-check-label" for="plan-{{ $slug }}">{{ $name }} <span class="text-muted">({{ $slug }})</span></label>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">No active employer plans found.</p>
                    @endforelse
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $coupon->is_active ?? true))>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>

                <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Save changes' : 'Create coupon' }}</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
