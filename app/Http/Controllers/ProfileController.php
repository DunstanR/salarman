<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\EmployeeAcademic;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $academicDetails = EmployeeAcademic::where('employeeRef', $user->_id)
            ->with(['primarySubject', 'secondarySubjects', 'section'])
            ->first();

        return view('profile.edit', [
            'user' => $user,
            'academicDetails' => $academicDetails
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Display the user's profile.
     */
    public function show(Request $request): View
    {
        $user = $request->user();
        
        // Debug information
        \Log::info('User ID: ' . $user->_id);
        
        // Find academic details
        $academicDetails = EmployeeAcademic::where('employeeRef', $user->_id)
            ->with(['primarySubject', 'secondarySubjects', 'section'])
            ->first();
            
        // Debug information
        if ($academicDetails) {
            \Log::info('Academic details found: ' . json_encode($academicDetails->toArray()));
            
            // Check if primary subject exists
            if ($academicDetails->primarySubject) {
                \Log::info('Primary subject found: ' . $academicDetails->primarySubject->name);
            } else {
                \Log::info('Primary subject not found');
            }
            
            // Check if service_grade exists
            if (isset($academicDetails->service_grade)) {
                \Log::info('Service grade: ' . $academicDetails->service_grade);
            } else {
                \Log::info('Service grade not set');
            }
        } else {
            \Log::info('No academic details found for user: ' . $user->_id);
            
            // Try to create a new academic record for testing
            try {
                $newAcademic = new EmployeeAcademic();
                $newAcademic->employeeRef = $user->_id;
                $newAcademic->education = 'BSc. Physical Science, University of Sri Jayawardenapura';
                $newAcademic->service_grade = 'GRADE_1';
                $newAcademic->save();
                
                \Log::info('Created new academic record for testing');
                $academicDetails = $newAcademic;
            } catch (\Exception $e) {
                \Log::error('Error creating academic record: ' . $e->getMessage());
            }
        }

        return view('profile.show', [
            'user' => $user,
            'academicDetails' => $academicDetails
        ]);
    }
} 