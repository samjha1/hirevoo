<div class="cd-page-back">
    <a href="{{ route('candidate.dashboard') }}" class="cd-link"><i class="mdi mdi-arrow-left"></i> Back to Dashboard</a>
</div>

<div class="cd-section-head">
    <div>
        <p class="cd-section-kicker">Career intelligence</p>
        <h2 class="cd-section-title">My Hiring Score</h2>
        <p class="cd-section-sub">Overall score, breakdown, resume ATS analysis, and role skill match — all from your saved data.</p>
    </div>
    @if($primaryResume)
        <a href="{{ route('candidate.resume.review') }}" class="cd-link">Open resume review →</a>
    @endif
</div>

<div class="cd-grid cd-grid--top">
    <div class="cd-card cd-card--score">
        <div class="cd-card-head"><h2 class="cd-card-title">Overall Hiring Score</h2></div>
        <div class="cd-score-body">
            <div class="cd-score-ring-wrap">
                <svg class="cd-score-ring" viewBox="0 0 120 120" aria-hidden="true">
                    <circle class="cd-score-ring-track" cx="60" cy="60" r="54"/>
                    <circle class="cd-score-ring-fill" cx="60" cy="60" r="54"
                            stroke-dasharray="{{ $scoreCirc }}" stroke-dashoffset="{{ $scoreOffset }}"/>
                </svg>
                <div class="cd-score-ring-center">
                    <span class="cd-score-num">{{ $hiringScore }}<small>/100</small></span>
                    <span class="cd-score-badge">{{ $hiringScoreLabel }}</span>
                </div>
            </div>
            <div class="cd-score-meta">
                <p>You are better than <strong>{{ $scorePercentile }}%</strong> of candidates</p>
            </div>
        </div>
    </div>
    <div class="cd-card cd-card--trend">
        <div class="cd-card-head">
            <h2 class="cd-card-title">Score Trend</h2>
            <span class="cd-card-meta">Last 6 Months</span>
        </div>
        <div class="cd-trend-chart-wrap">
            <canvas id="cdScoreTrendChartFull" height="180" aria-label="Score trend chart"></canvas>
        </div>
    </div>
    <div class="cd-card cd-card--next">
        <div class="cd-next-icon" aria-hidden="true"><i class="mdi mdi-target"></i></div>
        <p class="cd-next-label">Recommended Next Step</p>
        <h3 class="cd-next-title">{{ $nextStep['title'] }}</h3>
        <p class="cd-next-desc">{{ $nextStep['description'] }}</p>
        <a href="{{ $nextStep['url'] }}" class="cd-btn cd-btn--primary">{{ $nextStep['label'] }}</a>
    </div>
</div>

<div class="cd-grid cd-grid--middle">
    <div class="cd-card">
        <div class="cd-card-head">
            <h2 class="cd-card-title">Hiring Score Breakdown</h2>
            <span class="cd-card-meta">6 dimensions</span>
        </div>
        <ul class="cd-breakdown-list">
            @forelse($scoreBreakdown as $item)
                <li class="cd-breakdown-item">
                    <span class="cd-breakdown-icon"><i class="mdi {{ $item['icon'] }}"></i></span>
                    <div class="cd-breakdown-body">
                        <div class="cd-breakdown-row">
                            <span>{{ $item['label'] }}</span>
                            <strong>{{ $item['score'] }}/100</strong>
                        </div>
                        <div class="cd-progress"><span style="width: {{ $item['score'] }}%"></span></div>
                    </div>
                </li>
            @empty
                <li class="cd-empty-text">Complete your profile and upload a resume to see breakdown.</li>
            @endforelse
        </ul>
    </div>
    <div class="cd-card">
        <div class="cd-card-head"><h2 class="cd-card-title">How Your Score Is Built</h2></div>
        <ul class="cd-factor-list">
            @foreach($hsFactors as $factor)
                <li class="cd-factor-item">
                    <div class="cd-factor-top">
                        <span>{{ $factor['label'] }}</span>
                        <strong>{{ $factor['display'] }}</strong>
                    </div>
                    <div class="cd-factor-meta">
                        <span class="cd-factor-weight">{{ $factor['weight'] }}</span>
                        <span class="cd-factor-detail">{{ $factor['detail'] }}</span>
                    </div>
                    @if(isset($factor['value']) && is_numeric($factor['value']) && ($factor['weight'] ?? '') !== 'Bonus')
                        <div class="cd-progress cd-progress--sm"><span style="width: {{ min(100, (int) $factor['value']) }}%"></span></div>
                    @endif
                </li>
            @endforeach
        </ul>
        @if($hsAvgJobMatch !== null)
            <p class="cd-factor-foot mb-0"><i class="mdi mdi-briefcase-outline"></i> Average job match: <strong>{{ $hsAvgJobMatch }}%</strong></p>
        @endif
    </div>
</div>

