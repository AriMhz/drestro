<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PinLogin extends Component
{
    public $pin = '';
    public $error = '';

    public function login($submittedPin)
    {
        $user = User::where('pin', $submittedPin)->first();

        if ($user) {
            Auth::login($user);
            session()->regenerate();

            // Redirect based on role
            return $this->redirectAfterLogin($user);
        }

        $this->error = 'Invalid PIN. Please try again.';
        $this->dispatch('pin-invalid');
    }

    protected function redirectAfterLogin($user)
    {
        if ($user->role === 'admin') {
            return redirect()->intended('/admin');
        } elseif ($user->role === 'manager') {
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

    public function render()
    {
        return view('livewire.auth.pin-login')->layout('components.layouts.guest', ['title' => 'PIN Login']);
    }
}
