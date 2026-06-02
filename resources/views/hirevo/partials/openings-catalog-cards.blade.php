@foreach($jobs as $entry)
    @if(is_array($entry) && ($entry['type'] ?? '') === 'goal')
        @include('hirevo.partials.job-goal-opening-card', [
            'role' => $entry['model'],
            'appliedGoalIds' => $appliedGoalIds ?? [],
        ])
    @elseif(is_array($entry) && ($entry['type'] ?? '') === 'employer')
        @include('hirevo.partials.employer-job-card', [
            'job' => $entry['model'],
            'appliedIds' => $appliedIds ?? [],
            'jobMatchScores' => $jobMatchScores ?? [],
        ])
    @else
        @include('hirevo.partials.employer-job-card', [
            'job' => $entry,
            'appliedIds' => $appliedIds ?? [],
            'jobMatchScores' => $jobMatchScores ?? [],
        ])
    @endif
@endforeach
