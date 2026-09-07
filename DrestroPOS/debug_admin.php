<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

$_COOKIE['drestro-session'] = 'eyJpdiI6IlZFeXdONnpWbi9nZlR1enhHeENIREE9PSIsInZhbHVlIjoieDBzM0w5ektPQjNwT2NIQkVIcFh1aVhmSFFNcnhEV3YzZ0FydmVhSlExMFdxSnlrdHZKNFQ2T0lQazBHd1F4UDlHYVJFb0Q0YUVSSVRvQmt0Q1h5bWZTamZYTG4zMy9keGtxejBzdk5BemdzOU1xWTdBVWxPK0FEQVh6Qnd2eVoiLCJtYWMiOiI0ZDFjNGVkMjM0YjkzN2M3ZTJmNDExZGNjZWE4M2IwNGJmZGY4NTA4ZDRjMTNiYjNlMWE3ZTBkNWI2NzJjYWJkIiwidGFnIjoiIn0=';
$_COOKIE['remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d'] = 'eyJpdiI6ImFzL2lLVUxMUy9ZVFNnbjVHaE9SRkE9PSIsInZhbHVlIjoiaUdtbnNZaUVHSHFhc2tZZmxNZjdva0ZjVCttQUJtR1dTMWtFRmJQU1lFS3JXck1IRG92Ris1bUFGajR3Q2RKNGVtd25oTUk4TzM5T1R6WHA2ZXVLejJxWjE5MXJJaEhLcDZLYnNnUzZsSS9yZit3R3U1dnRZV1drS0xhVUtpNTFtakVLTnJIKzdWcDlRYzFxS2c0NTcrMG5tQ2J1MFVnNXA5bWdhS1A5TWQzZjV1STM5ZlBtNUJDUXhOelh0U3dPQVpySVU5RE9hc1d5N2FaaDcyQ0JJd1ZTQ1FaV3pia1dXUk1DWVB5ekYzZz0iLCJtYWMiOiJhZTQyMjZkYTJmZWE3OGNmNDQxNzQzNzg3NzhhNmVkYzEzNGNlMWRhODQzOTUwYjExZmNmOGJmN2M2M2FjYjQ1IiwidGFnIjoiIn0=';

// Manually start session to test Auth
$request = \Illuminate\Http\Request::create('/admin', 'GET', [], $_COOKIE, [], ['HTTP_HOST' => 'localhost:8080']);

$app->make(\Illuminate\Session\SessionManager::class)->driver()->start();

$restaurant = \App\Models\Restaurant::find(4);
app()->instance('restaurant', $restaurant);
echo "Auth id: " . auth()->id() . "\n";
if (!auth()->check()) {
    echo "Query log:\n";
    \Illuminate\Support\Facades\DB::enableQueryLog();
    auth()->user();
    print_r(\Illuminate\Support\Facades\DB::getQueryLog());
}
