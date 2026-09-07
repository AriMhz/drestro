<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SSOController extends Controller
{
    public function login(Request $request)
    {
        $email = $request->query('email');
        $token = $request->query('token');
        $name = $request->query('name', 'Restaurant Owner');
        
        $planId = $request->query('plan', 'premium_trial');
        $expiresAt = $request->query('expires_at', '');
        $limitsStr = $request->query('limits', '{}');
        
        if (!$email || !$token) {
            return redirect('/login')->with('error', 'Invalid SSO parameters.');
        }

        $restaurantName = $request->query('restaurant_name', '');
        $restaurantAddress = $request->query('restaurant_address', '');
        $restaurantPhone = $request->query('restaurant_phone', '');
        $restaurantPan = $request->query('restaurant_pan', '');

        // Validate the cryptographic signature of the exact payload
        $payloadWithRest = "{$email}|{$planId}|{$expiresAt}|{$limitsStr}|{$restaurantName}|{$restaurantAddress}|{$restaurantPhone}";
        $expectedTokenWithRest = hash_hmac('sha256', $payloadWithRest, 'DrestroPOS_Secure_Key_2026_X9P2');

        $payload = "{$email}|{$planId}|{$expiresAt}|{$limitsStr}";
        $expectedToken = hash_hmac('sha256', $payload, 'DrestroPOS_Secure_Key_2026_X9P2');

        // Fallback check for old tokens (backward compatibility just in case)
        $oldExpectedToken = hash_hmac('sha256', $email, 'DrestroPOS_Secure_Key_2026_X9P2');

        $payloadSimple = "{$email}|{$planId}";
        $expectedTokenSimple = hash_hmac('sha256', $payloadSimple, 'DrestroPOS_Secure_Key_2026_X9P2');

        if (!hash_equals($expectedTokenSimple, $token) && !hash_equals($expectedTokenWithRest, $token) && !hash_equals($expectedToken, $token) && !hash_equals($oldExpectedToken, $token)) {
            \Illuminate\Support\Facades\Log::warning('SSO Verification Mismatch', [
                'email' => $email,
                'received_token' => $token,
                'expected_simple' => $expectedTokenSimple,
                'expected_long' => $expectedTokenWithRest,
                'expected_med' => $expectedToken,
                'expected_old' => $oldExpectedToken,
                'all_params' => $request->all()
            ]);
            return redirect('/login')->with('error', 'Unauthorized SSO request.');
        }

        // Determine if it's a trial vs lifetime free plan
        $isFreePlan = ($planId === 'free' || str_contains(strtolower($planId), 'free'));
        $isTrial = str_contains(strtolower($planId), 'trial');

        if ($isFreePlan) {
            $displayPlan = 'Free Plan';
            $expiresAtVal = null;
        } else {
            $displayPlan = ucfirst(str_replace('_trial', '', $planId));
            $expiresAtVal = !empty($expiresAt) ? \Carbon\Carbon::parse($expiresAt)->toDateTimeString() : null;
        }
        
        $limits = json_decode($limitsStr, true) ?? [];

        $slug = $request->query('slug', 'demo');
        $licenseKey = $request->query('license_key', null);

        $restaurantTagline = $request->query('restaurant_tagline', '');
        $restaurantWard = $request->query('restaurant_ward', '');
        $restaurantCity = $request->query('restaurant_city', '');
        $restaurantEmail = $request->query('restaurant_email', '');
        $restaurantCurrency = $request->query('restaurant_currency', 'Rs.');
        $restaurantTax = $request->query('restaurant_tax', '0');
        $restaurantServiceCharge = $request->query('restaurant_service_charge', '0');
        $restaurantCalendar = $request->query('restaurant_calendar', 'AD');

        // Find or create the restaurant using the slug passed from SSO
        $restaurant = \App\Models\Restaurant::where('slug', $slug)->first();
        
        $licenseData = [
            'plan' => $displayPlan,
            'status' => $isTrial ? 'trial' : 'active',
            'expires_at' => $expiresAtVal,
            'features' => ['all'],
            'limits' => $limits
        ];

        $emailVal = $restaurantEmail ?: $email;
        $cityVal = $restaurantCity;
        $wardVal = $restaurantWard;
        if (empty($cityVal) && !empty($restaurantAddress) && str_contains($restaurantAddress, ',')) {
            $parts = array_map('trim', explode(',', $restaurantAddress));
            if (count($parts) >= 2) {
                $cityVal = end($parts);
            }
        }

        if (!$restaurant) {
            $restaurant = \App\Models\Restaurant::create([
                'name' => $restaurantName ?: ($name . ' POS'),
                'slug' => $slug,
                'address' => $restaurantAddress ?: null,
                'phone' => $restaurantPhone ?: null,
                'pan_number' => $restaurantPan ?: null,
                'tagline' => $restaurantTagline ?: null,
                'ward' => $wardVal ?: null,
                'city' => $cityVal ?: null,
                'email' => $emailVal ?: null,
                'currency' => $restaurantCurrency ?: 'Rs.',
                'tax_percent' => floatval($restaurantTax),
                'service_charge_percent' => floatval($restaurantServiceCharge),
                'date_calendar_type' => $restaurantCalendar ?: 'AD',
                'license_key' => $licenseKey,
                'license_data' => $licenseData
            ]);
        } else {
            // Update the existing restaurant's identity with the latest synced data from SaaS
            $restaurant->update([
                'name' => $restaurantName ?: $restaurant->name,
                'address' => $restaurantAddress ?: $restaurant->address,
                'phone' => $restaurantPhone ?: $restaurant->phone,
                'pan_number' => $restaurantPan ?: $restaurant->pan_number,
                'tagline' => $restaurantTagline ?: $restaurant->tagline,
                'ward' => $wardVal ?: $restaurant->ward,
                'city' => $cityVal ?: $restaurant->city,
                'email' => $emailVal ?: $restaurant->email,
                'currency' => $restaurantCurrency ?: $restaurant->currency,
                'tax_percent' => floatval($restaurantTax),
                'service_charge_percent' => floatval($restaurantServiceCharge),
                'date_calendar_type' => $restaurantCalendar ?: $restaurant->date_calendar_type,
                'license_key' => $licenseKey ?: $restaurant->license_key,
                'license_data' => $licenseData
            ]);
        }

        // Bind the restaurant so global scopes work
        app()->instance('restaurant', $restaurant);

        // Find user directly via DB to bypass ALL Eloquent global scopes
        $userRow = DB::table('users')->where('email', $email)->first();
        
        if (!$userRow) {
            // Insert directly to bypass global scopes and creating events
            $userId = DB::table('users')->insertGetId([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make(\Illuminate\Support\Str::random(16)),
                'role' => 'super_admin',
                'restaurant_id' => $restaurant->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $user = User::withoutGlobalScopes()->find($userId);
        } else {
            // Update the user's restaurant assignment
            DB::table('users')->where('id', $userRow->id)->update([
                'role' => 'super_admin',
                'restaurant_id' => $restaurant->id,
                'updated_at' => now(),
            ]);
            $user = User::withoutGlobalScopes()->find($userRow->id);
        }

        // Log them in first (this regenerates the session ID)
        Auth::login($user, true);

        // Store tenant_slug AFTER login so it survives session regeneration
        session()->put('tenant_slug', $slug);
        session()->save();

        // Send them to the dashboard
        return redirect('/admin');
    }

    public function handleAdminAction(Request $request)
    {
        $slug = $request->input('slug');
        $action = $request->input('action'); // 'reset' or 'delete'
        $token = $request->input('token');

        if (!$slug || !$action || !$token) {
            return response()->json([
                'success' => false,
                'message' => 'Missing parameter(s).'
            ], 400);
        }

        // Validate signature
        $signData = "{$slug}|{$action}";
        $expectedToken = hash_hmac('sha256', $signData, 'DrestroPOS_Secure_Key_2026_X9P2');

        if (!hash_equals($expectedToken, $token)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized request signature.'
            ], 401);
        }

        // Resolve restaurant by slug
        $restaurant = \App\Models\Restaurant::where('slug', $slug)->first();
        if (!$restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant not found.'
            ], 404);
        }

        $restaurantId = $restaurant->id;

        // Self-healing check for feature columns in restaurants table
        if (!\Illuminate\Support\Facades\Schema::hasColumn('restaurants', 'feature_hotel')) {
            try {
                \Illuminate\Support\Facades\Schema::table('restaurants', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->boolean('feature_hotel')->default(false);
                    $table->boolean('feature_inventory')->default(true);
                    $table->boolean('feature_reports')->default(true);
                    $table->boolean('feature_daybook')->default(true);
                    $table->boolean('feature_ebilling')->default(false);
                    $table->boolean('feature_multi_user')->default(true);
                    $table->boolean('feature_kot_bot')->default(true);
                });
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Self-healing feature columns failed: ' . $e->getMessage());
            }
        }

        try {
            if ($action === 'reset') {
                \Illuminate\Support\Facades\DB::table('orders')->where('restaurant_id', $restaurantId)->delete();
                if (\Illuminate\Support\Facades\Schema::hasTable('inventory_logs')) {
                    \Illuminate\Support\Facades\DB::table('inventory_logs')->where('restaurant_id', $restaurantId)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('invoices')) {
                    \Illuminate\Support\Facades\DB::table('invoices')->where('restaurant_id', $restaurantId)->delete();
                }
                
                \Illuminate\Support\Facades\Cache::flush();
                return response()->json([
                    'success' => true,
                    'message' => 'Restaurant data successfully reset.'
                ]);
            } elseif ($action === 'delete') {
                // Delete everything cascadingly
                \Illuminate\Support\Facades\DB::table('orders')->where('restaurant_id', $restaurantId)->delete();
                if (\Illuminate\Support\Facades\Schema::hasTable('inventory_logs')) {
                    \Illuminate\Support\Facades\DB::table('inventory_logs')->where('restaurant_id', $restaurantId)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('invoices')) {
                    \Illuminate\Support\Facades\DB::table('invoices')->where('restaurant_id', $restaurantId)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('inventory_items')) {
                    \Illuminate\Support\Facades\DB::table('inventory_items')->where('restaurant_id', $restaurantId)->delete();
                }
                
                \Illuminate\Support\Facades\DB::table('menu_items')->where('restaurant_id', $restaurantId)->delete();
                \Illuminate\Support\Facades\DB::table('menu_categories')->where('restaurant_id', $restaurantId)->delete();
                \Illuminate\Support\Facades\DB::table('tables')->where('restaurant_id', $restaurantId)->delete();
                
                // Finally delete users and the restaurant
                \Illuminate\Support\Facades\DB::table('users')->where('restaurant_id', $restaurantId)->delete();
                \Illuminate\Support\Facades\DB::table('restaurants')->where('id', $restaurantId)->delete();

                \Illuminate\Support\Facades\Cache::flush();
                return response()->json([
                    'success' => true,
                    'message' => 'Restaurant completely deleted.'
                ]);
            } elseif ($action === 'update_details') {
                $name = $request->input('name');
                $phone = $request->input('phone');
                $panNumber = $request->input('pan_number');
                $tagline = $request->input('tagline');
                $address = $request->input('address');
                $ward = $request->input('ward');
                $city = $request->input('city');
                $email = $request->input('email');

                $updateData = array_filter([
                    'name' => $name,
                    'phone' => $phone,
                    'pan_number' => $panNumber,
                    'tagline' => $tagline,
                    'address' => $address,
                    'ward' => $ward,
                    'city' => $city,
                    'email' => $email,
                ], function($v) { return $v !== null; });

                if ($request->has('feature_hotel')) $updateData['feature_hotel'] = (bool)$request->input('feature_hotel');
                if ($request->has('feature_inventory')) $updateData['feature_inventory'] = (bool)$request->input('feature_inventory');
                if ($request->has('feature_reports')) $updateData['feature_reports'] = (bool)$request->input('feature_reports');
                if ($request->has('feature_daybook')) $updateData['feature_daybook'] = (bool)$request->input('feature_daybook');
                if ($request->has('feature_ebilling')) $updateData['feature_ebilling'] = (bool)$request->input('feature_ebilling');
                if ($request->has('feature_multi_user')) $updateData['feature_multi_user'] = (bool)$request->input('feature_multi_user');

                $restaurant->update($updateData);
                \Illuminate\Support\Facades\Cache::flush();

                return response()->json([
                    'success' => true,
                    'message' => 'Restaurant details & feature access updated in POS successfully.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid action.'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Action failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
