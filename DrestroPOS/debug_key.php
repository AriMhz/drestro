<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\LicenseManager;

$key = 'eyJjbGllbnQiOiI5YWRjY2FmYmY0MmM0MTZlNmNiMDMxMDJjZDIxNmFmMDkwZWIyMzZlOWY0NTAxZmFmNDA0YTkyNGEyMDUxZmFlIiwicGxhbiI6IlByZW1pdW0iLCJtYWNoaW5lX2lkIjoiOWFkY2NhZmJmNDJjNDE2ZTZjYjAzMTAyY2QyMTZhZjA5MGViMjM2ZTlmNDUwMWZhZjQwNGE5MjRhMjA1MWZhZSIsImZlYXR1cmVzIjpbInRha2Vfb3JkZXIiLCJjYXNoaWVyIiwia2l0Y2hlbl9kaXNwbGF5Iiwid2FpdGVyX2Rhc2hib2FyZCIsImludmVudG9yeSIsInJlcG9ydHMiLCJxcl9tZW51Il0sImlzc3VlZF9hdCI6IjIwMjYtMDUtMDciLCJleHBpcmVzX2F0IjoiMjAyNy0wNS0wNyJ9.c0fba8d457016a602ae352e8e23e9726ce7897a8ad711a461769525ecbc7262b';

echo "Testing Key...\n";
$data = LicenseManager::verify($key);

if ($data) {
    echo "SUCCESS! Data found:\n";
    print_r($data);
} else {
    echo "FAILED! Checking why...\n";
    $parts = explode('.', $key);
    if (count($parts) !== 2) {
        echo "Error: Key does not have 2 parts (dot separator missing).\n";
    } else {
        $payload = json_decode(base64_decode($parts[0]), true);
        echo "Payload decoded: " . ($payload ? "YES" : "NO") . "\n";
        print_r($payload);
        
        $machineId = LicenseManager::getMachineId();
        echo "Current Machine ID (Hashed): " . $machineId . "\n";
        echo "Payload Machine ID: " . ($payload['machine_id'] ?? 'MISSING') . "\n";
        
        if (strtoupper($machineId) === strtoupper($payload['machine_id'] ?? '')) {
            echo "IDs MATCH! So signature must be the issue.\n";
        } else {
            echo "IDs DO NOT MATCH!\n";
        }
    }
}
