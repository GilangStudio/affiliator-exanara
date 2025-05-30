<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Support\Facades\Log;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    protected $activityLogService;
    protected $userService;
    protected $notificationService;

    public function __construct(
        ActivityLogService $activityLogService,
        UserService $userService,
        NotificationService $notificationService
    ) {
        $this->activityLogService = $activityLogService;
        $this->userService = $userService;
        $this->notificationService = $notificationService;
    }

    /**
     * Show login form
     */
    public function login()
    {
        return view('pages.auth.login');
    }

    /**
     * Process login dengan email atau phone
     */
    public function loginProcess(Request $request)
    {
        $request->validate([
            'email_or_phone' => 'required|string',
            'password' => 'required|string',
        ], [
            'email_or_phone.required' => 'Email atau nomor telepon harus diisi',
            'password.required' => 'Password harus diisi',
        ]);

        try {
            $emailOrPhone = $request->email_or_phone;

            // Cek apakah input adalah email atau phone
            $loginField = filter_var($emailOrPhone, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

            // Format phone number jika diperlukan
            if ($loginField === 'phone') {
                $emailOrPhone = $this->formatPhoneNumber($emailOrPhone);
            }

            $credentials = [
                $loginField => $emailOrPhone,
                'password' => $request->password
            ];

            if (Auth::attempt($credentials)) {
                $user = Auth::user();

                // Cek apakah user aktif
                if (!$user->is_active) {
                    Auth::logout();
                    return redirect()->back()
                        ->withInput($request->only('email_or_phone'))
                        ->with('error', 'Akun Anda tidak aktif. Silakan hubungi administrator.');
                }

                // Update last login menggunakan service
                // $user->update(['last_login_at' => now()]);
                User::where('id', $user->id)->update(['last_login_at' => now()]);

                // Log aktivitas login
                $this->activityLogService->logAuth($user->id, 'login');

                $request->session()->regenerate();

                // Redirect berdasarkan role
                $redirectRoute = $this->getRedirectRouteByRole($user->role);

                return redirect()->intended(route($redirectRoute))
                    ->with('success', 'Selamat datang, ' . $user->name . '!');
            }

            return redirect()->back()
                ->withInput($request->only('email_or_phone'))
                ->with('error', 'Email/nomor telepon atau password salah.');
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());

            return redirect()->back()
                ->withInput($request->only('email_or_phone'))
                ->with('error', 'Terjadi kesalahan saat login. Silakan coba lagi.');
        }
    }

    /**
     * Show register form
     */
    public function register()
    {
        return view('pages.auth.register');
    }

    /**
     * Process registration
     */
    public function registerProcess(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => [
                'required',
                'string',
                'regex:/^(\+62|62|0)[0-9]{9,13}$/',
                'unique:users'
            ],
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'Nama harus diisi',
            'name.max' => 'Nama maksimal 255 karakter',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'phone.required' => 'Nomor telepon harus diisi',
            'phone.regex' => 'Format nomor telepon tidak valid. Gunakan format: 08xxxxxxxxx atau +62xxxxxxxxx',
            'phone.unique' => 'Nomor telepon sudah terdaftar',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        try {
            // Format phone number
            $phone = $this->formatPhoneNumber($request->phone);

            // Create user using service
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $phone,
                'password' => $request->password,
                'role' => 'affiliator', // default role for registration
            ];

            $user = $this->userService->createUser($userData);

            // Auto login setelah registrasi
            Auth::login($user);

            // Send welcome notification
            $this->notificationService->sendWelcomeNotification($user->id);

            return redirect()->route('dashboard')
                ->with('success', 'Registrasi berhasil! Selamat datang di sistem affiliator.');
        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());

            return redirect()->back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->with('error', 'Terjadi kesalahan saat registrasi. Silakan coba lagi.');
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            // Log aktivitas logout
            $this->activityLogService->logAuth($user->id, 'logout');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil logout');
    }

    /**
     * Show forgot password form
     */
    public function forgotPassword()
    {
        return view('pages.auth.forgot-password');
    }

    /**
     * Process forgot password
     */
    public function forgotPasswordProcess(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.exists' => 'Email tidak ditemukan'
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user->is_active) {
                return redirect()->back()
                    ->with('error', 'Akun Anda tidak aktif. Silakan hubungi administrator.');
            }

            // Generate reset token (you can implement this as needed)
            $token = Str::random(60);

            // Store token in database or cache
            Cache::put('password_reset_' . $user->id, $token, now()->addMinutes(60));

            // Send reset email (implement email service)
            // $this->emailService->sendPasswordResetEmail($user, $token);

            // Log activity
            $this->activityLogService->log(
                $user->id,
                'forgot_password_request',
                'Password reset requested for: ' . $user->email
            );

            return redirect()->back()
                ->with('success', 'Link reset password telah dikirim ke email Anda.');
        } catch (\Exception $e) {
            Log::error('Forgot password error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    /**
     * Show reset password form
     */
    public function resetPassword($token)
    {
        return view('pages.auth.reset-password', compact('token'));
    }

    /**
     * Process reset password
     */
    public function resetPasswordProcess(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.required' => 'Email harus diisi',
            'email.exists' => 'Email tidak ditemukan',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            // Verify token
            $cachedToken = Cache::get('password_reset_' . $user->id);

            if (!$cachedToken || $cachedToken !== $request->token) {
                return redirect()->back()
                    ->with('error', 'Token reset password tidak valid atau sudah kadaluarsa.');
            }

            // Update password using service
            $this->userService->changePassword($user, $request->password);

            // Clear token
            Cache::forget('password_reset_' . $user->id);

            // Log activity
            $this->activityLogService->logAuth($user->id, 'password_reset');

            return redirect()->route('login')
                ->with('success', 'Password berhasil direset. Silakan login dengan password baru.');
        } catch (\Exception $e) {
            Log::error('Reset password error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat reset password. Silakan coba lagi.');
        }
    }

    /**
     * Format phone number to consistent format
     */
    private function formatPhoneNumber($phone)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // // Handle different formats
        // if (substr($phone, 0, 2) === '62') {
        //     // Already has country code 62
        //     return $phone;
        // } elseif (substr($phone, 0, 1) === '0') {
        //     // Remove leading 0 and add country code
        //     return '62' . substr($phone, 1);
        // } else {
        //     // Add country code
        //     return '62' . $phone;
        // }

        // if start with 62, remove it
        if (substr($phone, 0, 2) === '62') {
            $phone = substr($phone, 2);
        }

        // if start with 0, remove it
        if (substr($phone, 0, 1) === '0') {
            $phone = substr($phone, 1);
        }

        return $phone;
    }

    /**
     * Get redirect route based on user role
     */
    private function getRedirectRouteByRole($role)
    {
        return match ($role) {
            'superadmin' => 'superadmin.dashboard',
            'admin' => 'admin.dashboard',
            'affiliator' => 'dashboard',
            default => 'dashboard'
        };
    }

    /**
     * Check if user can register (optional restriction)
     */
    private function canRegister()
    {
        // You can add registration restrictions here
        // For example, only allow registration during certain periods
        // or with invitation codes

        return true; // Allow registration by default
    }

    /**
     * Verify user account (if email verification is needed)
     */
    public function verifyAccount($token)
    {
        try {
            // Find user by verification token
            $user = User::where('email_verification_token', $token)->first();

            if (!$user) {
                return redirect()->route('login')
                    ->with('error', 'Token verifikasi tidak valid.');
            }

            if ($user->email_verified_at) {
                return redirect()->route('login')
                    ->with('info', 'Email sudah terverifikasi sebelumnya.');
            }

            // Verify email
            $user->update([
                'email_verified_at' => now(),
                'email_verification_token' => null
            ]);

            // Log activity
            $this->activityLogService->log(
                $user->id,
                'email_verified',
                'Email address verified'
            );

            return redirect()->route('login')
                ->with('success', 'Email berhasil diverifikasi. Silakan login.');
        } catch (\Exception $e) {
            Log::error('Email verification error: ' . $e->getMessage());

            return redirect()->route('login')
                ->with('error', 'Terjadi kesalahan saat verifikasi email.');
        }
    }

    /**
     * Resend email verification
     */
    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            if ($user->email_verified_at) {
                return redirect()->back()
                    ->with('info', 'Email sudah terverifikasi.');
            }

            // Generate new verification token
            $token = Str::random(60);
            $user->update(['email_verification_token' => $token]);

            // Send verification email
            // $this->emailService->sendVerificationEmail($user, $token);

            return redirect()->back()
                ->with('success', 'Email verifikasi telah dikirim ulang.');
        } catch (\Exception $e) {
            Log::error('Resend verification error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }
}
