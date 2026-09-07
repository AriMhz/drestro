$files = Get-ChildItem -Path resources\views\livewire\admin -Name -Filter *.blade.php
$code = "<?php`n"
$code += "ini_set('display_errors', 1); error_reporting(E_ALL);`n"
foreach ($file in $files) {
    $base64 = [Convert]::ToBase64String([IO.File]::ReadAllBytes("resources\views\livewire\admin\$file"))
    $code += "file_put_contents('../resources/views/livewire/admin/$file', base64_decode('$base64'));`n"
}
$code += '$views = glob("../storage/framework/views/*.php"); foreach($views as $v) { @unlink($v); }' . "`n"
$code += "if (function_exists('opcache_reset')) { opcache_reset(); }`n"
$code += "echo '<h1>SUCCESS! ALL admin views forcefully rewritten!</h1>';`n"
$code += "?>"
[IO.File]::WriteAllText("fix_all_views.php", $code)
