<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "<h1>Deleting Debug Ticket on SaaS...</h1>";
$response = \Illuminate\Support\Facades\Http::delete('https://drestro.com/api/support/tickets?licenseKey=DR-648F-0693-B406&titleMatch=' . urlencode('System Debug Connection Test'));

echo "Status: " . htmlspecialchars($response->status()) . "<br>";
echo "Body: " . htmlspecialchars($response->body()) . "<br>";
