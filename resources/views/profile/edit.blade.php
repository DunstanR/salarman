@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Edit Profile') }}</div>

                <div class="card-body">
                    @if (session('status') === 'profile-updated')
                        <div class="alert alert-success" role="alert">
                            {{ __('Your profile has been updated successfully.') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}" id="profileForm">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="firstname" class="form-label">{{ __('First Name') }}</label>
                                <input type="text" class="form-control @error('firstname') is-invalid @enderror" 
                                    id="firstname" name="firstname" value="{{ old('firstname', $user->firstname) }}" 
                                    placeholder="Enter your first name" required>
                                @error('firstname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="lastname" class="form-label">{{ __('Last Name') }}</label>
                                <input type="text" class="form-control @error('lastname') is-invalid @enderror" 
                                    id="lastname" name="lastname" value="{{ old('lastname', $user->lastname) }}" 
                                    placeholder="Enter your last name" required>
                                @error('lastname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">{{ __('Email Address') }}</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                    id="email" name="email" value="{{ old('email', $user->email) }}" 
                                    placeholder="Enter your email address" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="mobile" class="form-label">{{ __('Mobile Number') }}</label>
                                <input type="tel" class="form-control @error('mobile') is-invalid @enderror" 
                                    id="mobile" name="mobile" value="{{ old('mobile', $user->mobile) }}" 
                                    placeholder="Enter your mobile number (e.g., 0712345678)"
                                    pattern="07[0-9]{8}"
                                    title="Mobile number must start with 07 and be 10 digits long"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                    maxlength="10"
                                    required>
                                <small class="form-text text-muted">Format: 07XXXXXXXX (10 digits, starting with 07)</small>
                                @error('mobile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">{{ __('Address') }}</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                id="address" name="address" rows="3" 
                                placeholder="Enter your address">{{ old('address', $user->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('profile.show') }}" class="btn btn-secondary">{{ __('Back to Profile') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('Update Profile') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileInput = document.getElementById('mobile');
    
    // Prevent non-numeric input
    mobileInput.addEventListener('keypress', function(e) {
        if (!/[0-9]/.test(e.key)) {
            e.preventDefault();
        }
    });

    // Ensure it starts with 07
    mobileInput.addEventListener('input', function(e) {
        let value = this.value.replace(/[^0-9]/g, '');
        if (value.length > 0 && !value.startsWith('07')) {
            value = '07' + value.substring(2);
        }
        this.value = value;
    });

    // Form validation
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        const mobile = mobileInput.value;
        if (mobile && (!mobile.startsWith('07') || mobile.length !== 10)) {
            e.preventDefault();
            alert('Mobile number must start with 07 and be 10 digits long');
        }
    });
});
</script>
@endpush
@endsection 