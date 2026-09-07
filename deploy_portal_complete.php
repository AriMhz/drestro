<?php
// Unified Deployment Script for Customer, Finance, and QR Fixes
$routes_file = __DIR__ . '/../routes/web.php';
$middleware_file = __DIR__ . '/../app/Http/Middleware/TenantMiddleware.php';
$app_layout_file = __DIR__ . '/../resources/views/components/layouts/app.blade.php';
$helpers_file = __DIR__ . '/../app/helpers.php';
$customer_php_file = __DIR__ . '/../app/Livewire/Admin/CustomerManager.php';
$customer_blade_file = __DIR__ . '/../resources/views/livewire/admin/customer-manager.blade.php';
$finance_php_file = __DIR__ . '/../app/Livewire/Admin/FinanceManager.php';
$finance_blade_file = __DIR__ . '/../resources/views/livewire/admin/finance-manager.blade.php';
$print_qr_file = __DIR__ . '/../resources/views/print/qr.blade.php';
$table_manager_file = __DIR__ . '/../resources/views/livewire/admin/table-manager.blade.php';

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
        Route::get('/ordered-goods', App\Livewire\Admin\OrderedGoods::class)->name('ordered-goods');
        Route::get('/support', App\Livewire\Admin\SupportTickets::class)->name('support');
        Route::get('/customers', App\Livewire\Admin\CustomerManager::class)->name('customers');
        Route::get('/finance', App\Livewire\Admin\FinanceManager::class)->name('finance');
        
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

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">Create 4-Digit Login PIN (Optional)</label>
                        <input type="text" name="pin" pattern="\d{4}" maxlength="4" placeholder="e.g. 1234" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 outline-none transition-all dark:bg-[#1a1a1a] dark:border-slate-800 dark:text-white">
                        <p class="text-[10px] text-slate-500 mt-1 dark:text-slate-400">You can use a 4-digit PIN to quickly log into the POS system instead of your password.</p>
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
        'password' => 'required|min:6|confirmed',
        'pin' => 'nullable|digits:4'
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
        'pin' => $request->pin,
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
            $tenantSlug = session('tenant_slug');
            Log::info('TenantMiddleware: tenant_slug from session', ['tenant_slug' => $tenantSlug]);

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

            // For production, if they access root and have no tenant, they should be redirected to the POS login
            if (app()->environment('production')) {
                return redirect('/login');
            }
            
            Log::warning('TenantMiddleware: NO restaurant found, falling through!');
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

                    <!-- Restaurant Info & Active Plan Badge -->
                    @if($restaurant)
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-100 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#E53935] to-red-600 dark:from-red-500 dark:to-red-650 text-white flex items-center justify-center font-black text-sm shadow-sm shrink-0 uppercase">
                            {{ substr($restaurant->name, 0, 2) }}
                        </div>
                        <div class="overflow-hidden">
                            <h4 class="font-bold text-slate-800 dark:text-slate-200 text-xs truncate" title="{{ $restaurant->name }}">
                                {{ $restaurant->name }}
                            </h4>
                            @php 
                                $planName = $activeLicense['plan'] ?? 'Free';
                            @endphp
                            <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                👑 {{ $planName }}
                            </span>
                        </div>
                    </div>
                    @endif
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
                            return 'group flex items-center px-4 py-3 rounded-xl font-medium transition-all ' . 
                                ($isActive 
                                    ? 'bg-[#E53935] text-white shadow-[0_4px_12px_rgba(229,57,53,0.2)]' 
                                    : 'text-slate-600 dark:text-gray-400 hover:text-[#111111] dark:hover:text-white hover:bg-slate-100 dark:hover:bg-[#2A2A2A] dark:bg-[#222222]');
                        };
                        
                        $getIconClass = function($isActive) {
                            return 'w-5 h-5 mr-3 shrink-0 transition-colors ' . 
                                ($isActive 
                                    ? 'text-white' 
                                    : 'text-slate-400 dark:text-gray-400 group-hover:text-[#111111] dark:group-hover:text-white');
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

                        @php $active = request()->is('admin/finance') || request()->is('admin/finance*'); @endphp
                        <a href="/admin/finance" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Finance
                        </a>

                        @php $active = request()->is('admin/customers') || request()->is('admin/customers*'); @endphp
                        <a href="/admin/customers" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Customers
                        </a>

                        @if((auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin') && str_contains(strtolower($activeLicense['plan'] ?? 'Free'), 'platinum'))
                        <a href="https://drestro.com/dashboard" target="_blank" rel="noopener noreferrer" class="group flex items-center px-4 py-3 rounded-xl font-medium transition-all text-slate-600 dark:text-gray-400 hover:text-[#111111] dark:hover:text-white hover:bg-slate-100 dark:hover:bg-[#2A2A2A] dark:bg-[#222222]">
                            <svg class="w-5 h-5 mr-3 shrink-0 text-slate-400 dark:text-gray-400 group-hover:text-[#111111] dark:group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Manage Outlets
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

                        @if($isSuperAdmin || $canAccess('ordered_goods'))
                        @php $active = request()->is('admin/ordered-goods'); @endphp
                        <a href="/admin/ordered-goods" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                            Ordered Goods
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
                            <h1 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-slate-100 truncate lg:hidden">{{ $title ?? 'Dashboard' }}</h1>
                            @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                                @if(isset($isExpired) && $isExpired)
                                    <span class="px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-600 border border-red-200 ml-2 animate-pulse dark:border-red-800 dark:text-red-400">EXPIRED</span>
                                @elseif(isset($dLeft) && $dLeft <= 30)
                                    <a href="https://drestro.com/dashboard/billing" target="_blank" class="px-3 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-700 border border-amber-300 ml-2 hover:bg-amber-200 transition-colors dark:bg-amber-500/20 dark:border-amber-500/40 dark:text-amber-400 flex items-center gap-1 shadow-sm cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        RENEW SOON ({{ $dLeft }} Days)
                                    </a>
                                @endif
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

$helpers_content = <<<'EOT'
<?php

if (!function_exists('current_restaurant')) {
    function current_restaurant() {
        if (app()->bound('restaurant')) {
            return app('restaurant');
        }
        return \App\Models\Restaurant::first();
    }
}

if (!function_exists('get_tenant_menu_url')) {
    function get_tenant_menu_url($tableId = null) {
        $restaurant = current_restaurant();
        $slug = $restaurant ? $restaurant->slug : 'demo';
        $host = request()->getHost();
        $scheme = request()->getScheme();
        
        $cleanHost = $host;
        if (str_contains($host, 'portal.drestro.com')) {
            $cleanHost = 'portal.drestro.com';
        } elseif (str_contains($host, 'localhost')) {
            $parts = explode(':', $host);
            $domainPart = $parts[0];
            $portPart = $parts[1] ?? '8000';
            
            $subparts = explode('.', $domainPart);
            if (count($subparts) > 1) {
                // If it is 'tenant.localhost', slice off the tenant prefix
                $domainPart = 'localhost';
            }
            $cleanHost = $domainPart . ':' . $portPart;
        } else {
            $parts = explode('.', $host);
            if (count($parts) > 2) {
                $cleanHost = implode('.', array_slice($parts, -2));
            }
        }
        
        $url = $scheme . '://' . $slug . '.' . $cleanHost . '/menu';
        if ($tableId) {
            $url .= '?table=' . $tableId;
        }
        return $url;
    }
}

EOT;

$customer_php = <<<'EOT'
<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class CustomerManager extends Component
{
    // Search & KPI
    public $searchQuery = '';
    public $toReceive = 0;
    public $toPay = 0;
    public $netToReceive = 0;

    // Modals
    public $showModal = false;
    public $showDetails = false;
    public $editingCustomerId = null;

    // Basic Details
    public $name = '';
    public $phone = '';
    public $email = '';
    public $loyaltyDiscount = 0;
    public $openingBalance = 0;
    public $balanceType = 'collect'; // 'collect' (To Receive / Dr) or 'pay' (To Pay / Cr)
    public $dob = '';
    public $group = '';

    // Billing & Credit Details
    public $legalName = '';
    public $taxNumber = '';
    public $address = '';
    public $creditLimit = 0;
    public $creditTerm = 0;

    // Dining Preferences
    public $favoriteDish = '';
    public $preferredSeating = '';
    public $dietaryType = '';
    public $allergies = '';
    public $preferredVisitingTime = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'phone' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:255',
        'loyaltyDiscount' => 'nullable|numeric|min:0|max:100',
        'openingBalance' => 'nullable|numeric|min:0',
        'balanceType' => 'required|in:collect,pay',
        'dob' => 'nullable|string',
        'group' => 'nullable|string',
        'legalName' => 'nullable|string|max:255',
        'taxNumber' => 'nullable|string|max:100',
        'address' => 'nullable|string|max:255',
        'creditLimit' => 'nullable|numeric|min:0',
        'creditTerm' => 'nullable|integer|min:0',
        'favoriteDish' => 'nullable|string',
        'preferredSeating' => 'nullable|string',
        'dietaryType' => 'nullable|string',
        'allergies' => 'nullable|string',
        'preferredVisitingTime' => 'nullable|string',
    ];

    public function mount()
    {
        // Self-healing migration for customers table
        if (!Schema::hasTable('customers')) {
            try {
                Schema::create('customers', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('restaurant_id');
                    $table->string('name');
                    $table->string('phone')->nullable();
                    $table->string('email')->nullable();
                    $table->float('loyalty_discount')->default(0);
                    $table->float('opening_balance')->default(0);
                    $table->string('balance_type')->default('collect');
                    $table->string('dob')->nullable();
                    $table->string('group')->nullable();
                    $table->string('legal_name')->nullable();
                    $table->string('tax_number')->nullable();
                    $table->string('address')->nullable();
                    $table->float('credit_limit')->default(0);
                    $table->integer('credit_term')->default(0);
                    $table->string('favorite_dish')->nullable();
                    $table->string('preferred_seating')->nullable();
                    $table->string('dietary_type')->nullable();
                    $table->string('allergies')->nullable();
                    $table->string('preferred_visiting_time')->nullable();
                    $table->float('due_amount')->default(0);
                    $table->timestamps();
                });
            } catch (\Exception $e) {
                // Ignore if fails
            }
        }
        
        $this->updateKPIs();
    }

    public function updateKPIs()
    {
        $restaurant = current_restaurant();
        if (!$restaurant) return;

        $customers = DB::table('customers')->where('restaurant_id', $restaurant->id)->get();

        $this->toReceive = 0;
        $this->toPay = 0;

        foreach ($customers as $c) {
            $amt = $c->opening_balance + $c->due_amount;
            if ($c->balance_type === 'collect') {
                $this->toReceive += $amt;
            } else {
                $this->toPay += $amt;
            }
        }

        $this->netToReceive = $this->toReceive - $this->toPay;
    }

    public function toggleAddModal()
    {
        $this->resetForm();
        $this->editingCustomerId = null;
        $this->showModal = true;
    }

    public function resetForm()
    {
        $this->name = '';
        $this->phone = '';
        $this->email = '';
        $this->loyaltyDiscount = 0;
        $this->openingBalance = 0;
        $this->balanceType = 'collect';
        $this->dob = '';
        $this->group = '';
        $this->legalName = '';
        $this->taxNumber = '';
        $this->address = '';
        $this->creditLimit = 0;
        $this->creditTerm = 0;
        $this->favoriteDish = '';
        $this->preferredSeating = '';
        $this->dietaryType = '';
        $this->allergies = '';
        $this->preferredVisitingTime = '';
    }

    public function editCustomer($id)
    {
        $c = DB::table('customers')->where('id', $id)->first();
        if (!$c) return;

        $this->editingCustomerId = $c->id;
        $this->name = $c->name;
        $this->phone = $c->phone ?? '';
        $this->email = $c->email ?? '';
        $this->loyaltyDiscount = $c->loyalty_discount;
        $this->openingBalance = $c->opening_balance;
        $this->balanceType = $c->balance_type;
        $this->dob = $c->dob ?? '';
        $this->group = $c->group ?? '';
        $this->legalName = $c->legal_name ?? '';
        $this->taxNumber = $c->tax_number ?? '';
        $this->address = $c->address ?? '';
        $this->creditLimit = $c->credit_limit;
        $this->creditTerm = $c->credit_term;
        $this->favoriteDish = $c->favorite_dish ?? '';
        $this->preferredSeating = $c->preferred_seating ?? '';
        $this->dietaryType = $c->dietary_type ?? '';
        $this->allergies = $c->allergies ?? '';
        $this->preferredVisitingTime = $c->preferred_visiting_time ?? '';

        $this->showModal = true;
    }

    public function saveCustomer()
    {
        $this->validate();

        $restaurant = current_restaurant();
        if (!$restaurant) return;

        $data = [
            'restaurant_id' => $restaurant->id,
            'name' => $this->name,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
            'loyalty_discount' => floatval($this->loyaltyDiscount),
            'opening_balance' => floatval($this->openingBalance),
            'balance_type' => $this->balanceType,
            'dob' => $this->dob ?: null,
            'group' => $this->group ?: null,
            'legal_name' => $this->legalName ?: null,
            'tax_number' => $this->taxNumber ?: null,
            'address' => $this->address ?: null,
            'credit_limit' => floatval($this->creditLimit),
            'credit_term' => intval($this->creditTerm),
            'favorite_dish' => $this->favoriteDish ?: null,
            'preferred_seating' => $this->preferredSeating ?: null,
            'dietary_type' => $this->dietaryType ?: null,
            'allergies' => $this->allergies ?: null,
            'preferred_visiting_time' => $this->preferredVisitingTime ?: null,
            'updated_at' => now(),
        ];

        if ($this->editingCustomerId) {
            DB::table('customers')->where('id', $this->editingCustomerId)->update($data);
        } else {
            $data['due_amount'] = 0;
            $data['created_at'] = now();
            DB::table('customers')->insert($data);
        }

        $this->showModal = false;
        $this->resetForm();
        $this->updateKPIs();
        session()->flash('message', 'Customer saved successfully!');
    }

    public function deleteCustomer($id)
    {
        DB::table('customers')->where('id', $id)->delete();
        $this->updateKPIs();
        session()->flash('message', 'Customer deleted successfully!');
    }

    public function render()
    {
        $restaurant = current_restaurant();
        $customers = collect();

        if ($restaurant) {
            $query = DB::table('customers')->where('restaurant_id', $restaurant->id);

            if (!empty($this->searchQuery)) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->searchQuery . '%')
                      ->orWhere('phone', 'like', '%' . $this->searchQuery . '%')
                      ->orWhere('email', 'like', '%' . $this->searchQuery . '%');
                });
            }

            $customers = $query->orderBy('created_at', 'desc')->get();
        }

        return view('livewire.admin.customer-manager', [
            'customers' => $customers
        ])->layout('components.layouts.app', ['title' => 'Customers']);
    }
}

