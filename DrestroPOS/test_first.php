<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

class TestRestaurant extends \App\Models\Restaurant {
    public static function first($columns = ['*']) {
        echo "Overridden first() called!\n";
        return parent::first($columns);
    }
}

TestRestaurant::first();
