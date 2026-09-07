<?php
$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (!file_exists($logFile)) {
    die("Log file not found.");
}
$lines = file($logFile);
$lastLines = array_slice($lines, -100);
echo "<pre>";
foreach ($lastLines as $line) {
    if (str_contains($line, 'TenantMiddleware')) {
        echo htmlspecialchars($line);
    }
}
echo "</pre>";
?>
