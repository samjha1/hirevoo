@extends('layouts.candidate')

@section('title', $pageTitle ?? 'Career Tools')

@section('header_greeting')
    <div class="cp-greeting">
        <h1 class="cp-greeting-title">{{ $pageTitle ?? 'Career Tools' }}</h1>
        <p class="cp-greeting-sub">{{ $pageSubtitle ?? '' }}</p>
    </div>
@endsection

@section('header_actions')
    <a href="{{ route('candidate.dashboard') }}" class="cp-btn cp-btn--outline">
        <i class="mdi mdi-arrow-left"></i><span>Dashboard</span>
    </a>
@endsection

@section('content')
<div class="cf-page">
    @if(empty($resume))
        <div class="cf-alert cf-alert--warn">
            <i class="mdi mdi-file-upload-outline me-1"></i>
            Upload your resume to unlock personalized {{ strtolower($pageTitle ?? 'insights') }}.
            <a href="{{ route('resume.upload') }}" class="fw-600 ms-1">Upload resume</a>
        </div>
    @endif

    @yield('feature_content')
</div>
@endsection

@stack('feature_scripts')
