@php use App\Support\TalentPoolDisplay; @endphp
<div class="row g-3 mb-4" id="tp-stats">
    <div class="col-6 col-md-4 col-xl">
        <div class="card employer-card h-100 tp-stat-card">
            <div class="card-body p-3">
                <p class="text-muted small mb-1">Total Candidates</p>
                <h4 class="mb-0 fw-700" data-stat="total">{{ number_format(TalentPoolDisplay::count((int) ($stats['total'] ?? 0))) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card employer-card h-100 tp-stat-card">
            <div class="card-body p-3">
                <p class="text-muted small mb-1">Verified Candidates</p>
                <h4 class="mb-0 fw-700 text-success" data-stat="verified">{{ number_format(TalentPoolDisplay::count((int) ($stats['verified'] ?? 0))) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card employer-card h-100 tp-stat-card">
            <div class="card-body p-3">
                <p class="text-muted small mb-1">Talent Pool</p>
                <h4 class="mb-0 fw-700 text-primary" data-stat="talent_pool">{{ number_format(TalentPoolDisplay::count((int) ($stats['talent_pool'] ?? 0))) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card employer-card h-100 tp-stat-card">
            <div class="card-body p-3">
                <p class="text-muted small mb-1">Shortlisted</p>
                <h4 class="mb-0 fw-700" data-stat="shortlisted">{{ number_format(TalentPoolDisplay::count((int) ($stats['shortlisted'] ?? 0))) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card employer-card h-100 tp-stat-card">
            <div class="card-body p-3">
                <p class="text-muted small mb-1">Saved</p>
                <h4 class="mb-0 fw-700" data-stat="saved">{{ number_format(TalentPoolDisplay::count((int) ($stats['saved'] ?? 0))) }}</h4>
            </div>
        </div>
    </div>
</div>
