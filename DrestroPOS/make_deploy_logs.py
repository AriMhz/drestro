import os
import base64

php_script = """<?php
$logPath = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logPath)) {
    $lines = file($logPath);
    // Get last 200 lines
    $lastLines = array_slice($lines, -200);
    echo "<h1>Server Error Logs (Last 200 Lines)</h1>";
    echo "<div style='background: #111; color: #0f0; padding: 20px; font-family: monospace; font-size: 13px; overflow-x: auto; white-space: pre-wrap; line-height: 1.5;'>";
    foreach ($lastLines as $line) {
        echo htmlspecialchars($line);
    }
    echo "</div>";
} else {
    echo "<h1>Log file not found at " . htmlspecialchars($logPath) . "</h1>";
}
?>"""

with open('deploy_logs.php', 'w', encoding='utf-8') as f:
    f.write(php_script)
