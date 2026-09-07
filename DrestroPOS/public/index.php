<?php

// Prevent notices, warnings, and deprecation alerts from causing 500 errors
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// EMERGENCY CACHE KILLER
if (isset($_GET['kill_cache'])) {
    $files = glob(__DIR__.'/../storage/framework/views/*.php');
    if ($files) {
        foreach($files as $file) @unlink($file);
    }
    @unlink(__DIR__.'/../bootstrap/cache/routes-v7.php');
    @unlink(__DIR__.'/../bootstrap/cache/config.php');
    @unlink(__DIR__.'/../bootstrap/cache/services.php');
    @unlink(__DIR__.'/../bootstrap/cache/packages.php');
    echo "<h1>Cache Killed Successfully!</h1><p>The system memory has been cleared.</p><p><a href='/staff/take-order'>Click here to go back to Take Order</a></p>";
    exit;
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
