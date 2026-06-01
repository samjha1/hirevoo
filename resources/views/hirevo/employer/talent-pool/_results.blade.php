@if(!empty($requiresSearch))
    <div class="card employer-card tp-empty-state">
        <div class="card-body text-center py-5">
            <i class="mdi mdi-magnify text-muted tp-empty-icon"></i>
            <h5 class="mt-3 fw-600">Start your search</h5>
            <p class="text-muted mb-3">Add keywords, skills, or filters to find matching candidates.</p>
            <a href="{{ route('employer.talent-pool.index') }}" class="btn btn-primary btn-sm">Modify search</a>
        </div>
    </div>
@elseif($candidates->isEmpty())
    <div class="card employer-card tp-empty-state">
        <div class="card-body text-center py-5">
            <i class="mdi mdi-account-search-outline text-muted tp-empty-icon"></i>
            <h5 class="mt-3 fw-600">No candidates found</h5>
            <p class="text-muted mb-3">Try adjusting your search or filters.</p>
            <a href="{{ route('employer.talent-pool.index') }}" class="btn btn-outline-primary btn-sm">New search</a>
        </div>
    </div>
@else
    @if(isset($paginator))
        @include('hirevo.employer.talent-pool._toolbar', ['paginator' => $paginator, 'perPage' => $perPage ?? 20])
    @endif

    <div class="tp-results-list">
        @foreach($candidates as $candidate)
            @include('hirevo.employer.talent-pool._candidate-list-row', [
                'candidate' => $candidate,
                'matchingSkills' => $matchingSkills ?? [],
                'canAccessTalentPool' => $canAccessTalentPool ?? false,
            ])
        @endforeach
    </div>

    @if(isset($paginator) && ($paginator->hasMorePages() || $paginator->currentPage() > 1))
        <div class="mt-3">
            @include('hirevo.employer.talent-pool._toolbar', ['paginator' => $paginator, 'perPage' => $perPage ?? 20])
        </div>
    @endif
@endif
