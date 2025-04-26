@extends('layouts.app')

@section('content')
<div class="container">
    <div class="auth-container">
        <div class="auth-header">
            <img src="{{ asset('images/logo.png') }}" alt="Logo">
            <h2>Welcome Back</h2>
            <p class="text-muted">Enter your email to receive a verification code</p>
        </div>

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.send-code') }}">
            @csrf
            <div class="mb-4">
                <label for="email" class="form-label">Email address</label>
                <input type="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       id="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
                       autofocus
                       placeholder="Enter your email">
                @error('email')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-paper-plane me-2"></i>Send Verification Code
            </button>
        </form>
    </div>
</div>
@endsection 