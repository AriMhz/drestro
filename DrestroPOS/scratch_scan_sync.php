<?php
$localIp = gethostbyname(gethostname());
$baseIp = substr($localIp, 0, strrpos($localIp, '.') + 1);

echo "Starting quick scan...\n";
$start = microtime(true);
$found = [];

for ($i = 1; $i <= 254; $i++) {
    $ip = $baseIp . $i;
    if ($ip == $localIp) continue;
    
    // 0.02 seconds = 20ms timeout
    $connection = @fsockopen($ip, 9100, $errno, $errstr, 0.02);
    if (is_resource($connection)) {
        $found[] = $ip;
        fclose($connection);
    }
}

$end = microtime(true);
echo "Scan took " . ($end - $start) . " seconds.\n";
print_r($found);
