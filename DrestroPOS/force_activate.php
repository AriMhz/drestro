<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$key = 'eyJjbGllbnQiOiI5YWRjY2FmYmY0MmM0MTZlNmNiMDMxMDJjZDIxNmFmMDkwZWIyMzZlOWY0NTAxZmFmNDA0YTkyNGEyMDUxZmFlIiwicGxhbiI6IlByZW1pdW0iLCJtYWNoaW5lX2lkIjoiOWFkY2NhZmJmNDJjNDE2ZTZjYjAzMTAyY2QyMTZhZjA5MGViMjM2ZTlmNDUwMWZhZjQwNGE5MjRhMjA1MWZhZSIsImZlYXR1cmVzIjpbInRha2Vfb3JkZXIiLCJjYXNoaWVyIiwia2l0Y2hlbl9kaXNwbGF5Iiwid2FpdGVyX2Rhc2hib2FyZCIsImludmVudG9yeSIsInJlcG9ydHMiLCJxcl9tZW51Il0sImlzc3VlZF9hdCI6IjIwMjYtMDUtMDciLCJleHBpcmVzX2F0IjoiMjAyNy0wNS0wNyJ9.c0fba8d457016a602ae352e8e23e9726ce7897a8ad711a461769525ecbc7262b';
$parts = explode('.', $key);
$payload = json_decode(base64_decode($parts[0]), true);

\App\Models\Restaurant::query()->update([
    'license_key' => $key,
    'license_data' => $payload,
    'machine_id' => $payload['machine_id']
]);

\Illuminate\Support\Facades\Cache::flush();

echo "--- CONGRATULATIONS ---\n";
echo "SYSTEM HAS BEEN ACTIVATED MANUALLY.\n";
echo "PLAN: " . $payload['plan'] . "\n";
echo "--- REFRESH YOUR BROWSER NOW ---";
