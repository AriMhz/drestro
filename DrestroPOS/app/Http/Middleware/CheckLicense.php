<?php
 
namespace App\Http\Middleware;
 
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Restaurant;
use Carbon\Carbon;
 
class CheckLicense
{
    private $featureRoutes = [
        'staff/take-order'    => 'take_order',
        'staff/room-service'  => 'take_room_service',
        'staff/cashier'       => 'cashier_panel',
        'staff/kitchen'       => 'kitchen_display',
        'staff/waiter'        => 'waiter_dashboard',
        'staff/bar'           => 'bar_display',
        'admin/inventory'     => 'inventory',
        'admin/reports'       => 'reports',
        'admin/menus'         => 'menu_manager',
        'admin/tables'        => 'restaurant_tables',
        'admin/rooms'         => 'hotel_room_manager',
        'admin/staff'         => 'staff_management',
        'staff/hotel-reception' => 'hotel_reception',
        'menu'                => 'qr_menu',
    ];
 
    public function handle(Request $request, Closure $next): Response
    {
        $restaurant = current_restaurant();
        
        // Fast Check: Trust the database license_data, otherwise get trial limits
        $licenseData = ($restaurant && $restaurant->license_data) 
            ? $restaurant->license_data 
            : \App\Services\LicenseManager::getFreeLimits();
 
        // Share with views
        view()->share('activeLicense', $licenseData);

        // Check if the license is expired (Only expire if expires_at is set and in the past)
        $isExpired = false;
        if (!empty($licenseData['expires_at'])) {
            $isExpired = \Carbon\Carbon::parse($licenseData['expires_at'])->isPast();
        } else {
            // Null or empty expires_at means Lifetime Active Free Plan!
            $isExpired = false;
        }

        // Share with views
        view()->share('activeLicense', $licenseData);
        view()->share('isLocked', $isExpired);

        // Always allow access to these critical routes
        $allowedRoutes = ['admin.license', 'license.activate', 'admin.logout', 'logout', 'login', 'clean-database'];
        
        $currentRouteName = $request->route() ? $request->route()->getName() : null;

        // If expired and not on an allowed route, block them
        if ($isExpired && !in_array($currentRouteName, $allowedRoutes)) {
            // Only redirect if they are trying to access admin or staff panels
            if ($request->is('admin/*') || $request->is('staff/*')) {
                return redirect()->route('admin.license')->with('error', 'Your 14-day trial period has expired. All POS features have been disabled. Please renew your subscription to continue.');
            }
        }

        return $next($request);
    }
}