EOT;

$customer_blade = <<<'EOT'
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Customers</h1>
            <p class="text-sm text-slate-500 mt-1 dark:text-slate-400">Manage client relationships, loyalty discounts, and credit records.</p>
        </div>
        <button wire:click="toggleAddModal" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md active:scale-95 transition-all text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add New Customer
        </button>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between dark:bg-slate-900 dark:border-slate-800">
            <div class="flex items-center gap-2.5 text-emerald-600 dark:text-emerald-400 mb-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 17l-4 4m0 0l-4-4m4 4V3"></path></svg>
                </div>
                <span class="text-sm font-bold uppercase tracking-wider">To Receive</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 block mb-0.5">Total Receivables</span>
                <span class="text-2xl font-black text-slate-800 dark:text-white">Rs. {{ number_format($toReceive, 0) }}</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between dark:bg-slate-900 dark:border-slate-800">
            <div class="flex items-center gap-2.5 text-rose-500 dark:text-rose-450 mb-2">
                <div class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7l4-4m0 0l4 4m-4-4v18"></path></svg>
                </div>
                <span class="text-sm font-bold uppercase tracking-wider">To Pay</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 block mb-0.5">Total Payables</span>
                <span class="text-2xl font-black text-slate-800 dark:text-white">Rs. {{ number_format($toPay, 0) }}</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between dark:bg-slate-900 dark:border-slate-800">
            <div class="flex items-center gap-2.5 text-blue-500 dark:text-blue-400 mb-2">
                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"></path></svg>
                </div>
                <span class="text-sm font-bold uppercase tracking-wider">Net To Receive</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 block mb-0.5">Balance Net</span>
                <span class="text-2xl font-black text-slate-800 dark:text-white">Rs. {{ number_format($netToReceive, 0) }}</span>
            </div>
        </div>
    </div>

    <!-- Search & List -->
    <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm dark:bg-slate-900 dark:border-slate-800">
        <div class="p-6 border-b border-slate-100 dark:border-slate-850">
            <div class="relative max-w-md w-full">
                <input wire:model.live.debounce.300ms="searchQuery" type="text" placeholder="Search customer by name, phone or email..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none dark:bg-slate-800 dark:border-slate-700 dark:text-white">
                <svg class="w-4.5 h-4.5 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </div>

        @if(session()->has('message'))
        <div class="mx-6 mt-4 p-4 bg-emerald-50 text-emerald-800 border border-emerald-100 rounded-xl text-sm font-semibold dark:bg-emerald-950/20 dark:text-emerald-450 dark:border-emerald-900/30">
            {{ session('message') }}
        </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-400 uppercase tracking-wider dark:bg-slate-950 dark:border-slate-850">
                        <th class="px-6 py-4">SN</th>
                        <th class="px-6 py-4">Customer</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4">Phone Number</th>
                        <th class="px-6 py-4">DOB</th>
                        <th class="px-6 py-4">Loyalty Dis</th>
                        <th class="px-6 py-4">Due Amount</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-850">
                    @forelse($customers as $index => $c)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">
                          <div className="font-semibold text-slate-850 dark:text-white text-sm">{{ $c->name }}</div>
                          @if($c->group)
                          <span class="inline-block mt-0.5 px-2 py-0.5 text-[10px] font-bold bg-slate-100 text-slate-600 rounded-md uppercase dark:bg-slate-800 dark:text-slate-400">{{ $c->group }}</span>
                          @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-450">{{ $c->email ?: '-' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-650 dark:text-gray-300 font-semibold">{{ $c->phone ?: '-' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-450">{{ $c->dob ?: '-' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-450 font-bold">
                            @if($c->loyalty_discount > 0)
                            <span class="text-emerald-600 dark:text-emerald-450">{{ $c->loyalty_discount }}%</span>
                            @else
                            -
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-slate-800 dark:text-white">
                            @php $bal = $c->opening_balance + $c->due_amount; @endphp
                            @if($bal > 0)
                            <span class="{{ $c->balance_type === 'collect' ? 'text-emerald-600 dark:text-emerald-450' : 'text-rose-500' }}">
                                Rs. {{ number_format($bal, 0) }} 
                                <span class="text-[10px] uppercase font-bold">({{ $c->balance_type === 'collect' ? 'Dr' : 'Cr' }})</span>
                            </span>
                            @else
                            Rs. 0
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2.5">
                                <button wire:click="editCustomer({{ $c->id }})" class="p-1.5 text-slate-400 hover:text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-lg transition-colors" title="Edit Customer">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <button wire:click="deleteCustomer({{ $c->id }})" wire:confirm="Are you sure you want to delete this customer?" class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-lg transition-colors" title="Delete Customer">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-12 text-center text-slate-400 dark:text-slate-500 bg-white dark:bg-slate-900">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            No customers found. Create a new customer above.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form -->
    @if($showModal)
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden shadow-2xl flex flex-col animate-in scale-in duration-300">
            <div class="px-6 py-4 border-b border-slate-150 dark:border-[#333333] flex justify-between items-center bg-slate-50 dark:bg-[#222222]">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white">
                    {{ $editingCustomerId ? 'Edit Customer' : 'Add Customer' }}
                </h3>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-650 dark:hover:text-white p-1 rounded-lg transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-6 text-left">
                <!-- 1. Basic Customer Details -->
                <div class="space-y-4">
                    <h4 class="text-sm font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider border-b pb-1 border-slate-100 dark:border-slate-850">1. Basic Customer Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Customer Full Name *</label>
                            <input wire:model="name" type="text" placeholder="Enter Customer Name" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                            @error('name') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Phone Number</label>
                            <input wire:model="phone" type="text" placeholder="e.g. +977 98XXXXXXX" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                            @error('phone') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Email</label>
                            <input wire:model="email" type="email" placeholder="Email Address" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                            @error('email') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Loyalty Discount (%)</label>
                            <input wire:model="loyaltyDiscount" type="number" step="0.01" placeholder="0.00 %" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                            @error('loyaltyDiscount') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Opening Balance Type</label>
                            <select wire:model="balanceType" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium">
                                <option value="collect">To Collect (Dr) ⬇</option>
                                <option value="pay">To Pay (Cr) ⬆</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Opening Balance Amount (Rs.)</label>
                            <input wire:model="openingBalance" type="number" placeholder="Rs. 0" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Date of Birth</label>
                            <input wire:model="dob" type="text" placeholder="YYYY-MM-DD" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Group / Segment</label>
                        <input wire:model="group" type="text" placeholder="e.g. VIP, Corporate, Regular" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                    </div>
                </div>

                <!-- 2. Billing & Credit Details -->
                <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-850">
                    <h4 class="text-sm font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider border-b pb-1 border-slate-100 dark:border-slate-850">2. Billing & Credit Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Legal Name</label>
                            <input wire:model="legalName" type="text" placeholder="Company Legal Name" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Tax / VAT Number</label>
                            <input wire:model="taxNumber" type="text" placeholder="PAN or VAT ID" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Address</label>
                        <input wire:model="address" type="text" placeholder="Billing Address" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Credit Limit (Rs.)</label>
                            <input wire:model="creditLimit" type="number" placeholder="Rs. 0" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Credit Term (Days)</label>
                            <input wire:model="creditTerm" type="number" placeholder="Days to pay" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                        </div>
                    </div>
                </div>

                <!-- 3. Dining Preferences -->
                <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-850">
                    <h4 class="text-sm font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider border-b pb-1 border-slate-100 dark:border-slate-850">3. Dining Preferences</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Favorite Dish</label>
                            <input wire:model="favoriteDish" type="text" placeholder="e.g. Momo, Pizza" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Preferred Seating</label>
                            <input wire:model="preferredSeating" type="text" placeholder="e.g. Window, Balcony" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Dietary Type</label>
                            <input wire:model="dietaryType" type="text" placeholder="e.g. Vegetarian, Vegan" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Allergies & Restrictions</label>
                            <input wire:model="allergies" type="text" placeholder="e.g. Peanuts, Gluten" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-gray-300 mb-1.5">Preferred Visiting Time</label>
                            <input wire:model="preferredVisitingTime" type="text" placeholder="e.g. 7 PM - 9 PM" class="w-full px-4 py-2.5 bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/40 text-sm font-medium" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-150 dark:border-[#333333] flex justify-end gap-3 bg-slate-50 dark:bg-[#222222]">
                <button wire:click="resetForm" type="button" class="px-5 py-2.5 border border-slate-200 dark:border-[#333333] hover:bg-slate-100 dark:hover:bg-[#111111] text-slate-650 dark:text-white text-sm font-bold rounded-xl transition-colors">
                    Reset
                </button>
                <button wire:click="saveCustomer" type="button" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl transition-colors shadow-md">
                    Save Customer
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

EOT;

$finance_php = <<<'EOT'
<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class FinanceManager extends Component
{
    // Navigation / Tab
    public $activeView = 'list'; // 'list', 'daybook', 'transactions', 'sales_purchase', 'income_expenses', 'payments', 'cash_banks', 'reports'

    // Modals & Sub-forms
    public $showTxModal = false;
    public $showAccountModal = false;
    public $showSalesInvoiceModal = false;

    // Transaction Fields (Daybook / Expense / Income)
    public $txId = null;
    public $txType = 'expense'; // 'income', 'expense', 'purchase'
    public $txAmount = 0;
    public $txCategory = 'Raw Materials';
    public $txDescription = '';
    public $txRemarks = '';
    public $txAccountId = null;
    public $txPartyType = 'supplier'; // 'supplier', 'staff', 'customer'
    public $txPartyName = '';
    public $txPaymentStatus = 'paid'; // 'paid', 'unpaid'
    public $txReferenceNumber = '';
    public $txDate = '';

    // Account Fields (Cash & Banks)
    public $accountId = null;
    public $accountName = '';
    public $accountType = 'bank'; // 'bank', 'cash', 'wallet', 'personal', 'loan'
    public $bankName = '';
    public $accountNumber = '';
    public $accountBalance = 0;
    public $accountDescription = '';

    // Sales Invoice Fields
    public $invoiceCustomerId = null;
    public $invoiceTxnDate = '';
    public $invoiceSalesStaff = '';
    public $invoiceRemarks = '';
    public $invoicePaymentMode = 'cash'; // 'cash', 'card', 'nepal_pay', 'fonepay', 'bank_transfer'
    public $invoicePaymentStatus = 'paid'; // 'paid', 'unpaid'
    public $invoiceItems = []; // Array of ['name' => '', 'qty' => 1, 'rate' => 0, 'amount' => 0]
    public $invoiceTotal = 0;

    protected $rules = [
        'txType' => 'required|in:income,expense,purchase',
        'txAmount' => 'required|numeric|min:0.01',
        'txCategory' => 'required|string',
        'txDescription' => 'nullable|string',
        'txRemarks' => 'nullable|string',
        'txAccountId' => 'required|integer',
        'txPartyType' => 'required|in:supplier,staff,customer',
        'txPartyName' => 'nullable|string',
        'txPaymentStatus' => 'required|in:paid,unpaid',
        'txReferenceNumber' => 'nullable|string',
        'txDate' => 'required|date',
    ];

    public function mount()
    {
        $rId = current_restaurant() ? current_restaurant()->id : 1;

        // Self-healing migration for customers table
        if (!Schema::hasTable('customers')) {
            try {
                Schema::create('customers', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('restaurant_id');
                    $table->string('name');
                    $table->string('phone')->nullable();
                    $table->string('email')->nullable();
                    $table->float('loyalty_discount')->default(0);
                    $table->float('opening_balance')->default(0);
                    $table->string('balance_type')->default('collect');
                    $table->string('dob')->nullable();
                    $table->string('group')->nullable();
                    $table->string('legal_name')->nullable();
                    $table->string('tax_number')->nullable();
                    $table->string('address')->nullable();
                    $table->float('credit_limit')->default(0);
                    $table->integer('credit_term')->default(0);
                    $table->string('favorite_dish')->nullable();
                    $table->string('preferred_seating')->nullable();
                    $table->string('dietary_type')->nullable();
                    $table->string('allergies')->nullable();
                    $table->string('preferred_visiting_time')->nullable();
                    $table->float('due_amount')->default(0);
                    $table->timestamps();
                });
            } catch (\Exception $e) {}
        }

        // Self-healing migration for finance_accounts table
        if (!Schema::hasTable('finance_accounts')) {
            try {
                Schema::create('finance_accounts', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('restaurant_id');
                    $table->string('name');
                    $table->string('type')->default('cash'); // bank, cash, wallet, personal, loan
                    $table->float('balance')->default(0);
                    $table->string('bank_name')->nullable();
                    $table->string('account_number')->nullable();
                    $table->text('description')->nullable();
                    $table->timestamps();
                });
                
                // Seed default accounts
                DB::table('finance_accounts')->insert([
                    ['restaurant_id' => $rId, 'name' => 'Counter (Cash)', 'type' => 'cash', 'balance' => 0, 'created_at' => now(), 'updated_at' => now()],
                    ['restaurant_id' => $rId, 'name' => 'Bank Account', 'type' => 'bank', 'balance' => 0, 'created_at' => now(), 'updated_at' => now()],
                    ['restaurant_id' => $rId, 'name' => 'Owner\'s Account', 'type' => 'personal', 'balance' => 0, 'created_at' => now(), 'updated_at' => now()],
                ]);
            } catch (\Exception $e) {}
        }

        // Self-healing migration for finance_transactions table
        if (!Schema::hasTable('finance_transactions')) {
            try {
                Schema::create('finance_transactions', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('restaurant_id');
                    $table->string('type'); // income, expense, sales, purchase
                    $table->float('amount');
                    $table->string('category')->nullable();
                    $table->string('description')->nullable();
                    $table->text('remarks')->nullable();
                    $table->string('party_type')->nullable(); // supplier, staff, customer
                    $table->string('party_name')->nullable();
                    $table->string('payment_status')->default('paid'); // paid, unpaid
                    $table->string('reference_number')->nullable();
                    $table->text('items')->nullable();
                    $table->string('sales_staff')->nullable();
                    $table->unsignedBigInteger('account_id')->nullable();
                    $table->date('date');
                    $table->timestamps();
                });
            } catch (\Exception $e) {}
        }

        // Dynamic column checks
        try {
            if (!Schema::hasColumn('finance_accounts', 'bank_name')) {
                Schema::table('finance_accounts', function (Blueprint $table) {
                    $table->string('bank_name')->nullable();
                    $table->string('account_number')->nullable();
                    $table->text('description')->nullable();
                });
            }
            if (!Schema::hasColumn('finance_transactions', 'remarks')) {
                Schema::table('finance_transactions', function (Blueprint $table) {
                    $table->text('remarks')->nullable();
                    $table->string('party_type')->nullable();
                    $table->string('party_name')->nullable();
                    $table->string('payment_status')->default('paid');
                    $table->string('reference_number')->nullable();
                    $table->text('items')->nullable();
                    $table->string('sales_staff')->nullable();
                });
            }
        } catch (\Exception $e) {}

        $this->txDate = date('Y-m-d');
        $this->invoiceTxnDate = date('Y-m-d');
        
        // Select first account by default
        $firstAcc = DB::table('finance_accounts')->where('restaurant_id', $rId)->first();
        if ($firstAcc) {
            $this->txAccountId = $firstAcc->id;
        }

        // Setup empty item for sales invoice
        $this->addInvoiceRow();
    }

    public function navigate($view)
    {
        $this->activeView = $view;
    }

    // Modal triggers
    public function openAddTxModal()
    {
        $this->resetTxForm();
        $this->showTxModal = true;
    }

    public function openAddAccountModal()
    {
        $this->resetAccountForm();
        $this->showAccountModal = true;
    }

    public function openSalesInvoiceModal()
    {
        $this->resetInvoiceForm();
        $this->showSalesInvoiceModal = true;
    }

    private function resetTxForm()
    {
        $this->txId = null;
        $this->txType = 'expense';
        $this->txAmount = 0;
        $this->txCategory = 'Raw Materials';
        $this->txDescription = '';
        $this->txRemarks = '';
        $this->txPartyType = 'supplier';
        $this->txPartyName = '';
        $this->txPaymentStatus = 'paid';
        $this->txReferenceNumber = '';
        $this->txDate = date('Y-m-d');
        $rId = current_restaurant() ? current_restaurant()->id : 1;
        $firstAcc = DB::table('finance_accounts')->where('restaurant_id', $rId)->first();
        if ($firstAcc) {
            $this->txAccountId = $firstAcc->id;
        }
    }

    private function resetAccountForm()
    {
        $this->accountId = null;
        $this->accountName = '';
        $this->accountType = 'bank';
        $this->bankName = '';
        $this->accountNumber = '';
        $this->accountBalance = 0;
        $this->accountDescription = '';
    }

    private function resetInvoiceForm()
    {
        $this->invoiceCustomerId = null;
        $this->invoiceTxnDate = date('Y-m-d');
        $this->invoiceSalesStaff = '';
        $this->invoiceRemarks = '';
        $this->invoicePaymentMode = 'cash';
        $this->invoicePaymentStatus = 'paid';
        $this->invoiceItems = [];
        $this->invoiceTotal = 0;
        $this->addInvoiceRow();
    }

    // Sales Invoice Rows Management
    public function addInvoiceRow()
    {
        $this->invoiceItems[] = [
            'name' => '',
            'qty' => 1,
            'rate' => 0,
            'amount' => 0
        ];
        $this->calculateInvoiceTotal();
    }

    public function removeInvoiceRow($index)
    {
        unset($this->invoiceItems[$index]);
        $this->invoiceItems = array_values($this->invoiceItems);
        if (count($this->invoiceItems) === 0) {
            $this->addInvoiceRow();
        }
        $this->calculateInvoiceTotal();
    }

    public function updateInvoiceRow($index, $field, $value)
    {
        $this->invoiceItems[$index][$field] = $value;
        if ($field === 'qty' || $field === 'rate') {
            $qty = floatval($this->invoiceItems[$index]['qty']);
            $rate = floatval($this->invoiceItems[$index]['rate']);
            $this->invoiceItems[$index]['amount'] = $qty * $rate;
        }
        $this->calculateInvoiceTotal();
    }

    public function calculateInvoiceTotal()
    {
        $this->invoiceTotal = collect($this->invoiceItems)->sum('amount');
    }

    // Save Handlers
    public function saveTransaction()
    {
        $this->validate();

        $rId = current_restaurant() ? current_restaurant()->id : 1;

        $data = [
            'restaurant_id' => $rId,
            'type' => $this->txType,
            'amount' => floatval($this->txAmount),
            'category' => $this->txCategory,
            'description' => $this->txDescription ?: null,
            'remarks' => $this->txRemarks ?: null,
            'party_type' => $this->txPartyType,
            'party_name' => $this->txPartyName ?: null,
            'payment_status' => $this->txPaymentStatus,
            'reference_number' => $this->txReferenceNumber ?: null,
            'account_id' => intval($this->txAccountId),
            'date' => $this->txDate,
            'updated_at' => now(),
        ];

        if ($this->txId) {
            $oldTx = DB::table('finance_transactions')->where('id', $this->txId)->first();
            if ($oldTx) {
                $this->adjustAccountBalance($oldTx->account_id, $oldTx->type, -$oldTx->amount);
            }

            DB::table('finance_transactions')->where('id', $this->txId)->update($data);
            $this->adjustAccountBalance($this->txAccountId, $this->txType, $this->txAmount);
        } else {
            $data['created_at'] = now();
            DB::table('finance_transactions')->insert($data);
            $this->adjustAccountBalance($this->txAccountId, $this->txType, $this->txAmount);
        }

        $this->showTxModal = false;
        session()->flash('message', 'Transaction saved successfully!');
    }

    public function saveAccount()
    {
        $this->validate([
            'accountName' => 'required|string|max:255',
            'accountType' => 'required|in:bank,cash,wallet,personal,loan',
            'bankName' => 'nullable|required_if:accountType,bank|string|max:255',
            'accountNumber' => 'nullable|required_if:accountType,bank|string|max:255',
            'accountBalance' => 'required|numeric',
            'accountDescription' => 'nullable|string',
        ]);

        $rId = current_restaurant() ? current_restaurant()->id : 1;

        $data = [
            'restaurant_id' => $rId,
            'name' => $this->accountName,
            'type' => $this->accountType,
            'bank_name' => $this->bankName ?: null,
            'account_number' => $this->accountNumber ?: null,
            'balance' => floatval($this->accountBalance),
            'description' => $this->accountDescription ?: null,
            'updated_at' => now()
        ];

        if ($this->accountId) {
            DB::table('finance_accounts')->where('id', $this->accountId)->update($data);
        } else {
            $data['created_at'] = now();
            DB::table('finance_accounts')->insert($data);
        }

        $this->showAccountModal = false;
        session()->flash('message', 'Finance account saved successfully!');
    }

    public function saveSalesInvoice()
    {
        $this->validate([
            'invoiceTxnDate' => 'required|date',
            'invoiceSalesStaff' => 'nullable|string',
            'invoiceRemarks' => 'nullable|string',
            'invoicePaymentMode' => 'required|string',
            'invoicePaymentStatus' => 'required|in:paid,unpaid',
        ]);

        $rId = current_restaurant() ? current_restaurant()->id : 1;

        // Map payment mode to an account
        $accountMap = [
            'cash' => 'Counter (Cash)',
            'card' => 'Bank Account',
            'nepal_pay' => 'Bank Account',
            'fonepay' => 'Bank Account',
            'bank_transfer' => 'Bank Account',
        ];
        
        $targetAccountName = $accountMap[$this->invoicePaymentMode] ?? 'Counter (Cash)';
        $acc = DB::table('finance_accounts')
            ->where('restaurant_id', $rId)
            ->where('name', 'like', '%' . $targetAccountName . '%')
            ->first();
            
        $accountId = $acc ? $acc->id : DB::table('finance_accounts')->where('restaurant_id', $rId)->value('id');

        $data = [
            'restaurant_id' => $rId,
            'type' => 'sales',
            'amount' => floatval($this->invoiceTotal),
            'category' => 'Direct Sales Cash',
            'description' => $this->invoiceRemarks ?: 'Manual Sales Invoice',
            'remarks' => $this->invoiceRemarks,
            'party_type' => 'customer',
            'party_name' => $this->invoiceCustomerId ? DB::table('customers')->where('id', $this->invoiceCustomerId)->value('name') : 'Walk-in Customer',
            'payment_status' => $this->invoicePaymentStatus,
            'reference_number' => 'INV-' . strtoupper(bin2hex(random_bytes(3))),
            'items' => json_encode($this->invoiceItems),
            'sales_staff' => $this->invoiceSalesStaff,
            'account_id' => $accountId,
            'date' => $this->invoiceTxnDate,
            'created_at' => now(),
            'updated_at' => now()
        ];

        DB::table('finance_transactions')->insert($data);
        
        if ($this->invoicePaymentStatus === 'paid' && $accountId) {
            $this->adjustAccountBalance($accountId, 'sales', $this->invoiceTotal);
        }

        $this->showSalesInvoiceModal = false;
        session()->flash('message', 'Sales invoice created successfully!');
    }

    private function adjustAccountBalance($accountId, $type, $amount)
    {
        $acc = DB::table('finance_accounts')->where('id', $accountId)->first();
        if (!$acc) return;

        // income/sales increases balance, expense/purchase decreases it
        $diff = ($type === 'income' || $type === 'sales') ? $amount : -$amount;
        
        DB::table('finance_accounts')->where('id', $accountId)->update([
            'balance' => $acc->balance + $diff,
            'updated_at' => now()
        ]);
    }

    public function deleteTransaction($id)
    {
        $tx = DB::table('finance_transactions')->where('id', $id)->first();
        if ($tx) {
            if ($tx->account_id && $tx->payment_status === 'paid') {
                $this->adjustAccountBalance($tx->account_id, $tx->type, -$tx->amount);
            }
            DB::table('finance_transactions')->where('id', $id)->delete();
        }
        session()->flash('message', 'Transaction deleted successfully!');
    }

    public function render()
    {
        $rId = current_restaurant() ? current_restaurant()->id : 1;

        $accounts = DB::table('finance_accounts')->where('restaurant_id', $rId)->get();
        $transactions = DB::table('finance_transactions')->where('restaurant_id', $rId)->orderBy('date', 'desc')->orderBy('id', 'desc')->get();
        $customers = DB::table('customers')->where('restaurant_id', $rId)->get();

        // Pull orders sales automatically
        $orderSales = DB::table('orders')
            ->where('restaurant_id', $rId)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.admin.finance-manager', [
            'accounts' => $accounts,
            'transactions' => $transactions,
            'orderSales' => $orderSales,
            'customers' => $customers
        ])->layout('components.layouts.app', ['title' => 'Finance']);
    }
}

