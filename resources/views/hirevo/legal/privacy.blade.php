@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('body_class', 'hirevo-legal-apna-page')

@section('content')
    @php
        $lastUpdated = 'June 9, 2026';
    @endphp

    <div class="hirevo-legal-apna hirevo-legal-apna--single">
        <div class="container-fluid custom-container">
            <button type="button" class="hirevo-legal-apna__back" onclick="if (window.history.length > 1) { history.back(); } else { window.location.href='{{ route('home') }}'; }">
                ← Go Back
            </button>

            <h1 class="hirevo-legal-apna__page-title">Privacy Policy</h1>

            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-9">
                    <article class="hirevo-legal-apna__content">
                        <h2 class="hirevo-legal-apna__doc-title">Privacy Policy</h2>
                        <hr class="hirevo-legal-apna__rule">
                        <div class="hirevo-legal-apna__doc-body">
                            @include('hirevo.legal.partials._privacy-content', ['lastUpdated' => $lastUpdated])
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </div>
@endsection
