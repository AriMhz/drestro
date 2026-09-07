<?php
$target_migration = __DIR__ . '/../database/migrations/2026_06_04_100000_add_otp_to_users_table.php';
$target_service = __DIR__ . '/../app/Services/AakashSmsService.php';
$target_controller = __DIR__ . '/../app/Livewire/Auth/OtpLogin.php';
$target_view = __DIR__ . '/../resources/views/livewire/auth/otp-login.blade.php';
$target_login = __DIR__ . '/../resources/views/auth/login.blade.php';
$target_routes = __DIR__ . '/../routes/web.php';

$migration_content = <<< 'EOT'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('otp')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['otp', 'otp_expires_at']);
        });
    }
};

EOT;

$service_content = <<< 'EOT'
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AakashSmsService
{
    /**
     * Send SMS via Aakash SMS API Gateway.
     * 
     * @param string $to
     * @param string $message
     * @return bool
     */
    public static function send($to, $message)
    {
        $token = env('AAKASH_SMS_AUTH_TOKEN');
        
        if (empty($token)) {
            Log::error("Aakash SMS: Auth Token is not set in .env.");
            return false;
        }

        // Clean up phone number (remove spaces, dashes, ensure correct country prefix if needed)
        $to = preg_replace('/[^0-9]/', '', $to);

        // Aakash SMS expects standard 10-digit mobile number for Nepal (98xxxxxxxx)
        if (strlen($to) > 10) {
            // Remove leading country codes like 977
            if (str_starts_with($to, '977')) {
                $to = substr($to, 3);
            }
        }

        try {
            $response = Http::asForm()->post('https://sms.aakashsms.com/sms/v3/send', [
                'auth_token' => $token,
                'to' => $to,
                'text' => $message,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Check Aakash SMS specific error format (typically 'error' => true/false or similar)
                if (isset($data['error']) && $data['error'] === true) {
                    Log::error("Aakash SMS Gateway Error: " . ($data['message'] ?? 'Unknown gateway error'));
                    return false;
                }
                
                Log::info("Aakash SMS sent successfully to {$to}.");
                return true;
            } else {
                Log::error("Aakash SMS Request failed with status " . $response->status() . ": " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Aakash SMS Gateway Exception: " . $e->getMessage());
        }

        return false;
    }
}

EOT;

$controller_content = <<< 'EOT'
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

EOT;

$view_content = <<< 'EOT'
<div class="min-h-screen flex flex-col items-center justify-center bg-slate-900 p-6">
    <div class="max-w-md w-full">
        <!-- Logo & Title -->
        <div class="text-center mb-10">
            <h1 class="text-4xl font-black text-white tracking-tighter mb-2">DRESTRO <span class="text-emerald-500">POS</span></h1>
            <p class="text-slate-400 font-medium">Sign in with SMS / OTP Code</p>
        </div>

        <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-[2.5rem] p-10 shadow-2xl mb-8">
            
            <!-- Error Alert -->
            @if ($error)
                <div class="bg-red-500/10 text-red-400 p-4 rounded-2xl text-sm font-semibold border border-red-500/20 mb-6 flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span>{{ $error }}</span>
                </div>
            @endif

            <!-- Success Alert -->
            @if ($success)
                <div class="bg-emerald-500/10 text-emerald-400 p-4 rounded-2xl text-sm font-semibold border border-emerald-500/20 mb-6 flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ $success }}</span>
                </div>
            @endif

            <!-- Step 1: Request OTP -->
            @if ($step === 1)
                <form wire:submit.prevent="requestOtp" class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2 uppercase tracking-wide">Mobile Number</label>
                        <input type="text" wire:model="phone" required autofocus 
                               class="w-full px-5 py-3.5 bg-slate-900 border border-slate-700 rounded-2xl text-white outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-semibold placeholder-slate-500" 
                               placeholder="e.g. 9841XXXXXX">
                    </div>

                    <button type="submit" wire:loading.attr="disabled"
                            class="w-full py-4 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white rounded-2xl font-bold shadow-[0_8px_20px_-8px_rgba(16,185,129,0.5)] hover:shadow-[0_8px_30px_-8px_rgba(16,185,129,0.6)] hover:-translate-y-0.5 active:scale-[0.98] transition-all text-md tracking-wide flex items-center justify-center gap-2">
                        <span wire:loading.remove>Send OTP Code</span>
                        <span wire:loading>Sending OTP...</span>
                    </button>
                </form>
            @endif

            <!-- Step 2: Verify OTP -->
            @if ($step === 2)
                <form wire:submit.prevent="verifyOtp" class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2 uppercase tracking-wide">6-Digit OTP Code</label>
                        <input type="text" wire:model="otp" required autofocus maxlength="6"
                               class="w-full px-5 py-3.5 bg-slate-900 border border-slate-700 rounded-2xl text-white text-center text-2xl tracking-widest outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-black placeholder-slate-600" 
                               placeholder="••••••">
                    </div>

                    <button type="submit" wire:loading.attr="disabled"
                            class="w-full py-4 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white rounded-2xl font-bold shadow-[0_8px_20px_-8px_rgba(16,185,129,0.5)] hover:shadow-[0_8px_30px_-8px_rgba(16,185,129,0.6)] hover:-translate-y-0.5 active:scale-[0.98] transition-all text-md tracking-wide flex items-center justify-center gap-2">
                        <span wire:loading.remove>Verify & Login</span>
                        <span wire:loading>Verifying...</span>
                    </button>

                    <button type="button" wire:click="backToPhone" class="w-full text-center text-sm font-bold text-slate-400 hover:text-white transition-all uppercase tracking-wider">
                        ← Change Phone Number
                    </button>
                </form>
            @endif

            <div class="flex items-center my-6">
                <div class="flex-grow border-t border-slate-700"></div>
                <span class="px-3 text-xs text-slate-500 font-bold uppercase tracking-wider bg-transparent">or</span>
                <div class="flex-grow border-t border-slate-700"></div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <a href="/login" class="py-3.5 border border-slate-700 hover:bg-slate-800 text-slate-300 rounded-2xl font-bold transition-all text-sm flex items-center justify-center gap-2">
                    EMAIL LOGIN
                </a>
                <a href="/pin-login" class="py-3.5 border border-slate-700 hover:bg-slate-800 text-slate-300 rounded-2xl font-bold transition-all text-sm flex items-center justify-center gap-2">
                    PIN LOGIN
                </a>
            </div>
        </div>

        <p class="text-center text-slate-500 text-xs font-bold uppercase tracking-widest">Powered by Drestro POS</p>
    </div>
</div>

EOT;

$login_content = <<< 'EOT'
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - Drestro POS</title>
        <!-- Offline Fonts -->
        <link rel="stylesheet" href="{{ asset('fonts/inter/inter.css') }}">
        @vite(['resources/css/app.css'])
    </head>
    <body class="bg-slate-50 font-[Inter] antialiased text-slate-800 flex items-center justify-center min-h-screen relative overflow-hidden dark:bg-slate-900 dark:text-slate-200">
        <!-- Abstract Background -->
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-500 rounded-full blur-3xl opacity-10"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-teal-500 rounded-full blur-3xl opacity-10"></div>

        <div class="w-full max-w-md bg-white/80 backdrop-blur-xl p-8 sm:p-10 rounded-3xl shadow-[0_8px_40px_-12px_rgba(0,0,0,0.1)] border border-slate-200/50 z-10 mx-4 dark:bg-slate-900/80">
            <div class="text-center mb-8">
                    <img src="{{ asset('images/logo.svg') }}" class="h-10 w-auto mx-auto mb-3 object-contain dark:hidden" alt="DRestro Logo">
                    <img src="{{ asset('images/logo-light.svg') }}" class="h-10 w-auto mx-auto mb-3 object-contain hidden dark:block" alt="DRestro Logo">
                <p class="text-slate-500 font-medium mt-1 dark:text-slate-400">Sign in to manage your restaurant</p>
            </div>

            <form method="POST" action="{{ route('login.post') }}" class="space-y-6">
                @csrf
                @if($errors->any())
                    <div class="bg-red-50 text-red-600 p-4 rounded-2xl text-sm font-semibold border border-red-100 flex items-start gap-3 dark:border-red-800/50 dark:text-red-400 dark:bg-red-900/20">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-red-50 text-red-600 p-4 rounded-2xl text-sm font-semibold border border-red-100 flex items-start gap-3 dark:border-red-800/50 dark:text-red-400 dark:bg-red-900/20">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide dark:text-slate-300">Email or Username</label>
                    <input type="text" name="login" value="{{ old('login') }}" required autofocus class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white outline-none transition-all font-medium placeholder-slate-400 dark:bg-slate-900 dark:border-slate-700" placeholder="admin@drestro.com or username">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide dark:text-slate-300">Password</label>
                    <input type="password" name="password" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white outline-none transition-all font-medium placeholder-slate-400 dark:bg-slate-900 dark:border-slate-700" placeholder="••••••••">
                </div>

                <button type="submit" class="w-full py-4 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white rounded-2xl font-bold shadow-[0_8px_20px_-8px_rgba(16,185,129,0.5)] hover:shadow-[0_8px_30px_-8px_rgba(16,185,129,0.6)] hover:-translate-y-0.5 active:scale-[0.98] transition-all text-lg tracking-wide">
                    Sign In
                </button>

                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('pin-login') }}" class="py-3.5 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-2xl font-bold transition-all flex items-center justify-center gap-2 text-sm">
                        PIN Login
                    </a>
                    <a href="{{ route('otp-login') }}" class="py-3.5 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-2xl font-bold transition-all flex items-center justify-center gap-2 text-sm">
                        SMS / OTP Login
                    </a>
                </div>

                <div class="flex items-center my-4">
                    <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
                    <span class="px-3 text-xs text-slate-400 font-bold uppercase tracking-wider bg-transparent">or</span>
                    <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
                </div>

                <a href="https://drestro.com/login" class="w-full py-3.5 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-2xl font-bold transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Continue with Google
                </a>


            </form>
        </div>
    </body>
</html>

EOT;

$routes_content = <<< 'EOT'
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/pin-login', App\Livewire\Auth\PinLogin::class)->name('pin-login');
Route::get('/otp-login', App\Livewire\Auth\OtpLogin::class)->name('otp-login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// SSO Route for Next.js Bridge
Route::get('/sso/login', [\App\Http\Controllers\SSOController::class, 'login'])->name('sso.login');

Route::get('/debug-user', function() {
    return [
        'logged_in_user' => auth()->user() ? auth()->user()->toArray() : null,
        'users_all' => \App\Models\User::withoutGlobalScopes()->get()->toArray(),
        'restaurants_all' => \App\Models\Restaurant::all()->toArray(),
        'current_restaurant' => current_restaurant() ? current_restaurant()->toArray() : null,
    ];
});

Route::get('/', function () {
    return redirect('/login');
});

// Digital Menu (Public)
Route::get('/menu', App\Livewire\Customer\DigitalMenu::class)->name('menu');

// Image Proxy (Fix for broken Windows symlinks)
Route::get('/storage/{folder}/{filename}', function ($folder, $filename) {
    $path = storage_path("app/public/{$folder}/{$filename}");
    if (!file_exists($path)) abort(404);
    
    $extension = pathinfo($path, PATHINFO_EXTENSION);
    $mimeTypes = [
        'webp' => 'image/webp',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
    ];
    
    return response()->file($path, [
        'Content-Type' => $mimeTypes[strtolower($extension)] ?? 'image/jpeg',
        'Cache-Control' => 'public, max-age=86400'
    ]);
})->where('folder', 'menu_items|categories|restaurant|logos')->where('filename', '.*');

Route::middleware(['auth'])->group(function () {
    
    // Toggle calendar type dynamically (AD/BS) - Super Admin Only!
    Route::get('/toggle-calendar/{type}', function ($type) {
        if (auth()->user()->role === 'super_admin' && in_array($type, ['ad', 'bs'])) {
            $restaurant = current_restaurant();
            if ($restaurant) {
                $restaurant->date_calendar_type = strtoupper($type);
                $restaurant->save();
            }
        }
        return back();
    })->name('toggle-calendar');

    // Admin Routes
    Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
        Route::get('/', App\Livewire\Admin\Dashboard::class)->name('dashboard');
        
        // These pages are ALWAYS accessible for admins
        Route::get('/license', App\Livewire\Admin\LicenseManager::class)->name('license');
        Route::get('/settings', App\Livewire\Admin\Settings::class)->name('settings');
        
        // License activation must be accessible even when license is invalid
        Route::post('/license/activate', function (Illuminate\Http\Request $request) {
            $key = trim($request->input('license_key'));
            $restaurant = current_restaurant();
            
            if ($key === 'CLEAR') {
                $restaurant->update(['license_key' => null, 'license_data' => null]);
                \Illuminate\Support\Facades\Cache::flush();
                return redirect()->route('admin.license')->with('success', 'System Reset: All errors cleared.');
            }
            
            $parts = explode('.', $key);
            if (count($parts) === 2) {
                $payload = json_decode(base64_decode($parts[0]), true);
                $signature = $parts[1];
                $expected = hash_hmac('sha256', $parts[0], 'DrestroPOS_Secure_Key_2026_X9P2');
                
                if (hash_equals($expected, $signature)) {
                    $plan = $payload['plan'] ?? 'Premium';
                    $restaurant->update([
                        'license_key' => $key,
                        'license_data' => $payload,
                        'machine_id' => $payload['machine_id'] ?? 'UNIVERSAL'
                    ]);
                    
                    \Illuminate\Support\Facades\Cache::flush();
                    return redirect()->route('admin.license')->with('success', "✅ Activation Successful! Welcome to $plan.");
                }
            }
            return back()->with('error', '❌ Invalid License Key Signature.');
        })->name('license.activate');

        // Note: CheckLicense middleware is applied globally in bootstrap/app.php
        Route::get('/menus', App\Livewire\Admin\MenuManager::class)->name('menus');
        Route::get('/tables', App\Livewire\Admin\TableManager::class)->name('tables');
        Route::get('/rooms', App\Livewire\Admin\RoomManager::class)->name('rooms');
        Route::get('/inventory', App\Livewire\Admin\InventoryManager::class)->name('inventory');
        Route::get('/staff', App\Livewire\Admin\StaffManager::class)->name('staff');
        Route::get('/reports', App\Livewire\Admin\ReportsManager::class)->name('reports');
        Route::get('/support', App\Livewire\Admin\SupportTickets::class)->name('support');
        
        // SAFE CLEANUP ROUTE FOR CLIENT DELIVERY
        Route::get('/clean-database', function() {
            try {
                \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = OFF');
                \Illuminate\Support\Facades\DB::table('order_items')->delete();
                \Illuminate\Support\Facades\DB::table('orders')->delete();
                \Illuminate\Support\Facades\DB::table('invoices')->delete();
                \Illuminate\Support\Facades\DB::table('payments')->delete();
                \Illuminate\Support\Facades\DB::table('inventory_transactions')->delete();
                \Illuminate\Support\Facades\DB::table('notifications')->delete();
                \Illuminate\Support\Facades\DB::statement("DELETE FROM sqlite_sequence WHERE name IN ('order_items', 'orders', 'invoices', 'payments', 'inventory_transactions', 'notifications')");
                
                \App\Models\Table::query()->update([
                    'status' => 'available',
                    'room_status' => 'available',
                    'guest_name' => null,
                    'guest_phone' => null,
                    'check_in_at' => null,
                    'current_order_id' => null
                ]);
                
                \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = ON');
                \Illuminate\Support\Facades\Cache::flush();
                return "✅ SUCCESS: All test orders, invoices, and payments have been wiped clean! The menu and settings are preserved. Ready for your client!";
            } catch (\Exception $e) {
                return "❌ ERROR: " . $e->getMessage();
            }
        })->name('clean-database');
    });

    // Staff Panel Routes
    Route::group(['prefix' => 'staff', 'as' => 'staff.'], function () {
        Route::get('/take-order', App\Livewire\Staff\WaiterOrderTaking::class)->name('take-order');
        Route::get('/room-service', App\Livewire\Staff\RoomService::class)->name('room-service');
        Route::get('/cashier', App\Livewire\Staff\CashierPanel::class)->name('cashier');
        Route::get('/waiter', App\Livewire\Staff\WaiterDashboard::class)->name('waiter');
        Route::get('/kitchen', App\Livewire\Staff\KitchenPanel::class)->name('kitchen');
        Route::get('/bar', App\Livewire\Staff\BarPanel::class)->name('bar');
        Route::get('/hotel-reception', App\Livewire\Staff\HotelCashier::class)->name('hotel-reception');
    });
});

