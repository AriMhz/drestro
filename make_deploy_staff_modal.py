import os

blade_path = "DrestroPOS/resources/views/livewire/admin/staff-manager.blade.php"
with open(blade_path, 'r', encoding='utf-8') as f:
    blade_content = f.read()

php_script = f"""<?php
$target_file = __DIR__ . '/../resources/views/livewire/admin/staff-manager.blade.php';
$content = <<< 'EOT'
{blade_content}
EOT;

file_put_contents($target_file, $content);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Http\\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\\Http\\Request::capture()
);

// Clear caches
\\Illuminate\\Support\\Facades\\Artisan::call('view:clear');
\\Illuminate\\Support\\Facades\\Artisan::call('cache:clear');

// Clear OPCache if enabled
if (function_exists('opcache_reset')) {{
    opcache_reset();
}}

echo "<h1>Deployment Successful!</h1>";
echo "<p>staff-manager.blade.php has been updated.</p>";
echo "<p>View Cache Cleared.</p>";
echo "<p>OPCache Cleared.</p>";
echo "<p>Please check your phone and desktop now!</p>";
"""

with open("deploy_staff_modal_fix.php", 'w', encoding='utf-8') as f:
    f.write(php_script)
    
print("deploy_staff_modal_fix.php created!")
