<?php
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "<h1>OPcache cleared successfully!</h1>";
        echo "<p>Your server's PHP memory cache has been flushed. The new backend changes should now take effect.</p>";
    } else {
        echo "<h1>OPcache is disabled or could not be cleared.</h1>";
    }
} else {
    echo "<h1>OPcache function does not exist on this server.</h1>";
}

// Also try to clear realpath cache
clearstatcache(true);
echo "<p>Realpath cache cleared.</p>";
?>
