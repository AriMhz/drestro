<?php
$bladePath = __DIR__ . '/../resources/views/components/layouts/app.blade.php';
if (file_exists($bladePath)) {
    $content = file_get_contents($bladePath);
    $debugScript = "<script>window.onerror = function(msg, url, line, col, error) { alert('JS Error: ' + msg + '\\nLine: ' + line + '\\nCol: ' + col); return false; };</script>";
    if (strpos($content, 'window.onerror') === false) {
        $newContent = str_replace('<head>', "<head>\n" . $debugScript, $content);
        file_put_contents($bladePath, $newContent);
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        echo "<h1>Debug Script Injected!</h1><p>Please hard refresh the Staff page. If there is a JS error, an alert popup will show it. Please send me a screenshot of the popup!</p>";
    } else {
        echo "<h1>Debug Script Already Injected!</h1>";
    }
} else {
    echo "<h1>app.blade.php not found</h1>";
}
?>
