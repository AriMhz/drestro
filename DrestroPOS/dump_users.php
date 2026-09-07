<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach(\App\Models\User::withoutGlobalScopes()->get() as $u) {
    echo $u->id . ' - ' . $u->first_name . ' - ' . $u->role . ' - ' . json_encode($u->allowed_pages) . "\n";
}
