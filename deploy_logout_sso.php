<?php
$controller_file = __DIR__ . '/../app/Http/Controllers/AuthController.php';
$routes_file = __DIR__ . '/../routes/web.php';
$middleware_file = __DIR__ . '/../app/Http/Middleware/TenantMiddleware.php';

$controller_content = <<<'EOT'
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

if (!is_dir(dirname($controller_file))) { mkdir(dirname($controller_file), 0755, true); }
if (!is_dir(dirname($routes_file))) { mkdir(dirname($routes_file), 0755, true); }
if (!is_dir(dirname($middleware_file))) { mkdir(dirname($middleware_file), 0755, true); }

file_put_contents($controller_file, $controller_content);
file_put_contents($routes_file, $routes_content);
file_put_contents($middleware_file, $middleware_content);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

if (function_exists('opcache_reset')) { opcache_reset(); }
Illuminate\Support\Facades\Artisan::call('config:clear');
Illuminate\Support\Facades\Artisan::call('route:clear');

echo "<h1>Deployment Successful!</h1>";
echo "<p>AuthController.php, routes/web.php, and TenantMiddleware.php have been updated.</p>";
echo "<p>Config &amp; Route Cache Cleared.</p>";
echo "<p>OPCache Cleared.</p>";
echo "<p>Logout now redirects to <code>https://drestro.com/signout</code></p>";
