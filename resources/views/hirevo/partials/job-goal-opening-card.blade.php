@php
    $roleInitial = strtoupper(mb_substr($role->title, 0, 1));
    $openCount = $role->displayOpenRolesCount();
    $referralPct = $role->displayReferralBoostPct();
@endphp
<div class="jo-job-card-wrap">
    <article class="card border-0 jo-job-card jo-job-card--goal">
        <div class="card-body p-3 p-md-4">
            <div class="jo-job-card-layout">
                <div class="jo-job-card__brand">
                    <div class="jo-co-avatar jo-co-avatar--goal" aria-hidden="true">{{ $roleInitial }}</div>
                </div>

                <div class="jo-job-card__content">
                    <header class="jo-job-card__head">
                        <h2 class="h5 mb-1">
                            <a href="{{ route('job-goal.show', $role) }}" class="jo-job-title d-block text-dark text-decoration-none">{{ $role->title }}</a>
                        </h2>
                        <p class="jo-job-card__company">Job goal · {{ number_format($openCount) }} open roles</p>
                    </header>

                    <div class="jo-job-card__tags">
                        <span class="jo-meta-pill jo-meta-pill--accent">Career path</span>
                        @if($role->sector)
                            <span class="jo-meta-pill">{{ str_replace('_', ' ', ucfirst($role->sector)) }}</span>
                        @endif
                    </div>

                    <p class="text-muted mb-0 small lh-base jo-job-desc-clamp">{{ Str::limit(strip_tags($role->description ?? ''), 180) ?: '—' }}</p>
                </div>

                <div class="jo-job-card__actions">
                    @if(in_array($role->id, $appliedGoalIds ?? []))
                        <span class="badge bg-success rounded-pill px-3 py-2 align-self-lg-end">Applied</span>
                    @else
                        <a href="{{ route('job-goal.apply', ['jobRole' => $role, 'return_to' => 'job-openings']) }}" class="btn btn-primary btn-sm rounded-pill jo-apply-btn d-inline-flex align-items-center justify-content-center">Apply now</a>
                        <a href="{{ route('job-goal.show', $role) }}" class="btn btn-outline-secondary btn-sm rounded-pill d-inline-flex align-items-center justify-content-center">Skill match</a>
                        <a href="{{ route('referral.intent', ['source' => 'job_openings', 'job_role_id' => $role->id]) }}" class="jo-referral-nudge" role="note">
                            <span class="jo-referral-nudge__icon" aria-hidden="true"><i class="uil uil-gift"></i></span>
                            <span class="jo-referral-nudge__text">
                                <span class="jo-referral-nudge__label">Get referral</span>
                                <span class="jo-referral-nudge__stat">Up to <strong>+{{ $referralPct }}%</strong> better odds to get hired</span>
                            </span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </article>
</div>
