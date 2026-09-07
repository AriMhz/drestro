<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use App\Services\AakashSmsService;
use Illuminate\Support\Facades\Auth;

class OtpLogin extends Component
{
    public $phone = '';
    public $otp = '';
    public $step = 1; // 1 = Enter Phone, 2 = Enter OTP
    public $error = '';
    public $success = '';

    public function requestOtp()
    {
        $this->validate([
            'phone' => 'required|string|min:10|max:15',
        ]);

        // Find user by phone number
        $user = User::where('phone', $this->phone)->first();

        if (!$user) {
            $this->error = 'No staff member found with this phone number.';
            return;
        }

        // Generate a random 6-digit OTP
        $otpCode = (string) rand(100000, 999999);
        
        // Update user record
        $user->otp = $otpCode;
        $user->otp_expires_at = now()->addMinutes(5);
        $user->save();

        // Send OTP via Aakash SMS
        $message = "Your DRestro POS login OTP is: {$otpCode}. It will expire in 5 minutes.";
        $sent = AakashSmsService::send($this->phone, $message);

        if ($sent) {
            $this->step = 2;
            $this->error = '';
            $this->success = 'OTP has been sent to your phone number!';
        } else {
            $this->error = 'Failed to send OTP. Please check your network or try again.';
        }
    }

    public function verifyOtp()
    {
        $this->validate([
            'otp' => 'required|string|size:6',
        ]);

        $user = User::where('phone', $this->phone)->first();

        if ($user && $user->otp === $this->otp && $user->otp_expires_at && now()->isBefore($user->otp_expires_at)) {
            // Clear OTP fields
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();

            // Login user
            Auth::login($user);
            session()->regenerate();

            // Redirect based on role
            return $this->redirectAfterLogin($user);
        }

        $this->error = 'Invalid or expired OTP. Please try again.';
    }

    protected function redirectAfterLogin($user)
    {
        if ($user->role === 'admin' || $user->role === 'manager') {
            return redirect()->intended('/admin');
        } elseif ($user->role === 'waiter') {
            return redirect()->intended('/staff/waiter');
        } elseif ($user->role === 'cashier') {
            return redirect()->intended('/staff/cashier');
        } elseif ($user->role === 'receptionist') {
            return redirect()->intended('/staff/hotel-reception');
        } elseif ($user->role === 'hotel') {
            return redirect()->intended('/staff/room-service');
        } elseif ($user->role === 'kitchen') {
            return redirect()->intended('/staff/kitchen');
        } elseif ($user->role === 'bar') {
            return redirect()->intended('/staff/bar');
        }

        return redirect()->intended('/admin');
    }

    public function backToPhone()
    {
        $this->step = 1;
        $this->otp = '';
        $this->error = '';
        $this->success = '';
    }

    public function render()
    {
        return view('livewire.auth.otp-login')->layout('components.layouts.guest', ['title' => 'OTP Login']);
    }
}
