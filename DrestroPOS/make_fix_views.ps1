$m = [Convert]::ToBase64String([IO.File]::ReadAllBytes('resources\views\livewire\admin\menu-manager.blade.php'))
$t = [Convert]::ToBase64String([IO.File]::ReadAllBytes('resources\views\livewire\admin\table-manager.blade.php'))
$i = [Convert]::ToBase64String([IO.File]::ReadAllBytes('resources\views\livewire\admin\inventory-manager.blade.php'))
$phpCode = '<?php
file_put_contents("../resources/views/livewire/admin/menu-manager.blade.php", base64_decode("' + $m + '"));
file_put_contents("../resources/views/livewire/admin/table-manager.blade.php", base64_decode("' + $t + '"));
file_put_contents("../resources/views/livewire/admin/inventory-manager.blade.php", base64_decode("' + $i + '"));
try { \Illuminate\Support\Facades\Artisan::call("view:clear"); } catch (\Exception $e) {}
opcache_reset();
echo "<h1>SUCCESS! All component views forcefully rewritten!</h1>";
?>'
[IO.File]::WriteAllText('fix_all_views.php', $phpCode)
