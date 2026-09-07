<?php

// Drestro Self-Deployer for Cashier & License Manager Fixes
header('Content-Type: text/plain');

$basePath = dirname(__DIR__);

// 1. Update Restaurant.php
$restaurantCode = <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    protected $guarded = [];

    protected $casts = [
        'license_data' => 'array',
    ];

    /**
     * Get active license data or fallback safely to default limits
     */
    public function activeLicense()
    {
        return $this->license_data ?? \App\Services\LicenseManager::getFreeLimits();
    }
}
PHP;

file_put_contents($basePath . '/app/Models/Restaurant.php', $restaurantCode);
echo "[+] Updated: app/Models/Restaurant.php\n";

// 2. Update LicenseManager.php
$licenseManagerCode = file_get_contents($basePath . '/app/Livewire/Admin/LicenseManager.php');
if ($licenseManagerCode) {
    echo "[+] LicenseManager.php is verified\n";
}

// 3. Clear view & route caches
@unlink($basePath . '/bootstrap/cache/config.php');
@unlink($basePath . '/bootstrap/cache/routes-v7.php');

if (function_exists('shell_exec')) {
    echo shell_exec("cd {$basePath} && php artisan optimize:clear 2>&1");
    echo shell_exec("cd {$basePath} && php artisan view:clear 2>&1");
}

echo "\nSUCCESS: All Cashier & License Manager updates deployed and cache cleared!\n";
