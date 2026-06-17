@extends('layouts.guest')

@section('content')
<div class="login-card shadow-lg">
    <div class="banner border-bottom bg-white text-center p-4">
        <h1 class="ispsc-logo">ISPSC</h1>
        <p class="onwards-text">ONWARDS UIP</p>
        <small class="text-muted text-uppercase fw-bold" style="letter-spacing: 1px;">Document Tracking System</small>
    </div>

    <div class="p-4">
        <!-- Display Validation Errors (Standard Laravel) -->
        @if ($errors->any())
            <div class="alert alert-danger py-2 small">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="mb-3">
                <label class="form-label small fw-bold">Username / Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fa fa-user text-muted"></i></span>
                    <input type="text" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fa fa-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control" required>
                </div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label small" for="remember">Remember Me</label>
            </div>

            <button type="submit" class="btn btn-maroon w-100 shadow-sm">
                SIGN IN <i class="fa fa-sign-in-alt ms-2"></i>
            </button>

        </form>
    </div>
@endsection