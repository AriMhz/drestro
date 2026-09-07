<?php
// Script to activate the production Drestro POS installation

// We run this from the Downloads folder but target the production DB
$prodDb = 'c:\DrestroPOS\database\database.sqlite';

if (!file_exists($prodDb)) {
    die("Error: Production database not found at $prodDb\n");
}

// Override the DB path for Laravel
putenv("DB_DATABASE=$prodDb");

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\LicenseManager;
use App\Models\Restaurant;

$secretKey = 'DrestroPOS_Secure_Key_2026_X9P2';

// 1. Get current Machine ID
$machineId = LicenseManager::getMachineId();
echo "Current Machine ID: $machineId\n";

// 2. Create Premium Payload
$payload = [
    'client' => 'Master Admin',
    'plan' => 'Premium',
    'machine_id' => $machineId,
    'features' => ['take_order', 'cashier', 'kitchen_display', 'waiter_dashboard', 'bar_display', 'inventory', 'reports', 'qr_menu'],
    'issued_at' => date('Y-m-d'),
    'expires_at' => '2030-01-01',
];

$payloadJson = json_encode($payload);
$payloadBase64 = base64_encode($payloadJson);
$signature = hash_hmac('sha256', $payloadBase64, $secretKey);
$licenseKey = $payloadBase64 . '.' . $signature;

echo "Generated License Key: " . substr($licenseKey, 0, 20) . "...\n";

// 3. Save to Production DB
$restaurant = Restaurant::first();
if ($restaurant) {
    $restaurant->license_key = $licenseKey;
    $restaurant->machine_id = $machineId;
    $restaurant->license_data = $payload; // Save the array directly (Laravel will cast it)
    $restaurant->save();
    echo "SUCCESS: Production database updated with Premium license!\n";
    
    // Verify
    $restaurant->refresh();
    $recheck = LicenseManager::verify($restaurant->license_key);
    echo "Verification: " . ($recheck ? "VERIFIED (Plan: {$recheck['plan']})" : "FAILED") . "\n";
} else {
    echo "ERROR: No restaurant record found in production database!\n";
}
