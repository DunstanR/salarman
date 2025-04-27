<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LeaveType;
use App\Models\LeaveApplication;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use MongoDB\BSON\UTCDateTime;
use Illuminate\Support\Facades\Log;

class LeaveController extends Controller
{
    public function balance()
    {
        $user = Auth::user();
        $leaveBalances = $user->getAllLeaveBalances();
        return view('leave.balance', compact('user', 'leaveBalances'));
    }

    public function create()
    {
        $user = Auth::user();
        $leaveTypes = LeaveType::all();
        $leaveBalances = $user->getAllLeaveBalances();
        
        return view('leave.apply', compact('user', 'leaveTypes', 'leaveBalances'));
    }

    public function store(Request $request)
    {
        Log::info('Starting leave application process', ['request' => $request->all()]);
        
        // Get all leave types from MongoDB
        $leaveTypes = LeaveType::all();
        $validLeaveTypeCodes = $leaveTypes->pluck('code')->toArray();
        Log::info('Retrieved leave types', ['types' => $validLeaveTypeCodes]);

        $request->validate([
            'leave_type' => ['required', 'string', Rule::in($validLeaveTypeCodes)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:500'],
            'duration' => ['required_if:leave_type,CASUAL', Rule::in(['FULL', 'HALF_MORNING', 'HALF_EVENING', 'full', 'half_morning', 'half_evening'])],
            'document' => ['required_if:leave_type,SICK', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ], [
            'leave_type.required' => 'Please select a leave type',
            'leave_type.in' => 'Invalid leave type selected',
            'start_date.required' => 'Please select a start date',
            'end_date.required' => 'Please select an end date',
            'end_date.after_or_equal' => 'End date must be after or equal to start date',
            'reason.required' => 'Please provide a reason for your leave',
            'reason.max' => 'Reason cannot exceed 500 characters',
            'duration.required_if' => 'Please select duration for casual leave',
            'duration.in' => 'Invalid duration selected. Please choose Full Day, Half Day (Morning), or Half Day (Evening)',
            'document.required_if' => 'Please upload a medical certificate for sick leave',
            'document.mimes' => 'Document must be a PDF, JPG, JPEG, or PNG file',
            'document.max' => 'Document size cannot exceed 2MB',
        ]);

        Log::info('Validation passed');

        $user = Auth::user();
        Log::info('User retrieved', ['user_id' => $user->_id]);
        
        $leaveType = LeaveType::where('code', $request->leave_type)->first();
        Log::info('Leave type retrieved', ['leave_type' => $leaveType ? $leaveType->toArray() : null]);
        
        // Check if user can apply for this leave
        if (!$user->canApplyForLeave($request->leave_type, Carbon::parse($request->start_date), Carbon::parse($request->end_date))) {
            Log::warning('User cannot apply for leave', [
                'user_id' => $user->_id,
                'leave_type' => $request->leave_type,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ]);
            return back()->with('error', 'You cannot apply for this leave at this time.');
        }

        // Check if document is required
        if ($leaveType->requires_document && !$request->hasFile('document')) {
            Log::warning('Document required but not provided', [
                'leave_type' => $request->leave_type,
                'requires_document' => $leaveType->requires_document
            ]);
            return back()->with('error', 'A document is required for this leave type.');
        }

        // Handle document upload
        $documentUrl = null;
        if ($request->hasFile('document')) {
            try {
                $path = $request->file('document')->store('leave-documents');
                $documentUrl = Storage::url($path);
                Log::info('Document uploaded successfully', ['path' => $path, 'url' => $documentUrl]);
            } catch (\Exception $e) {
                Log::error('Document upload failed', ['error' => $e->getMessage()]);
                return back()->with('error', 'Failed to upload document. Please try again.');
            }
        }

        // Calculate leave days
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $leaveDays = $startDate->diffInDays($endDate) + 1;

        // For half-day leaves, adjust the days
        if ($request->leave_type === 'CASUAL' && $request->duration !== 'FULL') {
            $leaveDays = 0.5;
        }

        Log::info('Preparing to create leave application', [
            'user_id' => $user->_id,
            'leave_type' => $request->leave_type,
            'start_date' => $startDate->toDateTimeString(),
            'end_date' => $endDate->toDateTimeString(),
            'duration' => $request->leave_type === 'CASUAL' ? $request->duration : 'FULL',
            'leave_days' => $leaveDays
        ]);

        try {
            // Create leave application
            $leave = new LeaveApplication([
                'user_id' => $user->_id,
                'leave_type' => $leaveType->_id,
                'start_date' => new UTCDateTime($startDate->timestamp * 1000),
                'end_date' => new UTCDateTime($endDate->timestamp * 1000),
                'duration' => $request->leave_type === 'CASUAL' ? $request->duration : 'FULL',
                'reason' => $request->reason,
                'status' => 'pending',
                'document_url' => $documentUrl,
                'document_type' => $leaveType->document_type,
                'document_number' => $request->document_number ?? null,
                'document_date' => isset($request->document_date) ? new UTCDateTime(Carbon::parse($request->document_date)->timestamp * 1000) : null,
                'document_issuer' => $request->document_issuer ?? null,
                'document_expiry_date' => isset($request->document_expiry_date) ? new UTCDateTime(Carbon::parse($request->document_expiry_date)->timestamp * 1000) : null
            ]);

            $leave->save();
            Log::info('Leave application created successfully', ['leave_id' => $leave->_id]);
            
            return redirect()->route('leave.history')
                ->with('success', 'Leave application submitted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create leave application', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to submit leave application. Please try again.');
        }
    }

    public function history()
    {
        $user = Auth::user();
        $applications = $user->leaveApplications()
            ->with('leaveType')
            ->latest()
            ->get();
        return view('leave.history', compact('applications'));
    }

    public function approve(LeaveApplication $application)
    {
        if (!$application->canBeApproved()) {
            return back()->with('error', 'This leave application cannot be approved.');
        }

        $application->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        return back()->with('success', 'Leave application approved successfully.');
    }

    public function reject(Request $request, LeaveApplication $application)
    {
        if (!$application->canBeRejected()) {
            return back()->with('error', 'This leave application cannot be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $application->update([
            'status' => 'rejected',
            'rejected_by' => Auth::id(),
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason']
        ]);

        return back()->with('success', 'Leave application rejected successfully.');
    }

    public function verifyDocument(Request $request, LeaveApplication $application)
    {
        if (!$application->canBeVerified()) {
            return back()->with('error', 'This document cannot be verified.');
        }

        $validated = $request->validate([
            'verification_notes' => 'required|string|max:500'
        ]);

        $application->update([
            'document_verified' => true,
            'document_verified_by' => Auth::id(),
            'document_verified_at' => now(),
            'document_verification_notes' => $validated['verification_notes']
        ]);

        return back()->with('success', 'Document verified successfully.');
    }
} 