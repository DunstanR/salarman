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

        // Generate a 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Create verification code record
        VerificationCode::create([
            'email' => $request->email,
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes(5),
            'used' => false
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

        $verificationCode = VerificationCode::where('email', $email)
            ->where('code', $request->code)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

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