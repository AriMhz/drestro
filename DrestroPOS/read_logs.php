<?php
$logFile = "../storage/logs/laravel.log";
if (!file_exists($logFile)) {
    echo "<h1>Log file not found!</h1>";
    exit;
}
$lines = file($logFile);
$lastLines = array_slice($lines, -200);
echo "<pre style=\"background:#111; color:#0f0; padding: 20px; white-space: pre-wrap; word-wrap: break-word;\">";
foreach ($lastLines as $line) {
    echo htmlspecialchars($line);
}
echo "</pre>";
?>