EOT;

$finance_blade = <<<'EOT'
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-3">
            @if($activeView !== 'list')
            <button wire:click="navigate('list')" class="p-2.5 bg-[#1c1c1c] hover:bg-[#252525] border border-[#2a2a2a] text-gray-300 rounded-xl transition-all shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </button>
            @endif
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800 dark:text-white tracking-tight">
                    @if($activeView === 'list') Finance
                    @elseif($activeView === 'daybook') Day Book
                    @elseif($activeView === 'transactions') Transactions Ledger
                    @elseif($activeView === 'sales_purchase') Sales & Purchase
                    @elseif($activeView === 'income_expenses') Income & Expenses
                    @elseif($activeView === 'payments') Payments Ledger
                    @elseif($activeView === 'cash_banks') Cash & Banks
                    @elseif($activeView === 'reports') Reports Directory
                    @endif
                </h1>
                <p class="text-sm text-slate-500 mt-1 dark:text-neutral-450">
                    @if($activeView === 'list') Manage daybook, cashflow accounts, manual sales invoices, and Nepal VAT tax registers.
                    @else Active Workspace: {{ str_replace('_', ' ', $activeView) }}.
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($activeView === 'daybook' || $activeView === 'income_expenses')
            <button wire:click="openAddTxModal" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-lg shadow-rose-950/20 active:scale-95 transition-all text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Expense
            </button>
            @elseif($activeView === 'sales_purchase')
            <button wire:click="openSalesInvoiceModal" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-950/20 active:scale-95 transition-all text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create Sales Invoice
            </button>
            @elseif($activeView === 'cash_banks')
            <button wire:click="openAddAccountModal" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-lg shadow-rose-950/20 active:scale-95 transition-all text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create Account
            </button>
            @endif
        </div>
    </div>

    @if(session()->has('message'))
    <div class="p-4 bg-emerald-950/20 text-emerald-450 border border-emerald-900/30 rounded-xl text-sm font-semibold">
        {{ session('message') }}
    </div>
    @endif

    <!-- MAIN RESTROX SIDEBAR DIRECTORY -->
    @if($activeView === 'list')
    <div class="bg-[#0c0c0c] rounded-2xl border border-[#222222] overflow-hidden shadow-2xl max-w-lg text-left">
        <div class="divide-y divide-[#222222]">
            <!-- Dashboard / Main Overview -->
            <div wire:click="navigate('daybook')" class="flex items-center justify-between p-5 hover:bg-[#151515] cursor-pointer transition-colors group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-rose-950/20 border border-rose-900/30 flex items-center justify-center text-rose-500 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 12v-6m-9-9h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-base">Day Book</h3>
                        <p class="text-xs text-neutral-450 mt-0.5">Manage daily balance sheets</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-neutral-500 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>

            <!-- Transactions -->
            <div wire:click="navigate('transactions')" class="flex items-center justify-between p-5 hover:bg-[#151515] cursor-pointer transition-colors group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-purple-950/20 border border-purple-900/30 flex items-center justify-center text-purple-400 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-base">Transactions</h3>
                        <p class="text-xs text-neutral-450 mt-0.5">All financial transaction logs</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-neutral-500 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>

            <!-- Sales & Purchase -->
            <div wire:click="navigate('sales_purchase')" class="flex items-center justify-between p-5 hover:bg-[#151515] cursor-pointer transition-colors group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-950/20 border border-blue-900/30 flex items-center justify-center text-blue-400 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-base">Sales & Purchase</h3>
                        <p class="text-xs text-neutral-450 mt-0.5">Manage sales invoices and supplier purchasing</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-neutral-500 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>

            <!-- Income & Expenses -->
            <div wire:click="navigate('income_expenses')" class="flex items-center justify-between p-5 hover:bg-[#151515] cursor-pointer transition-colors group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-rose-950/20 border border-rose-900/30 flex items-center justify-center text-rose-455 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-base">Income & Expenses</h3>
                        <p class="text-xs text-neutral-450 mt-0.5">Track utilities, wages, and raw materials</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-neutral-500 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>

            <!-- Payments -->
            <div wire:click="navigate('payments')" class="flex items-center justify-between p-5 hover:bg-[#151515] cursor-pointer transition-colors group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-teal-950/20 border border-teal-900/30 flex items-center justify-center text-teal-400 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-base">Payments</h3>
                        <p class="text-xs text-neutral-450 mt-0.5">POS checkout split payment tracking</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-neutral-500 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>

            <!-- Cash & Banks -->
            <div wire:click="navigate('cash_banks')" class="flex items-center justify-between p-5 hover:bg-[#151515] cursor-pointer transition-colors group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-950/20 border border-amber-900/30 flex items-center justify-center text-amber-500 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-base">Cash & Banks</h3>
                        <p class="text-xs text-neutral-450 mt-0.5">Setup Counter, Bank Account, digital wallets</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-neutral-500 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>

            <!-- Reports -->
            <div wire:click="navigate('reports')" class="flex items-center justify-between p-5 hover:bg-[#151515] cursor-pointer transition-colors group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-950/20 border border-emerald-900/30 flex items-center justify-center text-emerald-450 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-base">Reports</h3>
                        <p class="text-xs text-neutral-450 mt-0.5">Trial balance, VAT ledger reports</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-neutral-500 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>
        </div>
    </div>
    @endif

    <!-- DAY BOOK MATRIX VIEW -->
    @if($activeView === 'daybook')
    <div class="space-y-6">
        <!-- Date / Title Panel -->
        <div class="flex justify-between items-center bg-[#111] p-4 rounded-xl border border-[#222]">
            <div class="text-sm font-semibold text-gray-300">
                Day Book Range: <span class="text-rose-500 font-bold ml-1">{{ date('M d, Y') }}</span> to <span class="text-rose-500 font-bold">{{ date('M d, Y') }}</span>
            </div>
            <div class="flex gap-2">
                <button class="px-4 py-2 bg-[#252525] border border-[#3a3a3a] text-xs font-bold text-white rounded-xl hover:bg-[#333] transition-colors">Daybook History</button>
                <button onclick="window.print()" class="px-4 py-2 bg-rose-600 text-xs font-bold text-white rounded-xl hover:bg-rose-700 transition-colors shadow-md shadow-rose-950/20">Print Daybook</button>
            </div>
        </div>

        @php
            $cashId = collect($accounts)->firstWhere('name', 'Counter (Cash)')->id ?? 999;
            $bankId = collect($accounts)->firstWhere('name', 'Bank Account')->id ?? 998;
            $personalId = collect($accounts)->firstWhere('name', 'Owner\'s Account')->id ?? 997;

            $cashSales = collect($transactions)->where('type', 'sales')->where('account_id', $cashId)->sum('amount') + collect($orderSales)->sum('total_amount');
            $bankSales = collect($transactions)->where('type', 'sales')->where('account_id', $bankId)->sum('amount');
            $ownerSales = collect($transactions)->where('type', 'sales')->where('account_id', $personalId)->sum('amount');
            $creditSales = collect($transactions)->where('type', 'sales')->where('payment_status', 'unpaid')->sum('amount');

            $cashIncome = collect($transactions)->where('type', 'income')->where('account_id', $cashId)->sum('amount');
            $bankIncome = collect($transactions)->where('type', 'income')->where('account_id', $bankId)->sum('amount');
            $ownerIncome = collect($transactions)->where('type', 'income')->where('account_id', $personalId)->sum('amount');
            $creditIncome = collect($transactions)->where('type', 'income')->where('payment_status', 'unpaid')->sum('amount');

            $cashPurchase = collect($transactions)->where('type', 'purchase')->where('account_id', $cashId)->sum('amount');
            $bankPurchase = collect($transactions)->where('type', 'purchase')->where('account_id', $bankId)->sum('amount');
            $ownerPurchase = collect($transactions)->where('type', 'purchase')->where('account_id', $personalId)->sum('amount');
            $creditPurchase = collect($transactions)->where('type', 'purchase')->where('payment_status', 'unpaid')->sum('amount');

            $cashExpense = collect($transactions)->where('type', 'expense')->where('account_id', $cashId)->sum('amount');
            $bankExpense = collect($transactions)->where('type', 'expense')->where('account_id', $bankId)->sum('amount');
            $ownerExpense = collect($transactions)->where('type', 'expense')->where('account_id', $personalId)->sum('amount');
            $creditExpense = collect($transactions)->where('type', 'expense')->where('payment_status', 'unpaid')->sum('amount');
        @endphp

        <!-- Matrix Table -->
        <div class="bg-[#0f0f0f] border border-[#222] rounded-2xl overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-[#151515] text-gray-400 font-bold uppercase tracking-wider border-b border-[#222]">
                            <th class="px-6 py-4">PMT Accounts</th>
                            <th class="px-6 py-4">Counter (Cash)</th>
                            <th class="px-6 py-4">Bank Account</th>
                            <th class="px-6 py-4">Owner's Account</th>
                            <th class="px-6 py-4">Total</th>
                            <th class="px-6 py-4">Credit (Due)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#222] text-gray-300">
                        <tr class="bg-[#161616]/30"><td colspan="6" class="px-6 py-2.5 font-black text-rose-500 tracking-wide">RECEIPTS</td></tr>
                        <tr>
                            <td class="px-6 py-3 font-semibold text-white">Net Sales</td>
                            <td class="px-6 py-3">Rs. {{ number_format($cashSales, 0) }}</td>
                            <td class="px-6 py-3">Rs. {{ number_format($bankSales, 0) }}</td>
                            <td class="px-6 py-3">Rs. {{ number_format($ownerSales, 0) }}</td>
                            <td class="px-6 py-3 font-bold text-white">Rs. {{ number_format($cashSales + $bankSales + $ownerSales, 0) }}</td>
                            <td class="px-6 py-3 text-rose-500 font-bold">Rs. {{ number_format($creditSales, 0) }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3 font-semibold text-white">Income</td>
                            <td class="px-6 py-3">Rs. {{ number_format($cashIncome, 0) }}</td>
                            <td class="px-6 py-3">Rs. {{ number_format($bankIncome, 0) }}</td>
                            <td class="px-6 py-3">Rs. {{ number_format($ownerIncome, 0) }}</td>
                            <td class="px-6 py-3 font-bold text-white">Rs. {{ number_format($cashIncome + $bankIncome + $ownerIncome, 0) }}</td>
                            <td class="px-6 py-3 text-rose-500 font-bold">Rs. {{ number_format($creditIncome, 0) }}</td>
                        </tr>
                        @php
                            $totCashRec = $cashSales + $cashIncome;
                            $totBankRec = $bankSales + $bankIncome;
                            $totOwnerRec = $ownerSales + $ownerIncome;
                            $totRec = $totCashRec + $totBankRec + $totOwnerRec;
                            $totCreditRec = $creditSales + $creditIncome;
                        @endphp
                        <tr class="bg-[#151515] font-bold text-white border-t-2 border-[#222]">
                            <td class="px-6 py-3.5">Total Receipts [A]</td>
                            <td class="px-6 py-3.5">Rs. {{ number_format($totCashRec, 0) }}</td>
                            <td class="px-6 py-3.5">Rs. {{ number_format($totBankRec, 0) }}</td>
                            <td class="px-6 py-3.5">Rs. {{ number_format($totOwnerRec, 0) }}</td>
                            <td class="px-6 py-3.5 text-rose-500 font-black">Rs. {{ number_format($totRec, 0) }}</td>
                            <td class="px-6 py-3.5 text-red-500">-</td>
                        </tr>

                        <tr class="bg-[#161616]/30"><td colspan="6" class="px-6 py-2.5 font-black text-rose-500 tracking-wide">PAYMENTS</td></tr>
                        <tr>
                            <td class="px-6 py-3 font-semibold text-white">Purchase</td>
                            <td class="px-6 py-3">Rs. {{ number_format($cashPurchase, 0) }}</td>
                            <td class="px-6 py-3">Rs. {{ number_format($bankPurchase, 0) }}</td>
                            <td class="px-6 py-3">Rs. {{ number_format($ownerPurchase, 0) }}</td>
                            <td class="px-6 py-3 font-bold text-white">Rs. {{ number_format($cashPurchase + $bankPurchase + $ownerPurchase, 0) }}</td>
                            <td class="px-6 py-3 text-rose-500 font-bold">Rs. {{ number_format($creditPurchase, 0) }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3 font-semibold text-white">Expenses</td>
                            <td class="px-6 py-3">Rs. {{ number_format($cashExpense, 0) }}</td>
                            <td class="px-6 py-3">Rs. {{ number_format($bankExpense, 0) }}</td>
                            <td class="px-6 py-3">Rs. {{ number_format($ownerExpense, 0) }}</td>
                            <td class="px-6 py-3 font-bold text-white">Rs. {{ number_format($cashExpense + $bankExpense + $ownerExpense, 0) }}</td>
                            <td class="px-6 py-3 text-rose-500 font-bold">Rs. {{ number_format($creditExpense, 0) }}</td>
                        </tr>
                        @php
                            $totCashPay = $cashPurchase + $cashExpense;
                            $totBankPay = $bankPurchase + $bankExpense;
                            $totOwnerPay = $ownerPurchase + $ownerExpense;
                            $totPay = $totCashPay + $totBankPay + $totOwnerPay;
                            $totCreditPay = $creditPurchase + $creditExpense;
                        @endphp
                        <tr class="bg-[#151515] font-bold text-white border-t-2 border-[#222]">
                            <td class="px-6 py-3.5">Total Payments [B]</td>
                            <td class="px-6 py-3.5">Rs. {{ number_format($totCashPay, 0) }}</td>
                            <td class="px-6 py-3.5">Rs. {{ number_format($totBankPay, 0) }}</td>
                            <td class="px-6 py-3.5">Rs. {{ number_format($totOwnerPay, 0) }}</td>
                            <td class="px-6 py-3.5 text-rose-500 font-black">Rs. {{ number_format($totPay, 0) }}</td>
                            <td class="px-6 py-3.5 text-red-500">-</td>
                        </tr>

                        <!-- Totals C, D, E -->
                        @php
                            $netCash = $totCashRec - $totCashPay;
                            $netBank = $totBankRec - $totBankPay;
                            $netOwner = $totOwnerRec - $totOwnerPay;
                            $netTot = $totRec - $totPay;
                        @endphp
                        <tr class="bg-[#161616] font-black border-t-2 border-[#333]">
                            <td class="px-6 py-4 text-white text-sm">Net Receipt [C = A - B]</td>
                            <td class="px-6 py-4">Rs. {{ number_format($netCash, 0) }}</td>
                            <td class="px-6 py-4">Rs. {{ number_format($netBank, 0) }}</td>
                            <td class="px-6 py-4">Rs. {{ number_format($netOwner, 0) }}</td>
                            <td class="px-6 py-4 text-emerald-500 text-sm">Rs. {{ number_format($netTot, 0) }}</td>
                            <td class="px-6 py-4">-</td>
                        </tr>
                        <tr class="bg-[#111] font-semibold text-gray-400">
                            <td class="px-6 py-3.5">Opening Balance (D)</td>
                            <td class="px-6 py-3.5">Rs. 0</td>
                            <td class="px-6 py-3.5">Rs. 0</td>
                            <td class="px-6 py-3.5">Rs. 0</td>
                            <td class="px-6 py-3.5">Rs. 0</td>
                            <td class="px-6 py-3.5">-</td>
                        </tr>
                        <tr class="bg-[#1c1c1c] font-black border-t-2 border-[#444] text-white">
                            <td class="px-6 py-4 text-sm">Closing Balance [E = C + D]</td>
                            <td class="px-6 py-4">Rs. {{ number_format($netCash, 0) }}</td>
                            <td class="px-6 py-4">Rs. {{ number_format($netBank, 0) }}</td>
                            <td class="px-6 py-4">Rs. {{ number_format($netOwner, 0) }}</td>
                            <td class="px-6 py-4 text-emerald-400 text-sm">Rs. {{ number_format($netTot, 0) }}</td>
                            <td class="px-6 py-4">-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- TRANSACTIONS VIEW -->
    @if($activeView === 'transactions')
    <div class="bg-[#0f0f0f] rounded-2xl border border-[#222] overflow-hidden shadow-2xl">
        <div class="px-6 py-4 bg-[#151515] border-b border-[#222]">
            <h2 class="font-bold text-white">All Financial Ledger Logs</h2>
        </div>
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-[#1a1a1a] text-gray-400 font-bold uppercase border-b border-[#222]">
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Party Name</th>
                        <th class="px-6 py-3">Category</th>
                        <th class="px-6 py-3">Reference</th>
                        <th class="px-6 py-3">Type</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#222] text-gray-300">
                    <!-- Automated Sales -->
                    @foreach($orderSales as $sale)
                    <tr class="hover:bg-[#151515]/50 transition-colors">
                        <td class="px-6 py-4">{{ explode(' ', $sale->created_at)[0] }}</td>
                        <td class="px-6 py-4 font-semibold text-white">POS Customer</td>
                        <td class="px-6 py-4">POS Sales</td>
                        <td class="px-6 py-4 text-gray-450">{{ $sale->order_number }}</td>
                        <td class="px-6 py-4"><span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-950/20 text-emerald-450 rounded-full uppercase">Sales (In)</span></td>
                        <td class="px-6 py-4"><span class="px-2 py-0.5 text-[10px] bg-emerald-950/20 text-emerald-450 rounded-full font-bold">PAID</span></td>
                        <td class="px-6 py-4 text-sm font-bold text-right text-emerald-400">Rs. {{ number_format($sale->total_amount, 0) }}</td>
                    </tr>
                    @endforeach

                    <!-- Manual ledger entries -->
                    @foreach($transactions as $t)
                    <tr class="hover:bg-[#151515]/50 transition-colors">
                        <td class="px-6 py-4">{{ $t->date }}</td>
                        <td class="px-6 py-4 font-semibold text-white">{{ $t->party_name ?: '-' }}</td>
                        <td class="px-6 py-4 font-bold">{{ $t->category }}</td>
                        <td class="px-6 py-4 text-gray-450">{{ $t->reference_number ?: '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full uppercase {{ $t->type === 'income' || $t->type === 'sales' ? 'bg-emerald-950/20 text-emerald-450' : 'bg-rose-950/20 text-rose-500' }}">
                                {{ $t->type }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 text-[10px] rounded-full font-bold uppercase {{ $t->payment_status === 'paid' ? 'bg-emerald-950/20 text-emerald-450' : 'bg-amber-950/20 text-amber-400' }}">
                                {{ $t->payment_status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-right {{ $t->type === 'income' || $t->type === 'sales' ? 'text-emerald-400' : 'text-rose-500' }}">
                            Rs. {{ number_format($t->amount, 0) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- SALES & PURCHASE VIEW -->
    @if($activeView === 'sales_purchase')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sales Invoice Table -->
        <div class="bg-[#0f0f0f] rounded-2xl border border-[#222] overflow-hidden shadow-2xl">
            <div class="px-6 py-4 bg-[#151515] border-b border-[#222] flex justify-between items-center">
                <h2 class="font-bold text-white">Manual Sales Invoices</h2>
                <span class="text-xs font-bold text-emerald-450 bg-emerald-950/30 border border-emerald-900/30 px-2 py-0.5 rounded">Total: Rs. {{ number_format(collect($transactions)->where('type', 'sales')->sum('amount'), 0) }}</span>
            </div>
            <div class="overflow-x-auto max-h-[400px] text-xs">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-[#1a1a1a] text-gray-400 font-bold uppercase border-b border-[#222]">
                            <th class="px-6 py-3">Invoice Ref</th>
                            <th class="px-6 py-3">Customer</th>
                            <th class="px-6 py-3 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#222] text-gray-300">
                        @forelse(collect($transactions)->where('type', 'sales') as $s)
                        <tr class="hover:bg-[#151515]/30">
                            <td class="px-6 py-3.5 font-bold text-white">{{ $s->reference_number }}</td>
                            <td class="px-6 py-3.5 text-gray-450">{{ $s->party_name ?: 'Walk-in' }}</td>
                            <td class="px-6 py-3.5 text-sm font-bold text-right text-emerald-400">Rs. {{ number_format($s->amount, 0) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="p-8 text-center text-gray-500">No manual sales invoice records created yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Inventory Purchases -->
        <div class="bg-[#0f0f0f] rounded-2xl border border-[#222] overflow-hidden shadow-2xl">
            <div class="px-6 py-4 bg-[#151515] border-b border-[#222] flex justify-between items-center">
                <h2 class="font-bold text-white">Purchasing Ledger</h2>
                <span class="text-xs font-bold text-rose-500 bg-rose-950/30 border border-rose-900/30 px-2 py-0.5 rounded">Total: Rs. {{ number_format(collect($transactions)->where('type', 'purchase')->sum('amount'), 0) }}</span>
            </div>
            <div class="overflow-x-auto max-h-[400px] text-xs">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-[#1a1a1a] text-gray-400 font-bold uppercase border-b border-[#222]">
                            <th class="px-6 py-3">Category</th>
                            <th class="px-6 py-3">Supplier Name</th>
                            <th class="px-6 py-3 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#222] text-gray-300">
                        @forelse(collect($transactions)->where('type', 'purchase') as $p)
                        <tr class="hover:bg-[#151515]/30">
                            <td class="px-6 py-3.5 font-bold text-white">{{ $p->category }}</td>
                            <td class="px-6 py-3.5 text-gray-450">{{ $p->party_name ?: '-' }}</td>
                            <td class="px-6 py-3.5 text-sm font-bold text-right text-rose-500">Rs. {{ number_format($p->amount, 0) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="p-8 text-center text-gray-500">No purchases or stock expenses logged yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- CASH & BANKS VIEW -->
    @if($activeView === 'cash_banks')
    <div class="space-y-6 text-left">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($accounts as $acc)
            <div class="bg-[#0f0f0f] p-6 rounded-2xl border border-[#222222] shadow-2xl flex flex-col justify-between relative group">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5 text-white">
                        <div class="w-9 h-9 rounded-xl bg-[#1c1c1c] border border-[#2a2a2a] flex items-center justify-center text-rose-500">
                            @if($acc->type === 'cash')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            @elseif($acc->type === 'bank')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            @endif
                        </div>
                        <div>
                            <h3 class="font-bold text-white text-sm">{{ $acc->name }}</h3>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 bg-[#1c1c1c] text-neutral-450 border border-[#2a2a2a] rounded-md mt-0.5 inline-block">{{ $acc->type }}</span>
                        </div>
                    </div>
                </div>
                <div class="mt-5">
                    <span class="text-[11px] text-gray-400 block mb-0.5">Account Balance</span>
                    <span class="text-2xl font-black text-white">Rs. {{ number_format($acc->balance, 0) }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- REPORTS LIST VIEW -->
    @if($activeView === 'reports')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
        <!-- 1. Accounting -->
        <div class="bg-[#0f0f0f] border border-[#222] rounded-2xl p-6 space-y-4 shadow-2xl">
            <h3 class="font-bold text-rose-500 flex items-center gap-2 text-sm border-b border-[#222] pb-2 uppercase tracking-wide">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 12v-6m-9-9h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Accounting Reports
            </h3>
            <ul class="space-y-2.5 text-xs text-gray-300">
                <li class="hover:text-rose-500 cursor-pointer flex justify-between"><span>Transaction List</span></li>
                <li class="hover:text-rose-500 cursor-pointer flex justify-between"><span>Day Book</span></li>
                <li class="hover:text-rose-500 cursor-pointer flex justify-between"><span>Account Summary</span></li>
                <li class="hover:text-rose-500 cursor-pointer flex justify-between text-rose-500 font-bold"><span>Trial Balance 📌</span></li>
                <li class="hover:text-rose-500 cursor-pointer flex justify-between"><span>Profit Or Loss Statement <span class="bg-emerald-650 text-white text-[9px] px-1.5 py-0.5 rounded font-black">NEW</span></span></li>
                <li class="hover:text-rose-500 cursor-pointer flex justify-between"><span>Balance Sheet</span></li>
            </ul>
        </div>

        <!-- 2. Nepal Tax Report -->
        <div class="bg-[#0f0f0f] border border-[#222] rounded-2xl p-6 space-y-4 shadow-2xl">
            <h3 class="font-bold text-rose-500 flex items-center gap-2 text-sm border-b border-[#222] pb-2 uppercase tracking-wide">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                Nepal VAT Tax Reports
            </h3>
            <ul class="space-y-2.5 text-xs text-gray-300">
                <li class="hover:text-rose-500 cursor-pointer">Sales Register (VAT Annex 13)</li>
                <li class="hover:text-rose-500 cursor-pointer">Sales Return Register</li>
                <li class="hover:text-rose-500 cursor-pointer">Purchase Register</li>
                <li class="hover:text-rose-500 cursor-pointer">Purchase Return Register</li>
                <li class="hover:text-rose-500 cursor-pointer">VAT Summary Report</li>
                <li class="hover:text-rose-500 cursor-pointer text-gray-500">Annex 5 Materialized View</li>
            </ul>
        </div>

        <!-- 3. Sales Report Directory -->
        <div class="bg-[#0f0f0f] border border-[#222] rounded-2xl p-6 space-y-4 shadow-2xl">
            <h3 class="font-bold text-rose-500 flex items-center gap-2 text-sm border-b border-[#222] pb-2 uppercase tracking-wide">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                Sales & Performance
            </h3>
            <ul class="space-y-2.5 text-xs text-gray-300">
                <li class="hover:text-rose-500 cursor-pointer">Sales Master Report</li>
                <li class="hover:text-rose-500 cursor-pointer">Sales Ledger Ledger</li>
                <li class="hover:text-rose-500 cursor-pointer">Customer Monthly Sales</li>
                <li class="hover:text-rose-500 cursor-pointer">Dish Quantity Sales Report</li>
                <li class="hover:text-rose-500 cursor-pointer flex justify-between"><span>Complimentary Items</span> <span class="bg-[#1c1c1c] border border-[#2a2a2a] text-[9px] px-1.5 py-0.5 rounded font-bold text-rose-500">NEW</span></li>
                <li class="hover:text-rose-500 cursor-pointer text-gray-500">Complimentary Add-Ons</li>
            </ul>
        </div>
    </div>
    @endif

    <!-- MODAL: ADD EXPENSE -->
    @if($showTxModal)
    <div class="fixed inset-0 z-50 flex items-center justify-end">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" wire:click="$set('showTxModal', false)"></div>
        <!-- Modal Card -->
        <div class="bg-[#0f0f0f] border-l border-[#222222] h-full w-full max-w-lg shadow-2xl flex flex-col p-6 space-y-5 overflow-y-auto relative z-10" style="background-color: #0f0f0f !important; opacity: 1 !important;">
            <div class="flex justify-between items-center border-b border-[#222] pb-4">
                <h3 class="font-bold text-lg text-white">Add Expense</h3>
                <button wire:click="$set('showTxModal', false)" class="text-gray-400 hover:text-white p-2.5 rounded-lg bg-[#1c1c1c] border border-[#2a2a2a] transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="space-y-4 text-left text-xs">
                <!-- Amount -->
                <div>
                    <label class="block font-bold text-gray-300 mb-1.5">Amount *</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-gray-400 font-semibold">Rs.</span>
                        <input wire:model="txAmount" type="number" class="w-full pl-11 pr-4 py-3 bg-[#151515] border border-[#252525] text-white rounded-xl focus:ring-2 focus:ring-rose-500/25 focus:border-rose-500 outline-none font-bold text-sm" />
                    </div>
                    @error('txAmount') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Remarks -->
                <div>
                    <label class="block font-bold text-gray-300 mb-1.5">Remarks</label>
                    <input wire:model="txRemarks" type="text" placeholder="Enter Remarks" class="w-full px-4 py-3 bg-[#151515] border border-[#252525] text-white rounded-xl focus:ring-2 focus:ring-rose-500/25 focus:border-rose-500 outline-none font-medium" />
                </div>

                <!-- Account Head -->
                <div>
                    <label class="block font-bold text-gray-300 mb-1.5">Account Head *</label>
                    <select wire:model="txCategory" class="w-full px-4 py-3 bg-[#151515] border border-[#252525] text-white rounded-xl focus:ring-2 focus:ring-rose-500/25 focus:border-rose-500 outline-none font-medium">
                        <option value="Raw Materials">Raw Materials</option>
                        <option value="Salaries & Wages">Salaries & Wages</option>
                        <option value="Rent & Utilities">Rent & Utilities</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Office Expenses">Office Expenses</option>
                    </select>
                </div>

                <!-- Party Type Selector -->
                <div>
                    <label class="block font-bold text-gray-300 mb-1.5">Party Type</label>
                    <div class="grid grid-cols-3 gap-2.5">
                        <button wire:click="$set('txPartyType', 'supplier')" class="py-2.5 rounded-xl border text-xs font-bold flex items-center justify-center transition-all duration-200 active:scale-95 {{ $txPartyType === 'supplier' ? 'bg-rose-600 border-rose-500 text-white shadow-lg shadow-rose-950/20' : 'bg-[#252525] border-[#3a3a3a] text-gray-400 hover:text-white hover:bg-[#333]' }}">
                            <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            Supplier
                        </button>
                        <button wire:click="$set('txPartyType', 'staff')" class="py-2.5 rounded-xl border text-xs font-bold flex items-center justify-center transition-all duration-200 active:scale-95 {{ $txPartyType === 'staff' ? 'bg-rose-600 border-rose-500 text-white shadow-lg shadow-rose-950/20' : 'bg-[#252525] border-[#3a3a3a] text-gray-400 hover:text-white hover:bg-[#333]' }}">
                            <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            Staff
                        </button>
                        <button wire:click="$set('txPartyType', 'customer')" class="py-2.5 rounded-xl border text-xs font-bold flex items-center justify-center transition-all duration-200 active:scale-95 {{ $txPartyType === 'customer' ? 'bg-rose-600 border-rose-500 text-white shadow-lg shadow-rose-950/20' : 'bg-[#252525] border-[#3a3a3a] text-gray-400 hover:text-white hover:bg-[#333]' }}">
                            <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Customer
                        </button>
                    </div>
                </div>

                <!-- Party Name -->
                <div>
                    <label class="block font-bold text-gray-300 mb-1.5">Party Name</label>
                    <input wire:model="txPartyName" type="text" placeholder="Assign party to this expense" class="w-full px-4 py-3 bg-[#151515] border border-[#252525] text-white rounded-xl focus:ring-2 focus:ring-rose-500/25 focus:border-rose-500 outline-none font-medium" />
                </div>

                <!-- Payment Status Tabs -->
                <div>
                    <label class="block font-bold text-gray-300 mb-1.5">Payment Status</label>
                    <div class="grid grid-cols-2 gap-2.5">
                        <button wire:click="$set('txPaymentStatus', 'paid')" class="py-2.5 rounded-xl border text-xs font-bold flex items-center justify-center transition-all duration-200 active:scale-95 {{ $txPaymentStatus === 'paid' ? 'bg-rose-600 border-rose-500 text-white shadow-lg shadow-rose-950/20' : 'bg-[#252525] border-[#3a3a3a] text-gray-400 hover:text-white hover:bg-[#333]' }}">
                            <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Paid
                        </button>
                        <button wire:click="$set('txPaymentStatus', 'unpaid')" class="py-2.5 rounded-xl border text-xs font-bold flex items-center justify-center transition-all duration-200 active:scale-95 {{ $txPaymentStatus === 'unpaid' ? 'bg-rose-600 border-rose-500 text-white shadow-lg shadow-rose-950/20' : 'bg-[#252525] border-[#3a3a3a] text-gray-400 hover:text-white hover:bg-[#333]' }}">
                            <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Unpaid / Credit
                        </button>
                    </div>
                </div>

                @if($txPaymentStatus === 'paid')
                <!-- Payment Account -->
                <div>
                    <label class="block font-bold text-gray-300 mb-1.5">Payment Account *</label>
                    <select wire:model="txAccountId" class="w-full px-4 py-3 bg-[#151515] border border-[#252525] text-white rounded-xl focus:ring-2 focus:ring-rose-500/25 focus:border-rose-500 outline-none font-medium">
                        @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Ref Number & Date -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-gray-300 mb-1.5">Reference Number</label>
                        <input wire:model="txReferenceNumber" type="text" placeholder="e.g. Ref-1092" class="w-full px-4 py-3 bg-[#151515] border border-[#252525] text-white rounded-xl focus:ring-2 focus:ring-rose-500/25 focus:border-rose-500 outline-none font-medium" />
                    </div>
                    <div>
                        <label class="block font-bold text-gray-300 mb-1.5">Transaction Date *</label>
                        <input wire:model="txDate" type="date" class="w-full px-4 py-3 bg-[#151515] border border-[#252525] text-white rounded-xl focus:ring-2 focus:ring-rose-500/25 focus:border-rose-500 outline-none font-medium" />
                    </div>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="pt-4 border-t border-[#222] flex justify-end gap-3 bg-[#0f0f0f]">
                <button wire:click="$set('showTxModal', false)" class="px-5 py-2.5 bg-[#252525] border border-[#3a3a3a] hover:bg-[#333] text-white font-bold rounded-xl text-xs transition-colors">Reset</button>
                <button wire:click="saveTransaction" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition-colors shadow-md shadow-rose-950/20">Save Expense</button>
            </div>
        </div>
    </div>
    @endif

    <!-- MODAL: SALES INVOICE -->
    @if($showSalesInvoiceModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" wire:click="$set('showSalesInvoiceModal', false)"></div>
        <!-- Modal Card -->
        <div class="bg-[#0f0f0f] border border-[#222222] rounded-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden shadow-2xl flex flex-col relative z-10" style="background-color: #0f0f0f !important; opacity: 1 !important;">
            <div class="px-6 py-4 border-b border-[#222222] flex justify-between items-center bg-[#151515]">
                <h3 class="font-bold text-base text-white">Create Manual Sales Invoice</h3>
                <button wire:click="$set('showSalesInvoiceModal', false)" class="text-gray-400 hover:text-white p-2 bg-[#1c1c1c] border border-[#2a2a2a] rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-6 text-left text-xs">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block font-bold text-gray-300 mb-1.5">Customer</label>
                        <select wire:model="invoiceCustomerId" class="w-full px-4 py-3 bg-[#151515] border border-[#252525] text-white rounded-xl outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="">Select Customer (Walk-in)</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-gray-300 mb-1.5">TXN Date *</label>
                        <input wire:model="invoiceTxnDate" type="date" class="w-full px-4 py-3 bg-[#151515] border border-[#252525] text-white rounded-xl outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block font-bold text-gray-300 mb-1.5">Sales Staff</label>
                        <input wire:model="invoiceSalesStaff" type="text" placeholder="Enter Sales Staff name" class="w-full px-4 py-3 bg-[#151515] border border-[#252525] text-white rounded-xl outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                </div>

                <!-- Items List table -->
                <div class="space-y-3">
                    <h4 class="font-bold text-gray-400 uppercase tracking-wider">Items List</h4>
                    <div class="border border-[#222] rounded-xl overflow-hidden shadow-inner bg-[#121212]">
                        <table class="w-full text-left text-gray-300">
                            <thead>
                                <tr class="bg-[#151515] border-b border-[#222] text-[10px] uppercase font-bold text-gray-400">
                                    <th class="px-4 py-3 w-12">SN</th>
                                    <th class="px-4 py-3">Item Name *</th>
                                    <th class="px-4 py-3 w-28">Quantity</th>
                                    <th class="px-4 py-3 w-32">Rate (Rs.) *</th>
                                    <th class="px-4 py-3 w-36 text-right">Amount</th>
                                    <th class="px-4 py-3 w-16 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoiceItems as $index => $item)
                                <tr class="border-b border-[#1c1c1c]">
                                    <td class="px-4 py-3 font-semibold">{{ $index + 1 }}</td>
                                    <td class="px-4 py-2">
                                        <input type="text" placeholder="Enter Item Name" value="{{ $item['name'] }}" wire:input="updateInvoiceRow({{ $index }}, 'name', $event.target.value)" class="w-full px-3 py-2 bg-[#151515] border border-[#252525] text-white rounded-lg focus:ring-1 focus:ring-emerald-500 outline-none" />
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" value="{{ $item['qty'] }}" wire:input="updateInvoiceRow({{ $index }}, 'qty', $event.target.value)" class="w-full px-3 py-2 bg-[#151515] border border-[#252525] text-white rounded-lg focus:ring-1 focus:ring-emerald-500 outline-none" />
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" step="0.01" value="{{ $item['rate'] }}" wire:input="updateInvoiceRow({{ $index }}, 'rate', $event.target.value)" class="w-full px-3 py-2 bg-[#151515] border border-[#252525] text-white rounded-lg focus:ring-1 focus:ring-emerald-500 outline-none" />
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-white">
                                        Rs. {{ number_format($item['amount'], 2) }}
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        <button wire:click="removeInvoiceRow({{ $index }})" class="p-1.5 text-gray-500 hover:text-red-500 hover:bg-red-500/10 rounded-lg">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="addInvoiceRow" class="px-4 py-2 bg-[#1c1c1c] border border-[#2a2a2a] hover:bg-[#252525] text-white font-bold rounded-lg transition-colors">+ Add Row</button>
                    </div>
                </div>

                <!-- Remarks & Payout Mode -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-[#222]">
                    <div class="space-y-4">
                        <div>
                            <label class="block font-bold text-gray-300 mb-1.5">Remarks</label>
                            <textarea wire:model="invoiceRemarks" rows="2" placeholder="Enter Remarks" class="w-full px-4 py-3 bg-[#151515] border border-[#252525] text-white rounded-xl outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
                        </div>
                        <div>
                            <label class="block font-bold text-gray-300 mb-1.5">Payment Mode</label>
                            <div class="flex gap-2.5 flex-wrap">
                                @foreach(['cash' => ['Cash', 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'], 'card' => ['Card', 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'], 'nepal_pay' => ['Nepal Pay', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'], 'fonepay' => ['Fonepay', 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z'], 'bank_transfer' => ['Bank Transfer', 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4']] as $key => $data)
                                <button wire:click="$set('invoicePaymentMode', '{{ $key }}')" class="px-4 py-2.5 rounded-xl border text-xs font-bold flex items-center justify-center transition-all duration-200 active:scale-95 {{ $invoicePaymentMode === $key ? 'bg-emerald-650 border-emerald-650 text-white shadow-lg shadow-emerald-950/20' : 'bg-[#252525] border-[#3a3a3a] text-gray-400 hover:text-white hover:bg-[#333]' }}">
                                    <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $data[1] }}"></path></svg>
                                    {{ $data[0] }}
                                </button>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label class="block font-bold text-gray-300 mb-1.5">Payment Status</label>
                            <div class="grid grid-cols-2 gap-2.5 w-60">
                                <button wire:click="$set('invoicePaymentStatus', 'paid')" class="py-2.5 rounded-xl border text-xs font-bold flex items-center justify-center transition-all duration-200 active:scale-95 {{ $invoicePaymentStatus === 'paid' ? 'bg-emerald-650 border-emerald-650 text-white shadow-lg shadow-emerald-950/20' : 'bg-[#252525] border-[#3a3a3a] text-gray-400 hover:text-white hover:bg-[#333]' }}">
                                    <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Paid
                                </button>
                                <button wire:click="$set('invoicePaymentStatus', 'unpaid')" class="py-2.5 rounded-xl border text-xs font-bold flex items-center justify-center transition-all duration-200 active:scale-95 {{ $invoicePaymentStatus === 'unpaid' ? 'bg-emerald-650 border-emerald-650 text-white shadow-lg shadow-emerald-950/20' : 'bg-[#252525] border-[#3a3a3a] text-gray-400 hover:text-white hover:bg-[#333]' }}">
                                    <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Credit
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Totals -->
                    <div class="bg-[#151515] p-6 rounded-2xl border border-[#252525] flex flex-col justify-between space-y-4 h-fit shadow-xl">
                        <div class="space-y-2.5 font-semibold text-gray-400">
                            <div class="flex justify-between"><span>Taxable Amount</span><span class="text-white">Rs. {{ number_format($invoiceTotal, 2) }}</span></div>
                            <div class="flex justify-between"><span>Total Amount</span><span class="text-white">Rs. {{ number_format($invoiceTotal, 2) }}</span></div>
                            <div class="flex justify-between border-t border-[#222] pt-2 font-black text-white text-base"><span>Net Amount</span><span class="text-emerald-450">Rs. {{ number_format($invoiceTotal, 2) }}</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-[#222] flex justify-end gap-3 bg-[#151515]">
                <button wire:click="resetInvoiceForm" class="px-5 py-2.5 bg-[#252525] border border-[#3a3a3a] hover:bg-[#333] text-white font-bold rounded-xl text-xs transition-colors">Reset</button>
                <button wire:click="saveSalesInvoice" class="px-6 py-2.5 bg-emerald-650 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-colors shadow-md shadow-emerald-950/20">Save Sales Invoice</button>
            </div>
        </div>
    </div>
    @endif

    <!-- MODAL: CREATE ACCOUNT -->
    @if($showAccountModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" wire:click="$set('showAccountModal', false)"></div>
        <!-- Modal Card -->
        <div class="bg-[#0f0f0f] border border-[#222222] rounded-2xl w-full max-w-lg shadow-2xl flex flex-col overflow-hidden relative z-10" style="background-color: #0f0f0f !important; opacity: 1 !important;">
            <div class="px-6 py-4 border-b border-[#222222] flex justify-between items-center bg-[#151515]">
                <h3 class="font-bold text-base text-white">Create Accounts</h3>
                <button wire:click="$set('showAccountModal', false)" class="text-gray-400 hover:text-white p-2 bg-[#1c1c1c] border border-[#2a2a2a] rounded-lg transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="p-6 space-y-4 text-left text-xs">
                <div>
                    <label class="block font-bold text-gray-300 mb-1.5">Account Name *</label>
                    <input wire:model="accountName" type="text" placeholder="Enter Account Name" class="w-full px-4 py-3 bg-[#151515] border border-[#252525] text-white rounded-xl focus:ring-2 focus:ring-rose-500/25 focus:border-rose-500 outline-none font-medium" />
                    @error('accountName') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-bold text-gray-300 mb-1.5">Type</label>
                    <div class="flex gap-2 flex-wrap">
                        @foreach(['bank' => ['Bank', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'], 'cash' => ['Cash', 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'], 'wallet' => ['Digital Wallet', 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z'], 'personal' => ['Personal Account', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'], 'loan' => ['Loan Account', 'M9 8h6m-6 4h6m-6 4h6m1 5H6a2 2 0 01-2-2V4a2 2 0 012-2h12a2 2 0 012 2v15a2 2 0 01-2 2z']] as $key => $data)
                        <button wire:click="$set('accountType', '{{ $key }}')" class="px-4 py-2.5 rounded-xl border text-xs font-bold flex items-center justify-center transition-all duration-200 active:scale-95 {{ $accountType === $key ? 'bg-rose-600 border-rose-500 text-white shadow-lg shadow-rose-950/20' : 'bg-[#252525] border-[#3a3a3a] text-gray-400 hover:text-white hover:bg-[#333]' }}">
                            <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $data[1] }}"></path></svg>
                            {{ $data[0] }}
                        </button>
                        @endforeach
                    </div>
                </div>

                @if($accountType === 'bank')
                <div>
                    <label class="block font-bold text-gray-300 mb-1.5">Bank Account Name *</label>
                    <input wire:model="bankName" type="text" placeholder="Enter Bank Account Name" class="w-full px-4 py-3 bg-[#151515] border border-[#252525] text-white rounded-xl focus:ring-2 focus:ring-rose-500/25 focus:border-rose-500 outline-none font-medium" />
                    @error('bankName') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block font-bold text-gray-300 mb-1.5">Bank Account Number *</label>
                    <input wire:model="accountNumber" type="text" placeholder="Enter Bank Account Number" class="w-full px-4 py-3 bg-[#151515] border border-[#252525] text-white rounded-xl focus:ring-2 focus:ring-rose-500/25 focus:border-rose-500 outline-none font-medium" />
                    @error('accountNumber') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                </div>
                @endif

                <div>
                    <label class="block font-bold text-gray-300 mb-1.5">Opening Balance</label>
                    <input wire:model="accountBalance" type="number" placeholder="Rs. 0" class="w-full px-4 py-3 bg-[#151515] border border-[#252525] text-white rounded-xl focus:ring-2 focus:ring-rose-500/25 focus:border-rose-500 outline-none font-medium" />
                    @error('accountBalance') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-bold text-gray-300 mb-1.5">Description</label>
                    <textarea wire:model="accountDescription" rows="2" placeholder="Enter Description" class="w-full px-4 py-3 bg-[#151515] border border-[#252525] text-white rounded-xl focus:ring-2 focus:ring-rose-500/25 focus:border-rose-500 outline-none font-medium"></textarea>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-[#222222] flex justify-end gap-3 bg-[#151515]">
                <button wire:click="$set('showAccountModal', false)" class="px-5 py-2.5 bg-[#252525] border border-[#3a3a3a] hover:bg-[#333] text-white font-bold rounded-xl text-xs transition-colors">Reset</button>
                <button wire:click="saveAccount" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition-colors shadow-md shadow-rose-950/20">Save Accounts</button>
            </div>
        </div>
    </div>
    @endif
</div>

EOT;

$print_qr = <<<'EOT'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print QR - {{ $table->name }}</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f1f5f9;
        }

        .qr-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            padding: 40px;
            width: 400px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
        }

        .restaurant-name {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 8px 0;
            letter-spacing: -0.025em;
        }

        .table-name {
            font-size: 40px;
            font-weight: 900;
            color: #e11d48;
            margin: 0 0 24px 0;
            letter-spacing: -0.05em;
        }

        .qr-container {
            width: 280px;
            height: 280px;
            padding: 16px;
            border-radius: 20px;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-image {
            width: 100%;
            height: 100%;
            object-contain: fit;
        }

        .instruction-title {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 4px 0;
        }

        .instruction-desc {
            font-size: 13px;
            color: #64748b;
            margin: 0;
            line-height: 1.5;
        }

        /* Action Buttons (Hidden on Print) */
        .actions-panel {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 12px;
            z-index: 100;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.2s, opacity 0.2s;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-print {
            background-color: #0f172a;
            color: #ffffff;
        }

        .btn-close {
            background-color: #ffffff;
            color: #0f172a;
            border: 1px solid #e2e8f0;
        }

        @media print {
            body {
                background-color: #ffffff;
                min-height: 0;
            }
            .qr-card {
                box-shadow: none;
                border: none;
                padding: 0;
                width: 100%;
                max-width: 100%;
            }
            .actions-panel {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print();">

    <div class="qr-card">
        <h2 class="restaurant-name">{{ $restaurant->name ?? 'DRestro Restaurant' }}</h2>
        <h1 class="table-name">{{ $table->name }}</h1>
        
        <div class="qr-container">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=350x350&data={{ urlencode(get_tenant_menu_url($table->id)) }}" alt="QR Code" class="qr-image" />
        </div>

        <h3 class="instruction-title">Scan & Order</h3>
        <p class="instruction-desc">Scan this QR code using your smartphone camera to view the digital menu and place your order instantly.</p>
    </div>

    <div class="actions-panel">
        <button class="btn btn-close" onclick="window.close();">Close Window</button>
        <button class="btn btn-print" onclick="window.print();">Print QR Code</button>
    </div>

</body>
</html>

EOT;

$table_manager = <<<'EOT'
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="hidden lg:block">
            <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Table Management & QR Codes</h2>
            <p class="text-slate-500 text-sm mt-1 dark:text-slate-400">Add tables and generate unique QR codes for dine-in ordering.</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="toggleAddModal" class="px-4 py-2 bg-emerald-600 text-white rounded-lg font-medium hover:bg-emerald-700 transition-colors flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add New Table
            </button>
        </div>
    </div>

    <!-- Add Table Modal -->
    @if($showAddModal)
    <div class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl border border-slate-100 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-slate-800 dark:text-slate-200">Add New Table</h3>
                <button wire:click="toggleAddModal" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form wire:submit.prevent="saveTable" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1 dark:text-slate-300">Table Name/Number</label>
                    <input wire:model="newTableName" type="text" class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors dark:border-slate-700" placeholder="e.g. Table 1, Window Seat" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1 dark:text-slate-300">Seating Capacity</label>
                    <input wire:model="newTableCapacity" type="number" min="1" class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors dark:border-slate-700" required>
                </div>
                
                <div class="pt-4 flex gap-3">
                    <button type="button" wire:click="toggleAddModal" class="flex-1 px-4 py-2 bg-slate-100 text-slate-700 rounded-lg font-medium hover:bg-slate-200 transition-colors dark:text-slate-300 dark:bg-slate-800">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-emerald-600 text-white rounded-lg font-medium hover:bg-emerald-700 transition-colors shadow-sm">Save Table</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Tables Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        
        @foreach($tables as $table)
        <!-- Table Card -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col items-center text-center relative group dark:border-slate-800 dark:bg-slate-900">
            
            <button wire:click="deleteTable({{ $table->id }})" wire:confirm="Are you sure you want to delete this table?" class="absolute top-3 right-3 text-red-500 hover:text-red-700 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 rounded-full p-1.5 border border-slate-200/50 dark:border-slate-700/50 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>

            <h3 class="text-xl font-bold text-slate-800 dark:text-slate-200">{{ $table->name }}</h3>
            <p class="text-sm text-slate-500 mb-4 dark:text-slate-400">Capacity: {{ $table->capacity }} Persons</p>
            
            <!-- Dynamic QR Code -->
            <div class="w-32 h-32 bg-white rounded-lg p-2.5 flex items-center justify-center border border-slate-200 mb-4 shadow-sm">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(get_tenant_menu_url($table->id)) }}" alt="QR Code" class="w-full h-full object-contain" />
            </div>
            
            <div class="flex gap-2 w-full">
                <a href="{{ route('admin.print-qr', $table->id) }}" target="_blank" class="flex-1 px-3 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors flex justify-center items-center dark:text-slate-300 dark:bg-slate-800">
                    Print QR
                </a>
            </div>
        </div>
        @endforeach

        <!-- Add Table Card -->
        <div wire:click="toggleAddModal" class="bg-slate-50 p-6 rounded-2xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center text-center cursor-pointer hover:bg-slate-100 transition-colors group min-h-[300px] dark:bg-slate-900 dark:border-slate-700">
            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-4 shadow-sm group-hover:scale-105 transition-transform dark:bg-slate-900">
                <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </div>
            <h3 class="text-lg font-medium text-slate-700 dark:text-slate-300">Add Table</h3>
            <p class="text-sm text-slate-500 mt-1 dark:text-slate-400">Generate a new QR code</p>
        </div>

    </div>
</div>

EOT;

// Ensure directories exist
if (!is_dir(dirname($routes_file))) { mkdir(dirname($routes_file), 0755, true); }
if (!is_dir(dirname($middleware_file))) { mkdir(dirname($middleware_file), 0755, true); }
if (!is_dir(dirname($app_layout_file))) { mkdir(dirname($app_layout_file), 0755, true); }
if (!is_dir(dirname($helpers_file))) { mkdir(dirname($helpers_file), 0755, true); }
if (!is_dir(dirname($customer_php_file))) { mkdir(dirname($customer_php_file), 0755, true); }
if (!is_dir(dirname($customer_blade_file))) { mkdir(dirname($customer_blade_file), 0755, true); }
if (!is_dir(dirname($finance_php_file))) { mkdir(dirname($finance_php_file), 0755, true); }
if (!is_dir(dirname($finance_blade_file))) { mkdir(dirname($finance_blade_file), 0755, true); }
if (!is_dir(dirname($print_qr_file))) { mkdir(dirname($print_qr_file), 0755, true); }
if (!is_dir(dirname($table_manager_file))) { mkdir(dirname($table_manager_file), 0755, true); }

// Write files
file_put_contents($routes_file, $routes_content);
file_put_contents($middleware_file, $middleware_content);
file_put_contents($app_layout_file, $app_layout_content);
file_put_contents($helpers_file, $helpers_content);
file_put_contents($customer_php_file, $customer_php);
file_put_contents($customer_blade_file, $customer_blade);
file_put_contents($finance_php_file, $finance_php);
file_put_contents($finance_blade_file, $finance_blade);
file_put_contents($print_qr_file, $print_qr);
file_put_contents($table_manager_file, $table_manager);

// Reset Cache
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

echo "<h1>DRestro Portal Updates Deployed Successfully!</h1>";
echo "<ul>";
echo "<li>routes/web.php</li>";
echo "<li>TenantMiddleware.php</li>";
echo "<li>app.blade.php</li>";
echo "<li>helpers.php</li>";
echo "<li>CustomerManager (PHP & Blade)</li>";
echo "<li>FinanceManager (PHP & Blade)</li>";
echo "<li>print/qr.blade.php</li>";
echo "<li>table-manager.blade.php</li>";
echo "</ul>";
echo "<p>All Laravel caches and OPCache cleared successfully.</p>";
