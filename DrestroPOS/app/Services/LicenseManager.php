<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class LicenseManager
{
    /**
     * The Secret Key used to verify licenses.
     * In production, since you are obfuscating the code with IonCube,
     * this string will be completely hidden from the client.
     */
    private static $secretKey = 'DrestroPOS_Secure_Key_2026_X9P2';

    /**
     * Get the unique Machine ID (MAC Address or UUID).
     * Always reads directly from hardware to ensure consistency.
     */
    public static function getMachineId()
    {
        $id = '';
        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows: Use PowerShell to get UUID (wmic is deprecated and unreliable)
                $output = shell_exec('powershell -NoProfile -Command "(Get-CimInstance -ClassName Win32_ComputerSystemProduct).UUID" 2>NUL');
                if ($output) {
                    // Remove ANY non-alphanumeric characters, dashes, or invisible BOMs
                    $id = preg_replace('/[^a-zA-Z0-9-]/', '', trim($output));
                }
                // Fallback: try wmic if PowerShell fails
                if (empty($id)) {
                    $output = shell_exec('wmic csproduct get uuid 2>NUL');
                    if ($output) {
                        $lines = explode("\n", trim($output));
                        if (isset($lines[1])) {
                            $id = preg_replace('/[^a-zA-Z0-9-]/', '', trim($lines[1]));
                        }
                    }
                }
            } else {
                // Linux/Mac: get machine-id
                $output = shell_exec('cat /etc/machine-id 2>/dev/null');
                if ($output) {
                    $id = trim($output);
                } else {
                    $output = shell_exec("ifconfig -a | grep -Po 'HWaddr \\K.*$'");
                    $id = trim($output);
                }
            }
        } catch (Exception $e) {
            Log::error('Could not get Machine ID: ' . $e->getMessage());
        }

        if (empty($id)) {
            // Fallback: Create a persistent pseudo-ID in a fixed location
            $idFile = storage_path('app/machine_id');
            if (!file_exists($idFile)) {
                $dir = dirname($idFile);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                file_put_contents($idFile, uniqid('mac_', true));
            }
            $id = file_get_contents($idFile);
        }

        return hash('sha256', $id);
    }

    /**
     * Verify and decode a license key.
     * Sends the machine_id alongside the key so the server can enforce hardware binding.
     * Returns false if invalid, or an array of license data if valid.
     */
    public static function verify($licenseKeyString, $secretKeyOverride = null)
    {
        $licenseKeyString = trim($licenseKeyString);
        if (empty($licenseKeyString)) {
            return false;
        }

        try {
            // Determine the URL based on environment
            $billingApiUrl = config('app.env') === 'production' 
                ? 'https://drestro.com/api/license/verify' 
                : 'https://drestro.com/api/license/verify';

            // Always send the machine_id so the server can enforce hardware binding
            $machineId = self::getMachineId();

            $response = \Illuminate\Support\Facades\Http::timeout(5)->post($billingApiUrl, [
                'license_key' => $licenseKeyString,
                'machine_id' => $machineId,
                'domain' => request()->getHost(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['valid']) && $data['valid']) {
                    // Return the full license data provided by the server
                    return $data['data'] ?? [
                        'plan' => $data['clientName'] ?? 'Premium',
                        'status' => 'active',
                        'expires_at' => $data['expiryDate'] ?? null,
                        'features' => ['all'],
                    ];
                }
                
                // Return the error message for display
                Log::warning('License rejected: ' . ($data['message'] ?? 'Unknown reason'));
            }
        } catch (Exception $e) {
            Log::error('License Validation API Failed: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Check if a specific feature is enabled in the given license data.
     */
    public static function hasFeature($licenseData, $featureKey)
    {
        if (!$licenseData || !isset($licenseData['features'])) {
            return false;
        }
        return in_array($featureKey, $licenseData['features']);
    }

    /**
     * Get the default limits for a free/unlicensed installation.
     * Provides a 14-day trial from the date of installation.
     */
    public static function getFreeLimits()
    {
        $restaurant = current_restaurant();
        $installedAt = $restaurant->created_at ?? now();

        $allFeatures = [
            'take_order', 'take_room_service', 'cashier_panel', 'kitchen_display', 
            'waiter_dashboard', 'bar_display', 'inventory', 'reports', 
            'menu_manager', 'restaurant_tables', 'hotel_room_manager', 
            'staff_management', 'hotel_reception', 'qr_menu'
        ];

        return [
            'plan' => 'Free Plan (Lifetime)',
            'is_trial' => false,
            'is_expired' => false,
            'features' => $allFeatures,
            'limits' => [
                'tables' => 0,
                'users' => 1, // Max 1 user on Free Plan!
                'items' => 0
            ],
            'expires_at' => null,
            'installed_at' => $installedAt->format('Y-m-d H:i:s')
        ];
    }
}
