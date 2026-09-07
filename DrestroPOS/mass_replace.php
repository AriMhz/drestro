<?php

$dirs = [
    __DIR__ . '/app',
    __DIR__ . '/routes',
    __DIR__ . '/resources',
];

$replacements = [
    '\\App\\Models\\Restaurant::first()' => 'current_restaurant()',
    'Restaurant::first()' => 'current_restaurant()',
];

function processDirectory($dir, $replacements) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryModel($dir));
    foreach ($files as $file) {
        if ($file->isDir()) continue;
        if (!in_array($file->getExtension(), ['php'])) continue;

        $content = file_get_contents($file->getRealPath());
        $original = $content;
        
        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        if ($content !== $original) {
            file_put_contents($file->getRealPath(), $content);
            echo "Updated: " . $file->getRealPath() . "\n";
        }
    }
}

class RecursiveDirectoryModel extends RecursiveDirectoryIterator {
    public function __construct($path) {
        parent::__construct($path, \FilesystemIterator::SKIP_DOTS);
    }
}

foreach ($dirs as $dir) {
    processDirectory($dir, $replacements);
}

echo "Done replacing.\n";
