<?php
$auth_controller_file = __DIR__ . '/../app/Http/Controllers/AuthController.php';
$routes_file = __DIR__ . '/../routes/web.php';
$middleware_file = __DIR__ . '/../app/Http/Middleware/TenantMiddleware.php';
$app_layout_file = __DIR__ . '/../resources/views/components/layouts/app.blade.php';
$icon_file = __DIR__ . '/icon.svg';
$images_icon_file = __DIR__ . '/images/icon.svg';

$auth_controller_content = <<<'EOT'
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        // If already logged in, redirect to admin
        if (Auth::check()) {
            return redirect('/admin');
        }
        
        // Allow local / staff bypass for direct portal login if needed
        if ($request->has('local') || $request->has('bypass') || $request->has('staff')) {
            return view('auth.login');
        }
        
        return redirect('https://drestro.com/login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $loginValue = $request->input('login');

        // Determine if the user is logging in via email or username
        $field = filter_var($loginValue, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Always 'Remember Me' so staff stay logged in across sessions
        if (Auth::attempt([$field => $loginValue, 'password' => $request->password], true)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            if (in_array($user->role, ['admin', 'manager', 'super_admin'])) {
                return redirect()->intended('/admin');
            }
            
            $roleMap = [
                'waiter' => '/staff/waiter',
                'cashier' => '/staff/cashier',
                'receptionist' => '/staff/hotel-reception',
                'hotel' => '/staff/room-service',
                'kitchen' => '/staff/kitchen',
                'bar' => '/staff/bar'
            ];
            
            return redirect()->intended($roleMap[$user->role] ?? '/login');
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ])->onlyInput('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $host = $request->getHost();
        if (str_contains($host, 'localhost') || $host === '127.0.0.1') {
            return redirect('http://localhost:3000/signout');
        }
        return redirect('https://drestro.com/signout');
    }
}

EOT;

$routes_content = <<<'EOT'
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/pin-login', App\Livewire\Auth\PinLogin::class)->name('pin-login');
Route::get('/otp-login', App\Livewire\Auth\OtpLogin::class)->name('otp-login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::any('/logout', [AuthController::class, 'logout'])->name('logout');

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
        Route::get('/tables/{table}/print-qr', function (\App\Models\Table $table) {
            $restaurant = current_restaurant();
            return view('print.qr', compact('table', 'restaurant'));
        })->name('print-qr');
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
                <div class="w-16 h-16 bg-emerald-500 text-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
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

                    <button type="submit" class="w-full mt-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3.5 rounded-xl transition-all shadow-md flex justify-center items-center gap-2">
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

$middleware_content = <<<'EOT'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Log;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('TenantMiddleware START', ['url' => $request->path(), 'host' => $request->getHost()]);

        // Ignore assets and specific public routes
        if ($request->is('storage/*') || $request->is('sso/login') || $request->is('logout') || $request->is('login') || $request->is('invite/*')) {
            Log::info('TenantMiddleware: skipping for storage/sso/logout/login/invite');
            return $next($request);
        }

        $host = $request->getHost();
        $parts = explode('.', $host);
        $slug = $parts[0];

        Log::info('TenantMiddleware: host parsing', ['host' => $host, 'slug' => $slug, 'is_local' => app()->environment('local')]);

        // Exception for the root domain itself (if they visit portal.drestro.com directly)
        if ($host === 'portal.drestro.com' || $slug === 'localhost' || $slug === '127') {
            if (app()->environment('local')) {
                $tenantSlug = session('tenant_slug');
                Log::info('TenantMiddleware: local env, tenant_slug from session', ['tenant_slug' => $tenantSlug]);

                if ($tenantSlug) {
                    $restaurant = Restaurant::where('slug', $tenantSlug)->first();
                    Log::info('TenantMiddleware: slug lookup result', ['found' => !!$restaurant, 'restaurant_id' => $restaurant->id ?? null]);
                } else {
                    $restaurant = current_restaurant();
                    Log::info('TenantMiddleware: using current_restaurant() fallback', ['found' => !!$restaurant, 'restaurant_id' => $restaurant->id ?? null]);
                }

                if ($restaurant) {
                    app()->instance('restaurant', $restaurant);
                    Log::info('TenantMiddleware: bound restaurant, proceeding', ['restaurant_id' => $restaurant->id]);
                    return $next($request);
                }

                Log::warning('TenantMiddleware: NO restaurant found, falling through!');
            }
            // For production, if they access root, they should be redirected to the SaaS
            if (app()->environment('production')) {
                return redirect('https://drestro.com');
            }
        }

        Log::info('TenantMiddleware: past localhost block, looking up slug from host', ['slug' => $slug]);
        $restaurant = Restaurant::where('slug', $slug)->first();

        if (!$restaurant) {
            Log::error('TenantMiddleware: restaurant not found by slug, aborting 404', ['slug' => $slug]);
            abort(404, 'Restaurant Not Found. Please check your URL.');
        }

        // Bind the tenant to the service container
        app()->instance('restaurant', $restaurant);

        // Security check: if the user is logged in, ensure they belong to this tenant!
        if (auth()->check() && auth()->user()->restaurant_id !== $restaurant->id) {
            Log::warning('TenantMiddleware: user restaurant mismatch, logging out', [
                'user_restaurant_id' => auth()->user()->restaurant_id,
                'tenant_restaurant_id' => $restaurant->id,
            ]);
            auth()->logout();
            return redirect('/login')->with('error', 'Session expired or invalid tenant.');
        }

        Log::info('TenantMiddleware: completed normally', ['restaurant_id' => $restaurant->id]);
        return $next($request);
    }
}

