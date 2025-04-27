@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Apply for Leave</h4>
                </div>

                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <strong>Available Leave Balance:</strong> <span id="leaveBalance">Select a leave type to see balance</span>
                    </div>

                    <form method="POST" action="{{ route('leave.store') }}" id="leaveForm">
                        @csrf

                        <div class="mb-3">
                            <label for="leave_type" class="form-label">Leave Type</label>
                            <select class="form-select @error('leave_type') is-invalid @enderror" 
                                id="leave_type" name="leave_type" required>
                                <option value="">Select Leave Type</option>
                                @php
                                    $groupedTypes = $leaveTypes->groupBy('category')->filter(function($types, $category) {
                                        return !empty($category);
                                    });
                                @endphp
                                @foreach($groupedTypes->sortKeys() as $category => $types)
                                    <optgroup label="{{ $category }}">
                                    @foreach($types->unique('code')->sortBy('name') as $type)
                                        @if(!$type->gender_restriction || strtolower($type->gender_restriction) === strtolower($user->gender))
                                            <option value="{{ $type->code }}" 
                                                data-balance="{{ isset($leaveBalances[$type->code]) ? $leaveBalances[$type->code]['remaining'] : $type->days_per_year }}"
                                                data-total="{{ $type->days_per_year }}"
                                                data-code="{{ $type->code }}"
                                                data-name="{{ $type->name }}"
                                                data-min-notice="{{ $type->minimum_notice_days }}"
                                                data-can-same-day="{{ $type->can_be_applied_same_day }}"
                                                {{ old('leave_type') == $type->code ? 'selected' : '' }}>
                                                {{ $type->name }} ({{ $type->days_per_year }} days/year)
                                            </option>
                                        @endif
                                    @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('leave_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Duration dropdown for Casual Leave -->
                        <div class="mb-3" id="duration_container" style="display: none;">
                            <label for="duration" class="form-label">Duration</label>
                            <select class="form-select @error('duration') is-invalid @enderror" 
                                id="duration" name="duration">
                                <option value="FULL">Full Day</option>
                                <option value="HALF_MORNING">Half Day Morning</option>
                                <option value="HALF_EVENING">Half Day Evening</option>
                            </select>
                            @error('duration')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                        id="start_date" name="start_date" value="{{ old('start_date') }}" 
                                        min="{{ date('Y-m-d') }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                        id="end_date" name="end_date" value="{{ old('end_date') }}" 
                                        min="{{ date('Y-m-d') }}" required>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for Leave</label>
                            <textarea class="form-control @error('reason') is-invalid @enderror" 
                                id="reason" name="reason" rows="3" required>{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
                            <button type="submit" class="btn btn-primary">Submit Application</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Add console log to verify script loading
console.log('Leave application script loaded');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    
    // Get all required elements
    const leaveType = document.getElementById('leave_type');
    const durationContainer = document.getElementById('duration_container');
    const endDateContainer = document.getElementById('end_date').parentElement.parentElement;
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    const duration = document.getElementById('duration');
    const leaveForm = document.getElementById('leaveForm');
    const leaveBalance = document.getElementById('leaveBalance');

    // Debug log to check if elements are found
    console.log('Elements found:', {
        leaveType: !!leaveType,
        durationContainer: !!durationContainer,
        endDateContainer: !!endDateContainer,
        startDate: !!startDate,
        endDate: !!endDate,
        duration: !!duration,
        leaveForm: !!leaveForm,
        leaveBalance: !!leaveBalance
    });

    function updateFormDisplay() {
        console.log('Updating form display');
        const selectedOption = leaveType.options[leaveType.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            console.log('Selected leave type:', selectedOption.value);
            const balance = selectedOption.getAttribute('data-balance');
            const total = selectedOption.getAttribute('data-total');
            const code = selectedOption.getAttribute('data-code');
            const minNotice = parseInt(selectedOption.getAttribute('data-min-notice'));
            const canSameDay = selectedOption.getAttribute('data-can-same-day') === '1';
            
            console.log('Leave type details:', {
                balance,
                total,
                code,
                minNotice,
                canSameDay
            });
            
            // Update leave balance display
            if (code === 'NPL') {
                leaveBalance.innerHTML = 'No-Pay Leave can be applied regardless of balance';
            } else {
                leaveBalance.innerHTML = `${balance} out of ${total} days available`;
            }
            
            // Show/hide duration dropdown for Casual Leave
            if (code === 'CASUAL') {
                console.log('Showing duration container for CASUAL leave');
                durationContainer.style.display = 'block';
                // Hide end date for half-day options
                const durationValue = duration.value;
                endDateContainer.style.display = durationValue === 'FULL' ? 'block' : 'none';
            } else {
                console.log('Hiding duration container for non-CASUAL leave');
                durationContainer.style.display = 'none';
                endDateContainer.style.display = 'block';
            }

            // Set minimum date based on notice period
            const today = new Date();
            const minDate = new Date(today);
            if (!canSameDay) {
                minDate.setDate(today.getDate() + minNotice);
            }
            startDate.min = minDate.toISOString().split('T')[0];
        } else {
            console.log('No leave type selected');
            leaveBalance.innerHTML = 'Select a leave type to see balance';
            durationContainer.style.display = 'none';
            endDateContainer.style.display = 'block';
        }
    }

    // Add event listeners with debug logs
    leaveType.addEventListener('change', function() {
        console.log('Leave type changed');
        updateFormDisplay();
    });

    duration.addEventListener('change', function() {
        console.log('Duration changed:', this.value);
        if (leaveType.value === 'CASUAL') {
            endDateContainer.style.display = this.value === 'FULL' ? 'block' : 'none';
            if (this.value !== 'FULL') {
                endDate.value = startDate.value;
            }
        }
    });

    startDate.addEventListener('change', function() {
        console.log('Start date changed:', this.value);
        if (leaveType.value === 'CASUAL' && duration.value !== 'FULL') {
            endDate.value = this.value;
        }
        endDate.min = this.value;
    });

    leaveForm.addEventListener('submit', function(e) {
        console.log('Form submission attempted');
        const selectedOption = leaveType.options[leaveType.selectedIndex];
        
        if (!selectedOption || !selectedOption.value) {
            e.preventDefault();
            alert('Please select a leave type');
            return;
        }

        const start = new Date(startDate.value);
        const end = new Date(endDate.value);
        const today = new Date();
        const minNotice = parseInt(selectedOption.getAttribute('data-min-notice'));
        const canSameDay = selectedOption.getAttribute('data-can-same-day') === '1';

        console.log('Form validation:', {
            start,
            end,
            minNotice,
            canSameDay
        });

        // Check minimum notice period
        if (!canSameDay) {
            const minDate = new Date(today);
            minDate.setDate(today.getDate() + minNotice);
            if (start < minDate) {
                e.preventDefault();
                alert(`This leave type requires ${minNotice} days notice`);
                return;
            }
        }

        // Check if end date is after start date
        if (end < start) {
            e.preventDefault();
            alert('End date must be after start date');
            return;
        }

        // Check leave balance
        const balance = parseFloat(selectedOption.getAttribute('data-balance'));
        let days = 0;
        
        if (selectedOption.value === 'CASUAL' && duration.value !== 'FULL') {
            days = 0.5;
        } else {
            days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
        }

        console.log('Leave balance check:', {
            balance,
            days,
            leaveType: selectedOption.value
        });

        if (selectedOption.value !== 'NPL' && days > balance) {
            e.preventDefault();
            alert(`Insufficient leave balance. You have ${balance} days available.`);
            return;
        }
    });

    // Initial form display update
    console.log('Running initial form display update');
    updateFormDisplay();
});
</script>
@endpush
@endsection 