<?php
// find_db.php
// Place this in /home/drestro/htdocs/drestro.com/DrestroPOS/



$homeDir = '/home/drestro';
if (is_dir($homeDir)) {
    echo "=== Searching all of $homeDir recursively for .env and .sqlite ===\n";
    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($homeDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        $foundEnv = [];
        $foundSqlite = [];
        foreach ($iterator as $file) {
            $path = $file->getPathname();
            // Ignore standard build/cache folders to stay fast
            if (strpos($path, 'node_modules') !== false || strpos($path, 'vendor') !== false || strpos($path, '.next') !== false || strpos($path, '.git') !== false || strpos($path, 'cache') !== false) {
                continue;
            }
            if ($file->isFile()) {
                if ($file->getFilename() === '.env') {
                    $foundEnv[] = $path;
                }
                if (strpos($file->getFilename(), '.sqlite') !== false) {
                    $foundSqlite[] = $path;
                }
            }
        }
        
        echo "\nFound .env files:\n";
        foreach ($foundEnv as $env) {
            echo "  $env\n";
            // Print database connection details
            $lines = file($env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, 'DB_') !== false) {
                    echo "    $line\n";
                }
            }
        }
        
        echo "\nFound .sqlite files:\n";
        foreach ($foundSqlite as $sql) {
            echo "  $sql\n";
        }
    } catch (Exception $e) {
        echo "Search error: " . $e->getMessage() . "\n";
    }
} else {
    echo "$homeDir is not a directory!\n";
}
