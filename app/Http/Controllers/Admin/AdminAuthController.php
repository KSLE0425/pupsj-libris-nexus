<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminOtpMail;
use App\Models\KioskOtpCode;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Verify credentials without logging in yet
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            AuditLogger::log('auth_failed_admin', 'Failed admin login attempt for ' . $credentials['email'], [
                'email' => $credentials['email'],
                'ip'    => $request->ip(),
            ], 'auth', 'guest');

            return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
        }

        if ($user->role !== 'admin') {
            return back()->withErrors(['email' => 'You do not have admin access.'])->withInput();
        }

        // Feature Flag: OTP Verification (temporarily disabled per requirement, set to true to re-enable)
        $otpEnabled = false;

        if ($otpEnabled) {
            // Credentials are valid — generate and send OTP
            $this->sendOtp($user);

            // Store pending login in session (not logged in yet)
            $request->session()->put('admin_pending_login', $user->id);

            return redirect()->route('admin.otp.form');
        }

        // Direct login without OTP
        Auth::guard('web')->login($user, true);
        $request->session()->regenerate();

        AuditLogger::log('auth_login_admin', 'Administrator ' . $user->name . ' logged into the admin portal', [
            'admin_id'       => $user->id,
            'performer_name' => $user->name,
            'email'          => $user->email,
        ], 'auth', 'admin', $user->id);

        return redirect()->intended('/dashboard');
    }

    public function showOtpForm()
    {
        if (!session('admin_pending_login')) {
            return redirect()->route('admin.login');
        }
        return view('admin.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp_code' => 'required|string|size:6']);

        $userId = $request->session()->get('admin_pending_login');
        if (!$userId) {
            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Session expired. Please log in again.']);
        }

        $user = User::find($userId);
        if (!$user) {
            $request->session()->forget('admin_pending_login');
            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Account not found. Please log in again.']);
        }

        $otp = KioskOtpCode::where('email', $user->email)
            ->where('code', $request->otp_code)
            ->where('guard', 'admin')
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            return back()->withErrors(['otp_code' => 'Invalid or expired code. Please try again.']);
        }

        // Mark OTP as used
        $otp->update(['used_at' => now()]);

        // Clear pending session and actually log in
        $request->session()->forget('admin_pending_login');
        Auth::guard('web')->loginUsingId($userId);
        $request->session()->regenerate();

        AuditLogger::log('auth_login_admin', 'Administrator ' . $user->name . ' logged into the admin portal', [
            'admin_id'       => $user->id,
            'performer_name' => $user->name,
            'email'          => $user->email,
        ], 'auth', 'admin', $user->id);

        return redirect()->intended('/dashboard');
    }

    public function resendOtp(Request $request)
    {
        $userId = $request->session()->get('admin_pending_login');
        if (!$userId) {
            return redirect()->route('admin.login');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('admin.login');
        }

        $this->sendOtp($user);

        return redirect()->route('admin.otp.form')->with('otp_resent', true);
    }

    public function logout(Request $request)
    {
        $user = Auth::guard('web')->user();
        if ($user) {
            AuditLogger::log('auth_logout_admin', 'Administrator ' . $user->name . ' logged out', [
                'admin_id'       => $user->id,
                'performer_name' => $user->name,
            ], 'auth', 'admin', $user->id);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }

    private function sendOtp(User $user): void
    {
        // Invalidate any existing unused admin OTPs for this email
        KioskOtpCode::where('email', $user->email)
            ->where('guard', 'admin')
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        // Generate 6-digit code
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        KioskOtpCode::create([
            'email'      => $user->email,
            'code'       => $code,
            'guard'      => 'admin',
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new AdminOtpMail($code, $user->name));
    }
}
