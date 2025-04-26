<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'mobile' => 'nullable|string|regex:/^07\d{8}$/|size:10',
            'address' => 'nullable|string|max:500',
        ], [
            'mobile.regex' => 'The mobile number must start with 07 and contain only digits.',
            'mobile.size' => 'The mobile number must be exactly 10 digits long.',
        ]);

        $user->update($validated);

        return redirect()->route('profile.show')
            ->with('success', 'Profile updated successfully.');
    }
} 