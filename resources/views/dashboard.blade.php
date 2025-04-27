@extends('layouts.app')

@section('content')
<div class="container px-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
                        <div>
                            <h2 class="h4 card-title mb-1">Welcome, {{ Auth::user()->full_name }}</h2>
                            <p class="text-muted small mb-0">Department: {{ Auth::user()->department_name ?? 'Not Assigned' }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </button>
                        </form>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <div class="card bg-primary text-white h-100 shadow-sm">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-uppercase small mb-1">Profile</h6>
                                            <h3 class="h5 mb-0">View & Edit</h3>
                                        </div>
                                        <i class="fas fa-user fa-2x opacity-50"></i>
                                    </div>
                                </div>
                                <div class="card-footer py-2 d-flex align-items-center justify-content-between bg-primary border-top border-light border-opacity-25">
                                    <a href="{{ route('profile.show') }}" class="text-white text-decoration-none small">View Details</a>
                                    <i class="fas fa-angle-right"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="card bg-success text-white h-100 shadow-sm">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-uppercase small mb-1">Leave Balance</h6>
                                            <h3 class="h5 mb-0">{{ Auth::user()->leave_balance ?? 0 }} Days</h3>
                                        </div>
                                        <i class="fas fa-calendar-check fa-2x opacity-50"></i>
                                    </div>
                                </div>
                                <div class="card-footer py-2 d-flex align-items-center justify-content-between bg-success border-top border-light border-opacity-25">
                                    <a href="{{ route('leave.balance') }}" class="text-white text-decoration-none small">View Details</a>
                                    <i class="fas fa-angle-right"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="card bg-info text-white h-100 shadow-sm">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-uppercase small mb-1">Apply Leave</h6>
                                            <h3 class="h5 mb-0">New Request</h3>
                                        </div>
                                        <i class="fas fa-calendar-plus fa-2x opacity-50"></i>
                                    </div>
                                </div>
                                <div class="card-footer py-2 d-flex align-items-center justify-content-between bg-info border-top border-light border-opacity-25">
                                    <a href="{{ route('leave.apply') }}" class="text-white text-decoration-none small">Apply Now</a>
                                    <i class="fas fa-angle-right"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-header py-2 bg-white">
                                    <h5 class="card-title h6 mb-0">Recent Leave Applications</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="py-2">Date</th>
                                                    <th class="py-2">Type</th>
                                                    <th class="py-2">Days</th>
                                                    <th class="py-2">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse(Auth::user()->leaveApplications ?? [] as $application)
                                                <tr>
                                                    <td class="py-2">{{ $application->created_at->format('M d') }}</td>
                                                    <td class="py-2">{{ $application->leave_type }}</td>
                                                    <td class="py-2">{{ $application->duration }}</td>
                                                    <td class="py-2">
                                                        <span class="badge bg-{{ $application->status_color }} rounded-pill">
                                                            {{ $application->status }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="4" class="text-center py-3 text-muted">No leave applications found</td>
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

@push('styles')
<style>
    /* Mobile-first styles */
    @media (max-width: 576px) {
        .container {
            padding-left: 10px;
            padding-right: 10px;
        }
        
        .card {
            border-radius: 12px;
        }
        
        .table th, .table td {
            white-space: nowrap;
            font-size: 0.875rem;
        }
        
        .badge {
            font-size: 0.75rem;
        }
        
        .h4 {
            font-size: 1.25rem;
        }
        
        .h5 {
            font-size: 1.1rem;
        }
        
        .small {
            font-size: 0.8125rem;
        }
    }
</style>
@endpush
@endsection 