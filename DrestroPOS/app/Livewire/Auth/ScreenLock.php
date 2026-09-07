<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ScreenLock extends Component
{
    public $pin = '';
    public $error = '';
    
    // We listen to an event that alpine can trigger if needed, or we just rely on Alpine state
    
    public function unlock($submittedPin)
    {
        $user = Auth::user();
        
        if ($user && $user->pin === $submittedPin) {
            $this->error = '';
            $this->pin = ''; // clear it for next time
            // Tell the frontend to unlock
            $this->dispatch('screen-unlocked');
        } else {
            $this->error = 'Incorrect PIN.';
            $this->pin = '';
            $this->dispatch('pin-invalid');
        }
    }
    
    public function render()
    {
        return view('livewire.auth.screen-lock');
    }
}
