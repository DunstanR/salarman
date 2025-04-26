<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    public function balance()
    {
        $user = Auth::user();
        return view('leave.balance', compact('user'));
    }

    public function create()
    {
        $user = Auth::user();
        return view('leave.apply', compact('user'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_type' => 'required|string|in:annual,sick,personal',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);

        $user = Auth::user();
        
        // Calculate duration in days
        $start = \Carbon\Carbon::parse($validated['start_date']);
        $end = \Carbon\Carbon::parse($validated['end_date']);
        $duration = $start->diffInDays($end) + 1;

        // Check if user has sufficient leave balance
        if ($duration > $user->leave_balance) {
            return back()->with('error', 'Insufficient leave balance.');
        }

        // Create leave application
        $leave = $user->leaveApplications()->create([
            'leave_type' => $validated['leave_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'duration' => $duration,
            'reason' => $validated['reason'],
            'status' => 'pending'
        ]);

        return redirect()->route('leave.history')
            ->with('success', 'Leave application submitted successfully.');
    }

    public function history()
    {
        $user = Auth::user();
        $applications = $user->leaveApplications()->latest()->get();
        return view('leave.history', compact('applications'));
    }
} 