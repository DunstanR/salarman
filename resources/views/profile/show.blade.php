@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Profile Information') }}</div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('First Name') }}</label>
                            <p class="form-control-static">{{ $user->firstname }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Last Name') }}</label>
                            <p class="form-control-static">{{ $user->lastname }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Email') }}</label>
                            <p class="form-control-static">{{ $user->email }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Mobile') }}</label>
                            <p class="form-control-static">{{ $user->mobile ?? 'Not provided' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">{{ __('Address') }}</label>
                            <p class="form-control-static">{{ $user->address ?? 'Not provided' }}</p>
                        </div>
                    </div>

                    @if($academicDetails)
                    <div class="card mt-4">
                        <div class="card-header">{{ __('Academic Details') }}</div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label">{{ __('Education') }}</label>
                                    <p class="form-control-static">{{ $academicDetails->education ?? 'Not specified' }}</p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label">{{ __('Service Grade') }}</label>
                                    <p class="form-control-static">
                                        @if(isset($academicDetails->service_grade))
                                            @switch($academicDetails->service_grade)
                                                @case('GRADE_1')
                                                    Grade 1
                                                    @break
                                                @case('GRADE_2')
                                                    Grade 2
                                                    @break
                                                @case('GRADE_3')
                                                    Grade 3
                                                    @break
                                                @default
                                                    {{ $academicDetails->service_grade }}
                                            @endswitch
                                        @else
                                            Not specified
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label">{{ __('Primary Subject') }}</label>
                                    <p class="form-control-static">{{ $academicDetails->primarySubject->name ?? 'Not specified' }}</p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label">{{ __('Secondary Subjects') }}</label>
                                    <p class="form-control-static">
                                        @if($academicDetails->secondarySubjects->isNotEmpty())
                                            {{ $academicDetails->secondarySubjects->pluck('name')->join(', ') }}
                                        @else
                                            No secondary subjects assigned
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('No academic details found. Please contact the administrator to update your academic information.') }}
                    </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('profile.edit') }}" class="btn btn-primary">{{ __('Edit Profile') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 