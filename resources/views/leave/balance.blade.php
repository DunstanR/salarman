@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Leave Balance</h4>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-primary text-white mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Annual Leave</h5>
                                    <h2 class="mb-0">{{ $user->leave_balance ?? 0 }} Days</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-info text-white mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Used This Year</h5>
                                    <h2 class="mb-0">{{ $user->used_leave_days ?? 0 }} Days</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">Leave History</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Year</th>
                                            <th>Total Days</th>
                                            <th>Used Days</th>
                                            <th>Remaining</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($user->leaveHistory ?? [] as $history)
                                        <tr>
                                            <td>{{ $history->year }}</td>
                                            <td>{{ $history->total_days }}</td>
                                            <td>{{ $history->used_days }}</td>
                                            <td>{{ $history->remaining_days }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center">No leave history available</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
                        <a href="{{ route('leave.apply') }}" class="btn btn-primary">Apply for Leave</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 