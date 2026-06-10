@extends('layouts.app')

@section('title', 'Terms & Conditions')

@section('body_class', 'hirevo-legal-apna-page')

@section('content')
    @php
        $lastUpdated = 'June 9, 2026';
        $sections = [
            'terms' => [
                'label' => 'Terms & Conditions',
                'title' => 'Terms and Conditions',
                'partial' => 'hirevo.legal.partials._terms-section-terms',
            ],
            'refund' => [
                'label' => 'Refund, Cancellation & Subscription Policy',
                'title' => 'Refund, Cancellation & Subscription Policy',
                'partial' => 'hirevo.legal.partials._terms-section-refund',
            ],
            'cookies' => [
                'label' => 'Cookie Policy',
                'title' => 'Cookie Policy',
                'partial' => 'hirevo.legal.partials._terms-section-cookies',
            ],
            'candidate-privacy' => [
                'label' => 'Data Processing & Candidate Privacy Policy',
                'title' => 'Data Processing & Candidate Privacy Policy',
                'partial' => 'hirevo.legal.partials._terms-section-candidate-privacy',
            ],
        ];
        $activeSection = request('section', 'terms');
        if (! array_key_exists($activeSection, $sections)) {
            $activeSection = 'terms';
        }
        $active = $sections[$activeSection];
    @endphp

    <div class="hirevo-legal-apna">
        <div class="container-fluid custom-container">
            <button type="button" class="hirevo-legal-apna__back" onclick="if (window.history.length > 1) { history.back(); } else { window.location.href='{{ route('home') }}'; }">
                ← Go Back
            </button>

            <div class="hirevo-legal-apna__mobile-nav d-lg-none mb-4">
                <label for="legal-section-select" class="visually-hidden">Select policy section</label>
                <select id="legal-section-select" class="form-select" aria-label="Select policy section">
                    @foreach($sections as $key => $section)
                        <option value="{{ $key }}" @selected($activeSection === $key)>{{ $section['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="hirevo-legal-apna__layout">
                <aside class="hirevo-legal-apna__sidebar d-none d-lg-block" aria-label="Policy sections">
                    <p class="hirevo-legal-apna__nav-heading">Policies</p>
                    <ul class="hirevo-legal-apna__nav">
                        @foreach($sections as $key => $section)
                            <li>
                                <a href="{{ route('terms', ['section' => $key]) }}"
                                   class="{{ $activeSection === $key ? 'is-active' : '' }}"
                                   @if($activeSection === $key) aria-current="page" @endif>
                                    {{ $section['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </aside>

                <article class="hirevo-legal-apna__content">
                    <h1 class="hirevo-legal-apna__page-title">{{ $active['label'] }}</h1>
                    <div class="hirevo-legal-apna__doc-body">
                        @include($active['partial'], ['lastUpdated' => $lastUpdated])
                    </div>
                </article>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var select = document.getElementById('legal-section-select');
    if (!select) return;

    select.addEventListener('change', function () {
        var section = select.value;
        var url = new URL(@json(route('terms')), window.location.origin);
        url.searchParams.set('section', section);
        window.location.href = url.toString();
    });
})();
</script>
@endpush
