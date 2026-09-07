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
        if ($request->is('storage/*') || $request->is('sso/login') || $request->is('logout') || $request->is('login') || $request->is('invite/*') || $request->is('api/restaurant/admin-action')) {
            Log::info('TenantMiddleware: skipping for storage/sso/logout/login/invite/admin-action');
            return $next($request);
        }

        $host = $request->getHost();
        $parts = explode('.', $host);
        $slug = $parts[0];

        Log::info('TenantMiddleware: host parsing', ['host' => $host, 'slug' => $slug, 'is_local' => app()->environment('local')]);

        // Exception for the root domain itself (if they visit portal.drestro.com directly)
        if ($host === 'portal.drestro.com' || $slug === 'localhost' || $slug === '127') {
            $restaurant = null;

            // 1. Try resolving by table query parameter (for scanned QR menu URLs)
            $tableId = $request->query('table');
            if ($tableId) {
                $table = \App\Models\Table::find($tableId);
                if ($table) {
                    $restaurant = Restaurant::find($table->restaurant_id);
                    Log::info('TenantMiddleware: resolved by table query param', ['table_id' => $tableId, 'restaurant_id' => $restaurant->id ?? null]);
                }
            }

            // 2. Try resolving by slug query parameter (for general digital menu URLs)
            if (!$restaurant) {
                $qSlug = $request->query('slug') ?: $request->query('restaurant');
                if ($qSlug) {
                    $restaurant = Restaurant::where('slug', $qSlug)->first();
                    Log::info('TenantMiddleware: resolved by slug query param', ['slug' => $qSlug, 'restaurant_id' => $restaurant->id ?? null]);
                }
            }

            // 3. Try resolving by session (for logged in staff/admin)
            if (!$restaurant) {
                $tenantSlug = session('tenant_slug');
                if ($tenantSlug) {
                    $restaurant = Restaurant::where('slug', $tenantSlug)->first();
                    Log::info('TenantMiddleware: resolved by session slug', ['tenant_slug' => $tenantSlug, 'restaurant_id' => $restaurant->id ?? null]);
                }
            }

            // 4. Fallback to current_restaurant()
            if (!$restaurant) {
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