// TOTAL FACTORY RESET (USE WITH CAUTION)
Route::get('/admin/factory-reset', function() {
    if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'super_admin'])) {
        return "Access Denied.";
    }

    try {
        \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = OFF');
        
        // List of all transaction and config tables
        $tables = [
            'order_items', 'orders', 'invoices', 'payments', 
            'inventory_transactions', 'inventory_items', 
            'menu_items', 'menu_categories', 'tables', 
            'notifications', 'users', 'restaurants', 'failed_jobs'
        ];

        foreach ($tables as $table) {
            if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                \Illuminate\Support\Facades\DB::table($table)->delete();
                \Illuminate\Support\Facades\DB::statement("DELETE FROM sqlite_sequence WHERE name = '$table'");
            }
        }
        
        // 1. Create Fresh Users
        \App\Models\User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'role' => 'admin',
        ]);
        \App\Models\User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@admin.com',
            'password' => \Illuminate\Support\Facades\Hash::make('superadmin123'),
            'role' => 'super_admin',
        ]);
        
        // 2. Create Fresh Restaurant (No License)
        \App\Models\Restaurant::create([
            'name' => 'Drestro POS',
            'email' => 'admin@admin.com',
            'license_key' => null,
            'license_data' => null
        ]);

        // 3. Clear Storage Images (Categories & Menu Items)
        $folders = ['categories', 'menu_items', 'restaurants'];
        foreach ($folders as $folder) {
            $path = storage_path("app/public/$folder");
            if (file_exists($path)) {
                $files = glob($path . '/*'); 
                foreach($files as $file){
                    if(is_file($file)) unlink($file);
                }
            }
        }
        
        \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = ON');
        \Illuminate\Support\Facades\Cache::flush();
        \Illuminate\Support\Facades\Auth::logout();
        
        return "<h1>🏁 FACTORY RESET COMPLETE</h1>
                <p>The system is now in a 'Fresh Install' state for your client.</p>
                <ul>
                    <li>✅ All Data & Licenses Wiped</li>
                    <li>✅ Storage Images Cleared</li>
                    <li>✅ Default Login Restored: <b>admin@admin.com</b> / <b>admin123</b></li>
                </ul>
                <p><a href='/login'>Go to Login</a></p>";
    } catch (\Exception $e) {
        return "❌ ERROR: " . $e->getMessage();
    }
})->name('factory-reset');


