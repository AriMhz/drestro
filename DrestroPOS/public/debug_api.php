<?php

echo "<h1>Debug API Dashboard</h1>";

// 1. Read drestro.com .env file
$envPath = __DIR__ . '/../../drestro.com/.env';
if (!file_exists($envPath)) {
    // Try parent level
    $envPath = __DIR__ . '/../../../drestro.com/.env';
}
if (!file_exists($envPath)) {
    // Try absolute path under /home/drestro
    $envPath = '/home/drestro/htdocs/drestro.com/.env';
}

if (file_exists($envPath)) {
    echo "<h3>SaaS .env found at: $envPath</h3>";
    $envContent = file_get_contents($envPath);
    // Print without secrets
    $lines = explode("\n", $envContent);
    foreach ($lines as $line) {
        if (trim($line) && strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            if (in_array(trim($key), ['DATABASE_URL', 'AUTH_URL', 'NEXTAUTH_URL'])) {
                echo htmlspecialchars($key) . " = " . htmlspecialchars($val) . "<br>";
            }
        }
    }
} else {
    echo "<h3>SaaS .env NOT found!</h3>";
}

// 2. Fetch current restaurant details from POS database
try {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    $restaurant = \App\Models\Restaurant::first();
    if ($restaurant) {
        echo "<h3>POS Restaurant Info:</h3>";
        echo "ID: " . htmlspecialchars($restaurant->id) . "<br>";
        echo "Name: " . htmlspecialchars($restaurant->name) . "<br>";
        echo "License Key: " . htmlspecialchars($restaurant->license_key) . "<br>";
        echo "Machine ID: " . htmlspecialchars($restaurant->machine_id) . "<br>";
        
        // 3. Test HTTP request to SaaS
        echo "<h3>Testing API Connection to SaaS:</h3>";
        $response = \Illuminate\Support\Facades\Http::post('https://drestro.com/api/support/tickets', [
            'licenseKey' => $restaurant->license_key,
            'title' => 'System Debug Connection Test',
            'description' => 'Test connection from POS server',
            'priority' => 'LOW',
        ]);
        
        echo "Status: " . htmlspecialchars($response->status()) . "<br>";
        echo "Body: " . htmlspecialchars($response->body()) . "<br>";
    } else {
        echo "<h3>No POS Restaurant found!</h3>";
    }
} catch (\Exception $e) {
    echo "<h3>Error:</h3>" . htmlspecialchars($e->getMessage()) . "<br>";
}
