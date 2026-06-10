@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('body_class', 'hirevo-legal-apna-page')

@push('styles')
    @php
        $legalTermsCss = public_path('css/hirevo-legal-terms.css');
        $legalTermsCssVer = is_file($legalTermsCss) ? (string) filemtime($legalTermsCss) : '1';
    @endphp
    <link href="{{ asset('css/hirevo-legal-terms.css') }}?v={{ $legalTermsCssVer }}" rel="stylesheet">
    <style>
        .hirevo-legal-apna--single .hirevo-legal-apna__content {
            max-width: 820px;
        }
    </style>
@endpush

@section('content')
    @php
        $lastUpdated = 'June 9, 2026';
    @endphp

    <div class="hirevo-legal-apna hirevo-legal-apna--single" id="main-content">
        <div class="hirevo-legal-apna__inner">
            <button type="button" class="hirevo-legal-apna__back" onclick="if (window.history.length > 1) { history.back(); } else { window.location.href='{{ route('home') }}'; }">
                ← Go Back
            </button>

            <h1 class="hirevo-legal-apna__page-title">Privacy Policy</h1>

            <article class="hirevo-legal-apna__content">
                <h2 class="hirevo-legal-apna__doc-title">Privacy Policy</h2>
                <hr class="hirevo-legal-apna__rule">
                <div class="hirevo-legal-apna__doc-body">
                    @include('hirevo.legal.partials._privacy-content', ['lastUpdated' => $lastUpdated])
                </div>
            </article>
        </div>
    </div>
@endsection
