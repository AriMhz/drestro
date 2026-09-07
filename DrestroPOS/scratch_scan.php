<?php
$localIp = gethostbyname(gethostname());
$baseIp = substr($localIp, 0, strrpos($localIp, '.') + 1);
$sockets = [];

echo "Scanning {$baseIp}* on port 9100...\n";

for ($i = 1; $i <= 254; $i++) {
    $ip = $baseIp . $i;
    // skip self
    if ($ip === $localIp) continue;
    
    // Some windows setups may fail with async connect, so we silence warnings
    $socket = @stream_socket_client("tcp://$ip:9100", $errno, $errstr, 0.5, STREAM_CLIENT_ASYNC_CONNECT | STREAM_CLIENT_CONNECT);
    if ($socket) {
        stream_set_blocking($socket, false);
        $sockets[$ip] = $socket;
    }
}

$write = $sockets;
$read = null;
$except = null;

if (!empty($write)) {
    // Wait up to 1.5 seconds
    $num_changed = @stream_select($read, $write, $except, 1, 500000);
    
    if ($num_changed > 0) {
        foreach ($write as $socket) {
            $ip = array_search($socket, $sockets);
            if ($ip) {
                // On Windows, stream_select might mark connection refused as writable
                // we check if we can actually get the peer name
                $peerName = @stream_socket_get_name($socket, true);
                if ($peerName !== false) {
                    echo "Found open port on: $ip\n";
                }
            }
        }
    }
    
    foreach ($sockets as $socket) {
        @fclose($socket);
    }
}
echo "Done.\n";
