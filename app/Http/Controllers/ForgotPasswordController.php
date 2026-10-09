<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetMail;
use App\Models\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Show forgot password form
     */
    public function showForgotForm($guard = 'student')
    {
        $loginRoute = match($guard) {
            'student' => route('student.login'),
            'faculty' => route('faculty.login'),
            default => route('admin.login'),
        };

        return view('forgot-password', [
            'guard' => $guard,
            'loginRoute' => $loginRoute,
        ]);
    }

    /**
     * Send password reset email
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'guard' => 'required|in:student,faculty,web',
        ]);

        $email = $request->email;
        $guard = $request->guard;

        // Find the user based on guard
        $user = $this->findUserByGuard($email, $guard);
        
        if (!$user) {
            return back()->withErrors(['email' => 'No account found with this email address.']);
        }

        // Delete any existing tokens for this email+guard
        PasswordReset::where('email', $email)->where('guard', $guard)->delete();

        // Create new token
        $token = Str::random(64);
        
        PasswordReset::create([
            'email' => $email,
            'guard' => $guard,
            'token' => $token,
            'created_at' => now(),
            'expires_at' => now()->addMinutes(60),
        ]);

        // Send email
        try {
            Mail::to($email)->send(new PasswordResetMail($token, $email, $guard));
        } catch (\Exception $e) {
            \Log::error('Password reset email failed: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Unable to send reset email. Please try again later.']);
        }

        return back()->with('success', 'Password reset link has been sent to your email.');
    }

    /**
     * Show reset password form
     */
    public function showResetForm(Request $request, $token)
    {
        $email = $request->email;
        $guard = $request->guard ?? 'student';

        $loginRoute = match($guard) {
            'student' => route('student.login'),
            'faculty' => route('faculty.login'),
            default => route('admin.login'),
        };

        $reset = PasswordReset::where('email', $email)
            ->where('guard', $guard)
            ->where('token', $token)
            ->first();

        if (!$reset || now()->gt($reset->expires_at)) {
            return redirect()->route('login.selection')
                ->with('error', 'Password reset link has expired. Please request a new one.');
        }

        return view('reset-password', compact('token', 'email', 'guard', 'loginRoute'));
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'guard' => 'required|in:student,faculty,web',
            'password' => 'required|confirmed|min:6',
        ]);

        $reset = PasswordReset::where('email', $request->email)
            ->where('guard', $request->guard)
            ->where('token', $request->token)
            ->first();

        if (!$reset || now()->gt($reset->expires_at)) {
            return back()->with('error', 'Password reset link has expired. Please request a new one.');
        }

        // Update password
        $user = $this->findUserByGuard($request->email, $request->guard);
        
        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Delete used token
        $reset->delete();

        // Redirect to login based on guard
        $loginRoute = match($request->guard) {
            'student' => route('student.login'),
            'faculty' => route('faculty.login'),
            default => route('admin.login'),
        };

        return redirect($loginRoute)->with('success', 'Password has been reset successfully. You can now login.');
    }

    /**
     * Find user by email based on guard type
     */
    private function findUserByGuard($email, $guard)
    {
        return match($guard) {
            'student' => \App\Models\Student::where('email', $email)->first(),
            'faculty' => \App\Models\Faculty::where('email', $email)->first(),
            'web' => \App\Models\User::where('email', $email)->first(),
            default => null,
        };
    }
}