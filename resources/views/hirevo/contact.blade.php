@extends('layouts.app')

@section('title', 'Contact')

@section('content')
    <section class="section py-4">
        <div class="container">
            <nav class="mb-3" aria-label="breadcrumb">
                <!-- <ol class="breadcrumb mb-0 fs-14">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Contact</li>
                </ol> -->
            </nav>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="uil uil-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <strong>Please fix the errors below.</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="section-title mt-4 mt-lg-0">
                        <h3 class="title">Get in touch</h3>
                        <p class="text-muted">Reach out for support, partnership, or any questions about Hirevo.</p>
                        <form method="post" action="{{ route('contact.submit') }}" class="contact-form mt-4">
                            @csrf
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label for="nameInput" class="form-label">Name</label>
                                        <input type="text" name="name" id="nameInput" class="form-control" placeholder="Enter your name" value="{{ old('name') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="emailInput" class="form-label">Email</label>
                                        <input type="email" name="email" id="emailInput" class="form-control" placeholder="Enter your email" value="{{ old('email') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="subjectInput" class="form-label">Subject</label>
                                        <input type="text" name="subject" id="subjectInput" class="form-control" placeholder="Enter your subject" value="{{ old('subject') }}">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label for="messageInput" class="form-label">Your Message</label>
                                        <textarea class="form-control" name="message" id="messageInput" placeholder="Enter your message" rows="4">{{ old('message') }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" name="submit" class="btn btn-primary">Send Message <i class="uil uil-message ms-1"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-5 ms-auto order-first order-lg-last">
                    <div class="text-center">
                        <img src="{{ asset($theme.'/assets/images/contact.png') }}" alt="" class="img-fluid" onerror="this.style.display='none'">
                    </div>
                    <div class="mt-4 pt-3">
                        <div class="d-flex text-muted align-items-center mt-2">
                            <div class="flex-shrink-0 fs-22 text-primary">
                                <i class="uil uil-map-marker"></i>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <p class="mb-0">India</p>
                            </div>
                        </div>
                        <div class="d-flex text-muted align-items-center mt-2">
                            <div class="flex-shrink-0 fs-22 text-primary">
                                <i class="uil uil-envelope"></i>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <p class="mb-0">contact@hirevo.com</p>
                            </div>
                        </div>
                        <div class="d-flex text-muted align-items-center mt-2">
                            <div class="flex-shrink-0 fs-22 text-primary">
                                <i class="uil uil-phone-alt"></i>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <p class="mb-0">Reach us via the form below or email</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END CONTACT-PAGE -->
@endsection
