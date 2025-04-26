<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function sendVerificationCode(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'No account found with this email address.',
            ]);
        }

        // Check if user is a teacher before sending verification code
        if (!$user->isTeacher()) {
            $roleName = $user->role ? $user->role->name : 'Unknown';
            return back()->withErrors([
                'email' => "Access denied. This system is only accessible to teachers. Your account has the role: {$roleName}.",
            ]);
        }

        // Generate a 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Delete any existing unused codes for this email
        VerificationCode::where('email', $request->email)
            ->where('used', false)
            ->delete();
        
        // Create verification code record
        $verificationCode = VerificationCode::create([
            'email' => $request->email,
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes(5),
            'used' => false
        ]);

        // Log the created verification code for debugging
        Log::info('Verification code created', [
            'email' => $request->email,
            'code' => $code,
            'expires_at' => $verificationCode->expires_at
        ]);

        // Send email with verification code
        Mail::send('emails.verification-code', ['code' => $code], function($message) use ($request) {
            $message->to($request->email)
                    ->subject('Your Verification Code');
        });

        // Store email in session with a longer lifetime
        Session::put('verification_email', $request->email);
        Session::put('verification_expires_at', Carbon::now()->addMinutes(5)->timestamp);

        return redirect()->route('verification.notice');
    }

    public function showVerificationForm()
    {
        $email = Session::get('verification_email');
        
        if (!$email) {
            return redirect()->route('login')->withErrors([
                'email' => 'Session expired. Please try again.',
            ]);
        }

        return view('auth.verify', [
            'email' => $email,
            'expires_at' => Session::get('verification_expires_at')
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $email = Session::get('verification_email');
        
        if (!$email) {
            return redirect()->route('login')->withErrors([
                'email' => 'Session expired. Please try again.',
            ]);
        }

        // Log the verification attempt
        Log::info('Verification attempt', [
            'email' => $email,
            'code' => $request->code
        ]);

        $verificationCode = VerificationCode::where('email', $email)
            ->where('code', $request->code)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        // Log the verification code query result
        Log::info('Verification code query result', [
            'found' => $verificationCode ? true : false,
            'code_exists' => $verificationCode ? $verificationCode->code : null,
            'is_used' => $verificationCode ? $verificationCode->used : null,
            'expires_at' => $verificationCode ? $verificationCode->expires_at : null,
            'current_time' => now()
        ]);

        if (!$verificationCode) {
            return back()->withErrors([
                'code' => 'The verification code is invalid or has expired.',
            ]);
        }

        // Mark the code as used
        $verificationCode->update(['used' => true]);

        // Get the user
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return redirect()->route('login')->withErrors([
                'email' => 'User not found.',
            ]);
        }

        // Clear verification session data
        Session::forget(['verification_email', 'verification_expires_at']);
        
        // Regenerate the session ID
        $request->session()->regenerate();
        
        // Log the user in
        Auth::login($user);
        
        // Store user data in session
        Session::put('user_email', $user->email);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
} 