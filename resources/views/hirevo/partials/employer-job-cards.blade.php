@foreach($jobs as $job)
    @include('hirevo.partials.employer-job-card', ['job' => $job, 'appliedIds' => $appliedIds ?? [], 'jobMatchScores' => $jobMatchScores ?? []])
@endforeach
