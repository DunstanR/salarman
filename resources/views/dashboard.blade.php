@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="card-title mb-1">Welcome, {{ Auth::user()->full_name }}</h2>
                            <p class="text-muted mb-0">Department: {{ Auth::user()->department_name ?? 'Not Assigned' }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </button>
                        </form>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-uppercase mb-1">Profile</h6>
                                            <h3 class="mb-0">View & Edit</h3>
                                        </div>
                                        <i class="fas fa-user fa-2x opacity-50"></i>
                                    </div>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <a href="{{ route('profile.show') }}" class="text-white text-decoration-none">View Details</a>
                                    <i class="fas fa-angle-right"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-4">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-uppercase mb-1">Leave Balance</h6>
                                            <h3 class="mb-0">{{ Auth::user()->leave_balance ?? 0 }} Days</h3>
                                        </div>
                                        <i class="fas fa-calendar-check fa-2x opacity-50"></i>
                                    </div>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <a href="{{ route('leave.balance') }}" class="text-white text-decoration-none">View Details</a>
                                    <i class="fas fa-angle-right"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-4">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-uppercase mb-1">Apply Leave</h6>
                                            <h3 class="mb-0">New Request</h3>
                                        </div>
                                        <i class="fas fa-calendar-plus fa-2x opacity-50"></i>
                                    </div>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <a href="{{ route('leave.apply') }}" class="text-white text-decoration-none">Apply Now</a>
                                    <i class="fas fa-angle-right"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Recent Leave Applications</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Date Applied</th>
                                                    <th>Leave Type</th>
                                                    <th>Duration</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse(Auth::user()->leaveApplications ?? [] as $application)
                                                <tr>
                                                    <td>{{ $application->created_at->format('M d, Y') }}</td>
                                                    <td>{{ $application->leave_type }}</td>
                                                    <td>{{ $application->duration }} days</td>
                                                    <td>
                                                        <span class="badge bg-{{ $application->status_color }}">
                                                            {{ $application->status }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">No leave applications found</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 