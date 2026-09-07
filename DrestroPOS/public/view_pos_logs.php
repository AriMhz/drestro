<?php
$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logFile)) {
    echo "<h1>Laravel Logs:</h1>";
    echo "<pre>";
    // Print last 50 lines
    $lines = file($logFile);
    $lastLines = array_slice($lines, -50);
    echo htmlspecialchars(implode("", $lastLines));
    echo "</pre>";
} else {
    echo "<h1>No log file found!</h1>";
}