EOT;

$app_layout_content = <<<'EOT'
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $title ?? 'Drestro Admin' }}</title>
        <link rel="icon" type="image/svg+xml" href="/icon.svg">
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#10b981">
        
        <!-- Offline Fonts -->
        <link rel="stylesheet" href="{{ asset('fonts/inter/inter.css') }}">
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        <script>
            try {
                if (localStorage.getItem('pos_theme') === 'dark' || (!('pos_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {
                console.warn('localStorage access denied or unavailable', e);
            }
            function togglePosTheme() {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('pos_theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('pos_theme', 'dark');
                }
            }
        </script>
        <script>
            function toggleMobileSidebar() {
                const sidebar = document.getElementById('mobile-sidebar');
                const overlay = document.getElementById('mobile-overlay');
                if (sidebar.classList.contains('-translate-x-full')) {
                    sidebar.classList.remove('-translate-x-full');
                    sidebar.classList.add('translate-x-0');
                    overlay.classList.remove('hidden');
                } else {
                    sidebar.classList.remove('translate-x-0');
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                }
            }
        </script>
        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>


<body class="bg-slate-50 dark:bg-[#111111] dark:text-slate-100 font-[Inter] antialiased text-slate-800 transition-colors">
    <div x-data="{
          isLocked: false,
          idleTimer: null,
          role: '{{ auth()->user()->role ?? '' }}',
          idleTimeLimit: 900000, // 15 minutes in ms
          resetTimer() {
              if (this.role === 'super_admin' || this.isLocked) return;
              clearTimeout(this.idleTimer);
              this.idleTimer = setTimeout(() => {
                  this.isLocked = true;
              }, this.idleTimeLimit);
          },
          init() {
              if (this.role !== 'super_admin') {
                  this.resetTimer();
                  window.addEventListener('mousemove', () => this.resetTimer());
                  window.addEventListener('keydown', () => this.resetTimer());
                  window.addEventListener('click', () => this.resetTimer());
                  window.addEventListener('scroll', () => this.resetTimer(), true);
              }
          }
      }">
        <div>
            <div class="flex h-screen overflow-hidden">
                <!-- Mobile Sidebar Overlay -->
                <div id="mobile-overlay" class="fixed inset-0 bg-black/50 lg:hidden hidden" onclick="toggleMobileSidebar()" style="z-index: 55;"></div>

                <!-- Sidebar -->
                <aside id="mobile-sidebar" class="-translate-x-full lg:translate-x-0 fixed lg:relative inset-y-0 left-0 w-64 bg-white dark:bg-[#1A1A1A] border-r border-slate-200 dark:border-[#333333] flex flex-col transition-transform duration-300 lg:transform-none" style="z-index: 60;">
                    @php $restaurant = current_restaurant(); @endphp
                    <div class="h-14 sm:h-16 flex items-center px-6 border-b border-slate-200 dark:border-[#333333] gap-3">
                        <img src="/images/logo.svg" class="h-6 w-auto object-contain dark:hidden" alt="DRestro Logo">
                        <img src="/images/logo-light.svg" class="h-6 w-auto object-contain hidden dark:block" alt="DRestro Logo">
                        <button type="button" onclick="toggleMobileSidebar()" class="lg:hidden ml-auto p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-white rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    @php 
                        $lf = $activeLicense['features'] ?? []; 
                        $isSuperAdmin = auth()->user()->role === 'super_admin';
                        $hasAllowedPages = !empty(auth()->user()->allowed_pages);
                        
                        $expAt = $activeLicense['expires_at'] ?? null;
                        $isExpired = false;
                        if (!$expAt) {
                            $isExpired = true;
                        } else {
                            $dLeft = (int) now()->diffInDays(\Carbon\Carbon::parse($expAt), false);
                            if ($dLeft < 0) {
                                $isExpired = true;
                            }
                        }
                        
                        // Waiter Role Checks
                        $isWaiter = auth()->user()->role === 'waiter';
                        
                        // Determine default landing page
                        $homeUrl = '/admin';
                        if ($isWaiter) {
                            $homeUrl = '/staff/waiter';
                        }
                        
                        $canAccess = function($permission) use ($hasAllowedPages) {
                            if (auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin') return true;
                            if ($hasAllowedPages && in_array($permission, auth()->user()->allowed_pages ?? [])) return true;
                            return false;
                        };

                        $getLinkClass = function($isActive) {
                            return 'flex items-center px-4 py-3 rounded-xl font-medium transition-all ' . 
                                ($isActive 
                                    ? 'bg-[#E53935] text-white shadow-[0_4px_12px_rgba(229,57,53,0.2)]' 
                                    : 'text-slate-600 dark:text-gray-400 hover:text-[#111111] dark:hover:text-white hover:bg-slate-100 dark:bg-[#222222]');
                        };
                        
                        $getIconClass = function($isActive) {
                            return 'w-5 h-5 mr-3 shrink-0 ' . ($isActive ? 'text-white' : 'text-slate-400 dark:text-gray-400');
                        };
                    @endphp

                    <nav class="flex-1 min-h-0 overflow-y-auto py-6 px-4 space-y-2">
                        @if($canAccess('dashboard'))
                        @php $active = request()->is('admin') || (request()->is('staff/waiter') && $homeUrl == '/staff/waiter'); @endphp
                        <a href="{{ $homeUrl }}" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            Dashboard
                        </a>
                        @endif

                        @if($canAccess('menu_manager'))
                        @php $active = request()->is('admin/menus') || request()->is('admin/menus*'); @endphp
                        <a href="/admin/menus" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                            Menu Manager
                        </a>
                        @endif

                        @if($canAccess('restaurant_tables'))
                        @php $active = request()->is('admin/tables') || request()->is('admin/tables*'); @endphp
                        <a href="/admin/tables" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            Restaurant Tables
                        </a>
                        @endif

                        @if($canAccess('hotel_room_manager'))
                        @php $active = request()->is('admin/rooms') || request()->is('admin/rooms*'); @endphp
                        <a href="/admin/rooms" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            Hotel Room Manager
                        </a>
                        @endif

                        @if($canAccess('inventory'))
                        @php $active = request()->is('admin/inventory') || request()->is('admin/inventory*'); @endphp
                        <a href="/admin/inventory" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            Inventory
                        </a>
                        @endif

                        <div class="pt-4 pb-2">
                            <p class="px-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Staff Panels</p>
                        </div>

                        @if($canAccess('take_order'))
                        @php $active = request()->is('staff/take-order') || request()->is('staff/take-order*'); @endphp
                        <a href="/staff/take-order" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Take Restaurant Order
                        </a>
                        @endif

                        @if($canAccess('take_room_service'))
                        @php $active = request()->is('staff/room-service') || request()->is('staff/room-service*'); @endphp
                        <a href="/staff/room-service" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            Take Room Service
                        </a>
                        @endif

                        @if($canAccess('hotel_reception'))
                        @php $active = request()->is('staff/hotel-reception') || request()->is('staff/hotel-reception*'); @endphp
                        <a href="/staff/hotel-reception" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Hotel Reception Cashier
                        </a>
                        @endif

                        @if($canAccess('cashier_panel'))
                        @php $active = request()->is('staff/cashier') || request()->is('staff/cashier*'); @endphp
                        <a href="/staff/cashier" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2-2v14a2 2 0 002 2z"></path></svg>
                            Cashier Dashboard
                        </a>
                        @endif

                        @if($canAccess('waiter_dashboard'))
                        @php $active = request()->is('staff/waiter') || request()->is('staff/waiter*'); @endphp
                        <a href="/staff/waiter" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2-2v10a2 2 0 002 2z"></path></svg>
                            Waiter Dashboard
                        </a>
                        @endif

                        @if($canAccess('kitchen_display'))
                        @php $active = request()->is('staff/kitchen') || request()->is('staff/kitchen*'); @endphp
                        <a href="/staff/kitchen" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            Kitchen Display
                        </a>
                        @endif

                        @if($canAccess('bar_display'))
                        @php $active = request()->is('staff/bar') || request()->is('staff/bar*'); @endphp
                        <a href="/staff/bar" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2-2v10a2 2 0 002 2z"></path></svg>
                            Bar Display
                        </a>
                        @endif

                        <div class="pt-4 pb-2">
                            <p class="px-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Management</p>
                        </div>
                        
                        @if($canAccess('staff_roles'))
                        @php $active = request()->is('admin/staff') || request()->is('admin/staff*'); @endphp
                        <a href="/admin/staff" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Staff Accounts
                        </a>
                        @endif
                        
                        @if($canAccess('reports'))
                        @php $active = request()->is('admin/reports') || request()->is('admin/reports*'); @endphp
                        <a href="/admin/reports" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Reports & Analytics
                        </a>
                        @endif

                        @if($canAccess('support'))
                        @php $active = request()->is('admin/support') || request()->is('admin/support*'); @endphp
                        <a href="/admin/support" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            Priority Support
                        </a>
                        @endif

                        @if($canAccess('settings'))
                        @php $active = request()->is('admin/settings') || request()->is('admin/settings*'); @endphp
                        <a href="/admin/settings" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            System Settings
                        </a>
                        @endif
                        
                        @if($canAccess('license'))
                        @php $active = request()->is('admin/license') || request()->is('admin/license*'); @endphp
                        <a href="/admin/license" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            License Manager
                        </a>
                        @endif
                    </nav>

                    <div class="p-4 pb-6 border-t border-slate-200 dark:border-[#333333] shrink-0" style="padding-bottom: max(1.5rem, env(safe-area-inset-bottom));">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center overflow-hidden">
                                <div class="w-10 h-10 rounded-full bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold text-lg shrink-0">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <div class="ml-3 overflow-hidden">
                                    <p class="text-sm font-bold text-slate-800 dark:text-slate-200 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $restaurant->name ?? '' }}</p>
                                </div>
                            </div>
                            
                            <a href="{{ route('logout') }}" class="p-2 text-red-500 hover:bg-red-50 rounded-xl transition-colors dark:hover:bg-red-500/10 dark:text-red-400 shrink-0 ml-2" title="Logout">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            </a>
                        </div>
                    </div>
                </aside>

                <!-- Main Content -->
                <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900">
                    <!-- Top Header -->
                    <header class="h-14 sm:h-16 border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-[#0a0a0a]/80 backdrop-blur-md flex items-center justify-between px-4 sm:px-6 z-30 sticky top-0">
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="toggleMobileSidebar()" class="lg:hidden p-1.5 -ml-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-white rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                            </button>
                            <h1 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-slate-100 truncate">{{ $title ?? 'Dashboard' }}</h1>
                            @if(isset($isExpired) && $isExpired)
                                <span class="px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-600 border border-red-200 ml-2 animate-pulse dark:border-red-800 dark:text-red-400">EXPIRED</span>
                            @elseif(isset($dLeft) && $dLeft <= 30)
                                <a href="https://drestro.com/dashboard/billing" target="_blank" class="px-3 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-700 border border-amber-300 ml-2 hover:bg-amber-200 transition-colors dark:bg-amber-500/20 dark:border-amber-500/40 dark:text-amber-400 flex items-center gap-1 shadow-sm cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    RENEW SOON ({{ $dLeft }} Days)
                                </a>
                            @endif
                        </div>
                        
                        <div class="flex items-center gap-2 sm:gap-4">
                            @php
                                $now = now();
                                $dateStrForClock = $now->format('D, M j');
                            @endphp
                            
                            <div class="hidden md:flex items-center text-sm font-bold text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-800/50 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 font-mono" id="live-header-clock">
                                {{ strtoupper($dateStrForClock) }} | {{ $now->format('h:i:s A') }}
                            </div>

                            <script>
                                (function() {
                                    function updateClock() {
                                        const clockEl = document.getElementById('live-header-clock');
                                        if (clockEl) {
                                            const now = new Date();
                                            const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
                                            clockEl.textContent = "{{ strtoupper($dateStrForClock) }} | " + timeStr;
                                        }
                                    }
                                    updateClock();
                                    setInterval(updateClock, 1000);
                                })();
                            </script>

                            <button onclick="togglePosTheme()" class="hidden sm:flex p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-white bg-slate-50 hover:bg-slate-100 dark:bg-[#111] dark:hover:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-800 transition-colors shadow-sm" title="Toggle Dark Mode">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                            </button>

                            @if(request()->is('admin/settings'))
                            <button type="submit" form="settings-form" class="h-8 px-4 bg-rose-600 hover:bg-rose-700 text-white font-bold text-[10px] uppercase tracking-wider rounded-xl shadow-md shadow-rose-600/20 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer leading-none">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                <span>Save Settings</span>
                            </button>
                            @endif
                        </div>
                    </header>

                    <!-- Page Content -->
                    <div id="main-content-viewport" class="flex-1 overflow-y-auto p-4 sm:p-6 sm:pb-6 lg:p-8 lg:pb-8" style="padding-bottom: {{ request()->is('staff/take-order') ? '0px' : 'calc(5rem + env(safe-area-inset-bottom, 0px))' }};">
                        @if($isLocked)
                            <div class="flex flex-col items-center justify-center h-full text-center">
                                <div class="w-24 h-24 bg-red-100 text-red-500 rounded-full flex items-center justify-center mb-6 shadow-xl shadow-red-500/20">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                </div>
                                <h2 class="text-3xl font-black text-slate-800 mb-2 dark:text-slate-200">Subscription Expired</h2>
                                <p class="text-lg text-slate-500 max-w-md mb-8 dark:text-slate-400">
                                    Your DRestro POS trial/subscription has expired and the system has been locked. Please renew your subscription online to instantly restore access.
                                </p>
                                <a href="https://drestro.com/dashboard/billing" target="_blank" class="px-8 py-3.5 bg-rose-600 hover:bg-rose-500 text-white rounded-xl font-bold text-lg transition-all shadow-lg shadow-rose-600/20 flex items-center gap-2 active:scale-95">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    Renew Subscription Online
                                </a>
                            </div>
                        @else
                            {{ $slot }}
                        @endif
                    </div>
                </main>

                <!-- Mobile Bottom Navigation -->
                <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-lg border-t border-slate-200 flex items-center justify-around px-1 z-50 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] dark:bg-slate-900/95 dark:border-slate-700/50" style="height: calc(4rem + env(safe-area-inset-bottom, 0px)); padding-bottom: env(safe-area-inset-bottom, 0px);">
                    
                    @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                    <a href="/admin" class="flex flex-col items-center justify-center w-full h-full space-y-1 text-slate-500 hover:text-rose-600 transition-colors {{ request()->is('admin') ? 'text-rose-600 dark:text-rose-500' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Home</span>
                    </a>
                    @endif

                    @if(!$isLocked && $canAccess('take_order'))
                    <a href="/staff/take-order" class="flex flex-col items-center justify-center w-full h-full space-y-1 text-slate-500 hover:text-rose-600 transition-colors {{ request()->is('staff/take-order') ? 'text-rose-600 dark:text-rose-500' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-[10px] font-bold uppercase tracking-wider">POS</span>
                    </a>
                    @endif

                    @if(!$isLocked && $canAccess('restaurant_tables'))
                    <a href="/admin/tables" class="flex flex-col items-center justify-center w-full h-full space-y-1 text-slate-500 hover:text-rose-600 transition-colors {{ request()->is('admin/tables') ? 'text-rose-600 dark:text-rose-500' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Tables</span>
                    </a>
                    @endif
                    
                    <!-- Dark Mode -->
                    <button onclick="togglePosTheme()" class="flex flex-col items-center justify-center w-full h-full space-y-1 text-slate-500 hover:text-rose-600 transition-colors dark:text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Theme</span>
                    </button>

                    <!-- Menu / Sidebar Toggle -->
                    <button onclick="toggleMobileSidebar()" class="flex flex-col items-center justify-center w-full h-full space-y-1 text-slate-500 hover:text-rose-600 transition-colors dark:text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        <span class="text-[10px] font-bold uppercase tracking-wider">More</span>
                    </button>

                </nav>
            </div>
            
            <style>
                [x-cloak] { display: none !important; }
                @media print {
                    @page { margin: 1cm; size: A4 portrait; }
                    body { background-color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    aside, header, .print-hidden { display: none !important; }
                    main { height: auto !important; overflow: visible !important; }
                    .flex-1.overflow-y-auto { overflow: visible !important; padding: 0 !important; }
                    .grid { gap: 1rem !important; }
                    .shadow-sm { box-shadow: none !important; border: 1px solid #e2e8f0 !important; }
                }
            </style>

            <script>
                function downloadPDF(filename) {
                    alert('To save this report as a PDF:\n\n1. The Print Dialog will now open.\n2. Change your Printer "Destination" to "Save as PDF".\n3. Click Save!');
                    window.print();
                }
            </script>

            @livewireScripts
        </div>
    </div>
    </body>
</html>

EOT;

$icon_content = <<<'EOT'
<?xml version="1.0" encoding="UTF-8"?>
<svg id="Layer_1" xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 514.7 564.5">
  <!-- Generator: Adobe Illustrator 30.0.0, SVG Export Plug-In . SVG Version: 2.1.1 Build 123)  -->
  <defs>
    <style>
      .st0 {
        fill: #ed1c24;
      }
    </style>
  </defs>
  <path class="st0" d="M248,115.4H95.2v346.8h152.9c98.1,0,177.9-77.8,177.9-173.4s-79.8-173.4-177.9-173.4ZM261.7,310.7c19.9,18.8,57.4,40.9,72.8,61,10.6,13.7-4.2,29.3-17.5,20.7-5.1-3.3-16.4-16-21.3-21.1-13.6-14.1-25.7-29.9-40.3-43l4.3,63.1c-.7,10.1,3.6,26.2-9.6,29.8-10.1,2.7-18-5-18.1-14.9,0-1.8,1-3.6,1.1-5.3,0-24.7,3.5-49.1,4.2-73.7-17.2,16.7-31.8,35.8-48.3,53.1-7.5,7.8-18.2,22.1-29.8,11.7-14.4-13,9.4-29.4,18.6-37.7,6.6-5.9,55.8-45,55.2-48.7-4.1-4.4-8.3-8.8-12.7-12.9-2.9-2.7-12.1-11.5-14.8-12.8-9.6-4.6-16.4,3.5-28.7-4.3-8.9-5.7-29.5-29.5-36.7-38.6-1.5-1.8-7.7-8.6-5.8-10.6,2.5-.4,4,.8,5.8,2.1,7,4.9,15.9,16.5,22.8,22.8,1.7,1.5,12.4,11.1,13.3,11.1,1.1,0,2.3-3,1.1-4.2l-35.5-37.8,2.5-2.5,40.4,35,1.1-3.2-36.7-36.6,2.6-2.6c4.1,2.6,40,35.7,42,33.9.1-.1,1.1-3.1.1-3.7l-36.8-34.5,1.1-4.2c2.2,0,31.1,23.2,35,26.5,7.4,6.3,19,14.7,19.6,25.1.2,3-1,5-1,7.3.1,11.3,19.9,22.7,27.6,30.2-1.2-11.3,3.7-25.3-.5-36.1-2-5.1-12-11.7-15.6-17.3-12.2-18.7-3.8-62.9,20.4-67.1,23-4,32.1,33.2,30.9,50-1.4,18.8-8.7,18.8-18.7,30.1-7,7.9-2.4,27.2-2.6,37.2,26.1-23,49.7-50.7,75.4-73.8,3.7-3.4,13.2-13.5,17.8-8.8,6.8,16.5-8.1,36.2-18.3,48.6-6.5,7.9-38.1,39.8-46.8,40.3-2.6.2-4.5-1.7-6.6-1.9-5.9-.6-13.2,9.4-17.2,13.2-.3,1.4,3.1,4.1,4.2,5.2Z"/>
  <path class="st0" d="M240.6,47.7H32.6v482.1h208c135.7,0,247.9-109.5,247.9-241S376.3,47.7,240.6,47.7ZM248,494.2H63.1V83.4h184.9c115.8,0,210,92.1,210,205.4s-94.2,205.4-210,205.4Z"/>
</svg>

EOT;

// Ensure target directories exist
if (!is_dir(dirname($auth_controller_file))) { mkdir(dirname($auth_controller_file), 0755, true); }
if (!is_dir(dirname($routes_file))) { mkdir(dirname($routes_file), 0755, true); }
if (!is_dir(dirname($middleware_file))) { mkdir(dirname($middleware_file), 0755, true); }
if (!is_dir(dirname($app_layout_file))) { mkdir(dirname($app_layout_file), 0755, true); }
if (!is_dir(dirname($images_icon_file))) { mkdir(dirname($images_icon_file), 0755, true); }

file_put_contents($auth_controller_file, $auth_controller_content);
file_put_contents($routes_file, $routes_content);
file_put_contents($middleware_file, $middleware_content);
file_put_contents($app_layout_file, $app_layout_content);
file_put_contents($icon_file, $icon_content);
file_put_contents($images_icon_file, $icon_content);

// Clean views and config caches
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

if (function_exists('opcache_reset')) { opcache_reset(); }
\Illuminate\Support\Facades\Artisan::call('config:clear');
\Illuminate\Support\Facades\Artisan::call('route:clear');
\Illuminate\Support\Facades\Artisan::call('view:clear');

echo "<h1>Deployment Successful!</h1>";
echo "<p>All files deployed successfully:</p>";
echo "<ul>";
echo "<li>AuthController.php</li>";
echo "<li>routes/web.php</li>";
echo "<li>TenantMiddleware.php</li>";
echo "<li>app.blade.php</li>";
echo "<li>public/icon.svg</li>";
echo "<li>public/images/icon.svg</li>";
echo "</ul>";
echo "<p>Cache, Route Cache, and View Cache cleared.</p>";
echo "<p>OPCache reset successfully.</p>";
