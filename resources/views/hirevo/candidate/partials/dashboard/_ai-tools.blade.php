{{-- AI premium tools — quick access from dashboard overview --}}
@php
    $aiTools = [
        ['route' => 'candidate.assessments', 'icon' => 'mdi-clipboard-check-outline', 'label' => 'Assessments', 'desc' => 'Skill quizzes from your resume'],
        ['route' => 'candidate.mock-interviews', 'icon' => 'mdi-microphone-outline', 'label' => 'Mock Interviews', 'desc' => 'Practice behavioral & technical Qs'],
        ['route' => 'candidate.skill-gaps', 'icon' => 'mdi-chart-timeline-variant', 'label' => 'Skill Gaps', 'desc' => 'See what to learn next'],
        ['route' => 'pricing', 'icon' => 'mdi-school-outline', 'label' => 'Learning Hub', 'desc' => 'Courses & upskill paths'],
        ['route' => 'candidate.job-matches', 'icon' => 'mdi-briefcase-search-outline', 'label' => 'Job Matches', 'desc' => 'Roles ranked for your profile'],
    ];
@endphp

<div class="cd-card cd-card--compact cd-ai-tools cd-ai-tools--section">
    <div class="cd-card-head">
        <h2 class="cd-card-title">AI Career Tools</h2>
        @unless($candidateHasAiTools ?? false)
            <span class="cd-premium-pill"><i class="mdi mdi-crown"></i> Premium</span>
        @endunless
    </div>
    <div class="cd-ai-tools-grid">
        @foreach($aiTools as $tool)
            <x-candidate-premium-gate :feature="$tool['label']" compact>
                <a href="{{ route($tool['route']) }}" class="cd-ai-tool-card" @unless($candidateHasAiTools ?? false) tabindex="-1" @endunless>
                    <span class="cd-ai-tool-icon"><i class="mdi {{ $tool['icon'] }}"></i></span>
                    <strong>{{ $tool['label'] }}</strong>
                    <span class="cd-ai-tool-desc">{{ $tool['desc'] }}</span>
                </a>
            </x-candidate-premium-gate>
        @endforeach
    </div>
</div>