// Staff Invitation Routes (Public)
Route::get('/invite/{token}', function($token) {
    if (auth()->check()) {
        $user = auth()->user();
        if (in_array($user->role, ['admin', 'manager', 'super_admin'])) return redirect('/admin');
        $roleMap = ['waiter' => '/staff/waiter', 'cashier' => '/staff/cashier', 'receptionist' => '/staff/hotel-reception', 'hotel' => '/staff/room-service', 'kitchen' => '/staff/kitchen', 'bar' => '/staff/bar'];
        return redirect($roleMap[$user->role] ?? '/login');
    }
    
    $user = \App\Models\User::withoutGlobalScope('restaurant')->where('remember_token', $token)->first();
    if (!$user) {
        return redirect('/login')->with('error', 'Invalid or expired invitation link. You may have already accepted it.');
    }
    
    $restaurant = \App\Models\Restaurant::find($user->restaurant_id);
    if ($restaurant) {
        session(['tenant_slug' => $restaurant->slug]);
        app()->instance('restaurant', $restaurant);
    }
    
$html = <<<'HTML'
<x-layouts.guest title="Accept Invitation">
    <div class="min-h-screen flex items-center justify-center bg-slate-50 dark:bg-[#0a0a0a] p-4 font-[Inter]">
        <div class="w-full max-w-md bg-white dark:bg-[#111111] rounded-3xl shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="p-8 text-center bg-gradient-to-b from-emerald-50 to-white dark:from-emerald-900/20 dark:to-[#111111] border-b border-slate-100 dark:border-slate-800">
                <div class="w-16 h-16 bg-emerald-500 text-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-500/30">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2-2v10a2 2 0 002 2z"></path></svg>
                </div>
                <h2 class="text-2xl font-black text-slate-800 dark:text-white">You're Invited!</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-2 text-sm">Join <strong class="text-emerald-600 dark:text-emerald-400">{{ $restaurant->name ?? 'DRestro POS' }}</strong> as a {{ ucfirst(str_replace('_', ' ', $user->role)) }}.</p>
            </div>

            <div class="p-8">
                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="/invite/{{ $token }}" method="POST" class="space-y-5">
                    @csrf
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">First Name</label>
                            <input type="text" name="first_name" required placeholder="Ram" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 outline-none transition-all dark:bg-[#1a1a1a] dark:border-slate-800 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">Last Name</label>
                            <input type="text" name="last_name" required placeholder="Sharma" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 outline-none transition-all dark:bg-[#1a1a1a] dark:border-slate-800 dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">Create Password</label>
                        <input type="password" name="password" required placeholder="••••••••" minlength="6" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 outline-none transition-all dark:bg-[#1a1a1a] dark:border-slate-800 dark:text-white">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">Confirm Password</label>
                        <input type="password" name="password_confirmation" required placeholder="••••••••" minlength="6" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 outline-none transition-all dark:bg-[#1a1a1a] dark:border-slate-800 dark:text-white">
                    </div>

                    <button type="submit" class="w-full mt-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-emerald-500/30 flex justify-center items-center gap-2">
                        Accept Invitation & Login
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.guest>
HTML;
    return \Illuminate\Support\Facades\Blade::render($html, ['user' => $user, 'token' => $token, 'restaurant' => $restaurant]);

})->name('invite.accept');

Route::post('/invite/{token}', function(\Illuminate\Http\Request $request, $token) {
    if (auth()->check()) {
        $user = auth()->user();
        if (in_array($user->role, ['admin', 'manager', 'super_admin'])) return redirect('/admin');
        $roleMap = ['waiter' => '/staff/waiter', 'cashier' => '/staff/cashier', 'receptionist' => '/staff/hotel-reception', 'hotel' => '/staff/room-service', 'kitchen' => '/staff/kitchen', 'bar' => '/staff/bar'];
        return redirect($roleMap[$user->role] ?? '/login');
    }

    $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'password' => 'required|min:6|confirmed'
    ]);

    $user = \App\Models\User::withoutGlobalScope('restaurant')->where('remember_token', $token)->first();
    if (!$user) {
        return redirect('/login')->with('error', 'Invalid or expired invitation link. You may have already accepted it.');
    }
    
    $restaurant = \App\Models\Restaurant::find($user->restaurant_id);
    if ($restaurant) {
        session(['tenant_slug' => $restaurant->slug]);
        app()->instance('restaurant', $restaurant);
    }

    $user->update([
        'first_name' => $request->first_name,
        'last_name' => $request->last_name,
        'name' => $request->first_name . ' ' . $request->last_name,
        'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        'remember_token' => null // Consume the token
    ]);

    \Illuminate\Support\Facades\Auth::login($user);
    
    // Redirect based on role
    if (in_array($user->role, ['admin', 'manager', 'super_admin'])) {
        return redirect('/admin');
    }
    
    $roleMap = [
        'waiter' => '/staff/waiter',
        'cashier' => '/staff/cashier',
        'receptionist' => '/staff/hotel-reception',
        'hotel' => '/staff/room-service',
        'kitchen' => '/staff/kitchen',
        'bar' => '/staff/bar'
    ];
    
    return redirect($roleMap[$user->role] ?? '/login');
});

EOT;

// Create parent directories if they don't exist
@mkdir(dirname($target_migration), 0755, true);
@mkdir(dirname($target_service), 0755, true);
@mkdir(dirname($target_controller), 0755, true);
@mkdir(dirname($target_view), 0755, true);
@mkdir(dirname($target_login), 0755, true);
@mkdir(dirname($target_routes), 0755, true);

// Write files
file_put_contents($target_migration, $migration_content);
file_put_contents($target_service, $service_content);
file_put_contents($target_controller, $controller_content);
file_put_contents($target_view, $view_content);
file_put_contents($target_login, $login_content);
file_put_contents($target_routes, $routes_content);

// Bootstrap Laravel and Run Migrations
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

try {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    $migration_status = "Database migrated successfully!";
} catch (\Exception $e) {
    $migration_status = "Migration error: " . $e->getMessage();
}

// Clear caches
\Illuminate\Support\Facades\Artisan::call('view:clear');
\Illuminate\Support\Facades\Artisan::call('cache:clear');

if (function_exists('opcache_reset')) {
    opcache_reset();
}

echo "<h1>DRestro POS SMS Login Deployment Successful!</h1>";
echo "<p>Migration and all files written.</p>";
echo "<p><b>Migration Status:</b> " . $migration_status . "</p>";
echo "<p>Cache and OPCache cleared successfully!</p>";
