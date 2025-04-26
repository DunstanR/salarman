@extends('layouts.app')

@section('content')
<div class="container">
    <div class="auth-container">
        <div class="auth-header">
            <img src="{{ asset('images/logo.png') }}" alt="Logo">
            <h2>Enter Verification Code</h2>
            <p class="text-muted">We've sent a code to {{ $email }}</p>
        </div>

        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            Please enter the 6-digit code sent to your email.
        </div>

        <form method="POST" action="{{ route('verification.verify') }}" id="verificationForm">
            @csrf
            <div class="mb-3">
                <label for="code" class="form-label">Verification Code</label>
                <input type="text" 
                       class="form-control form-control-lg text-center" 
                       id="code" 
                       name="code" 
                       maxlength="6" 
                       pattern="\d{6}" 
                       required 
                       autocomplete="off"
                       style="letter-spacing: 0.5em; font-size: 1.5em;"
                       placeholder="000000">
                @error('code')
                    <div class="invalid-feedback d-block">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Verify Code
            </button>

            <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="text-decoration-none">Back to Login</a>
            </div>
        </form>
    </div>
</div>

<script>
    // Auto-submit when 6 digits are entered
    const codeInput = document.querySelector('input[name="code"]');
    codeInput.addEventListener('input', function() {
        if (this.value.length === 6) {
            document.getElementById('verificationForm').submit();
        }
    });
</script>
@endsection 