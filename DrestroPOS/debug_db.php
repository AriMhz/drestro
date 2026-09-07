<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$session = \Illuminate\Support\Facades\DB::table('sessions')->orderBy('last_activity', 'desc')->first();
if ($session) {
    echo "ID: " . $session->id . "\n";
    echo "Payload: " . base64_decode($session->payload) . "\n";
} else {
    echo "No sessions found.\n";
}