<div class="cd-grid cd-grid--middle">
    <div class="cd-card cd-card--resume-detail">
        <div class="cd-card-head">
            <h2 class="cd-card-title">Resume Analysis</h2>
            @if($hsResume)<span class="cd-card-meta">{{ $hsResume['file_name'] ?? 'Resume' }}</span>@endif
        </div>
        @if($hsResume)
            @php
                $atsScore = (int) ($hsResume['ai_score'] ?? 0);
                $atsBand = $atsScore >= 70 ? 'high' : ($atsScore >= 50 ? 'mid' : 'low');
                $hsSkills = $hsResume['skills'] ?? [];
            @endphp
            <div class="cd-resume-score-row">
                <div class="cd-resume-ats-pill cd-resume-ats-pill--{{ $atsBand }}">
                    <span class="cd-resume-ats-num">{{ $hsResume['ai_score'] !== null ? $atsScore : '—' }}</span>
                    <span class="cd-resume-ats-lbl">ATS / 100</span>
                </div>
                <div class="cd-resume-score-text">
                    @if(filled($hsResume['ai_score_explanation']))<p>{{ $hsResume['ai_score_explanation'] }}</p>@endif
                    @if(! empty($hsResume['analyzed_at']))
                        <span class="cd-card-meta">Last analysed {{ $hsResume['analyzed_at']->diffForHumans() }}</span>
                    @endif
                </div>
            </div>
            @if(filled($hsResume['ai_summary']))
                <div class="cd-resume-summary">
                    <p class="cd-detail-label">Summary</p>
                    <p>{{ $hsResume['ai_summary'] }}</p>
                </div>
            @endif
            @if(count($hsSkills) > 0)
                <div class="cd-resume-skills">
                    <p class="cd-detail-label">Extracted skills ({{ count($hsSkills) }})</p>
                    <div class="cd-skill-chips">
                        @foreach($hsSkills as $sk)
                            @if(is_string($sk) && trim($sk) !== '')<span class="cd-skill-chip">{{ $sk }}</span>@endif
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="cd-resume-actions">
                <a href="{{ route('candidate.resume.review') }}" class="cd-btn cd-btn--primary cd-btn--sm">Full resume review</a>
                @if($primaryResume)
                    <a href="{{ route('resume.results', $primaryResume) }}" class="cd-link">Matched jobs →</a>
                @endif
            </div>
        @else
            <p class="cd-empty-text">No resume on file.</p>
            <a href="{{ route('resume.upload') }}" class="cd-btn cd-btn--primary cd-btn--sm">Upload Resume</a>
        @endif
    </div>

    <div class="cd-card cd-card--role-detail">
        <div class="cd-card-head">
            <h2 class="cd-card-title">Target Role Skill Match</h2>
            @if($hsRoleMatch && ($hsRoleMatch['match_pct'] ?? null) !== null)
                <span class="cd-match-pill">{{ $hsRoleMatch['match_pct'] }}% match</span>
            @endif
        </div>
        @if($hsRoleMatch && $hsRoleMatch['role'])
            <h3 class="cd-role-title">{{ $hsRoleMatch['role']->title }}</h3>
            @if(! empty($hsRoleMatch['source']))<p class="cd-role-source">{{ $hsRoleMatch['source'] }}</p>@endif
            @if(count($hsRoleMatch['matched_skills'] ?? []) > 0)
                <div class="cd-skill-block cd-skill-block--match">
                    <p class="cd-detail-label">Matched skills</p>
                    <div class="cd-skill-chips">
                        @foreach($hsRoleMatch['matched_skills'] as $sk)<span class="cd-skill-chip cd-skill-chip--match">{{ $sk }}</span>@endforeach
                    </div>
                </div>
            @endif
            @if(count($hsRoleMatch['gap_skills'] ?? []) > 0)
                <div class="cd-skill-block cd-skill-block--gap">
                    <p class="cd-detail-label">Skill gaps</p>
                    <div class="cd-skill-chips">
                        @foreach($hsRoleMatch['gap_skills'] as $sk)<span class="cd-skill-chip cd-skill-chip--gap">{{ $sk }}</span>@endforeach
                    </div>
                </div>
            @endif
            <a href="{{ route('job-goal.show', $hsRoleMatch['role']) }}" class="cd-btn cd-btn--primary cd-btn--sm">View full skill report</a>
        @else
            <p class="cd-empty-text">Pick a job goal to see skill alignment.</p>
            <a href="{{ route('job-list') }}" class="cd-btn cd-btn--primary cd-btn--sm">Explore job goals</a>
        @endif
    </div>

    <div class="cd-card">
        <div class="cd-card-head"><h2 class="cd-card-title">Top Skill Gaps</h2></div>
        <div class="cd-gap-legend">
            <span><i class="cd-legend-dot cd-legend-dot--current"></i> Current</span>
            <span><i class="cd-legend-dot cd-legend-dot--rec"></i> Recommended</span>
        </div>
        <div class="cd-gap-list">
            @foreach($skillGapChart as $gap)
                <div class="cd-gap-item">
                    <div class="cd-gap-label">{{ $gap['skill'] }}</div>
                    <div class="cd-gap-bars">
                        <div class="cd-gap-bar cd-gap-bar--current" style="width: {{ $gap['current'] }}%"></div>
                        <div class="cd-gap-bar cd-gap-bar--rec" style="width: {{ $gap['recommended'] }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="cd-stats-strip">
    <div class="cd-stat-box"><span class="cd-stat-num">{{ $dashboardStats['total_apps'] ?? 0 }}</span><span class="cd-stat-lbl">Applications</span></div>
    <div class="cd-stat-box"><span class="cd-stat-num">{{ $dashboardStats['active_reviews'] ?? 0 }}</span><span class="cd-stat-lbl">In progress</span></div>
    <div class="cd-stat-box"><span class="cd-stat-num">{{ $dashboardStats['hired_count'] ?? 0 }}</span><span class="cd-stat-lbl">Hired</span></div>
    <div class="cd-stat-box"><span class="cd-stat-num">{{ $dashboardStats['avg_match'] ? round($dashboardStats['avg_match']).'%' : '—' }}</span><span class="cd-stat-lbl">Avg match</span></div>
</div>
