<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
$route = "\nRoute::get('/test-html', function() { \$html = view('livewire.admin.menu-manager')->render(); return ['length' => strlen(\$html), 'start' => substr(\$html, 0, 50), 'base64' => base64_encode(substr(\$html, 0, 100))]; });";
file_put_contents('../routes/web.php', $route, FILE_APPEND);
echo "<h1>Route injected. Please visit /test-html</h1>";
?>
