import os
import zipfile
import base64

files_to_deploy = [
    "resources/views/livewire/admin/dashboard.blade.php",
    "resources/views/livewire/admin/reports-manager.blade.php",
    "resources/views/livewire/admin/inventory-manager.blade.php",
    "resources/views/livewire/admin/menu-manager.blade.php",
    "resources/views/livewire/admin/table-manager.blade.php",
    "resources/views/livewire/admin/room-manager.blade.php",
    "resources/views/livewire/admin/staff-manager.blade.php",
    "resources/views/livewire/admin/settings.blade.php",
    "resources/views/livewire/admin/support-tickets.blade.php",
    "resources/views/livewire/admin/license-manager.blade.php",
    "resources/views/livewire/staff/hotel-cashier.blade.php",
    "resources/views/livewire/staff/cashier-panel.blade.php"
]

with zipfile.ZipFile('deploy.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    for rel_path in files_to_deploy:
        if os.path.exists(rel_path):
            zipf.write(rel_path, arcname=rel_path.replace('\\', '/'))
            print(f"Added: {rel_path}")
        else:
            print(f"ERROR: File not found: {rel_path}")

with open('deploy.zip', 'rb') as f:
    zip_b64 = base64.b64encode(f.read()).decode('utf-8')

php_script = "<?php\n"
php_script += "require __DIR__.'/../vendor/autoload.php';\n"
php_script += "$app = require_once __DIR__.'/../bootstrap/app.php';\n"
php_script += "$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);\n"
php_script += "$kernel->bootstrap();\n"
php_script += "$zipData = base64_decode('" + zip_b64 + "');\n"
php_script += "file_put_contents('deploy.zip', $zipData);\n"
php_script += "$zip = new ZipArchive;\n"
php_script += "if ($zip->open('deploy.zip') === TRUE) {\n"
php_script += "    $extracted = $zip->extractTo('../');\n"
php_script += "    $zip->close();\n"
php_script += "    unlink('deploy.zip');\n"
php_script += "    \\Illuminate\\Support\\Facades\\Artisan::call('view:clear');\n"
php_script += "    \\Illuminate\\Support\\Facades\\Artisan::call('cache:clear');\n"
php_script += "    if ($extracted) {\n"
php_script += "        echo '<h1>Calendar & Page Header Mobile Fixes Deployed Successfully!</h1>';\n"
php_script += "    } else {\n"
php_script += "        echo '<h1 style=\"color: red;\">ERROR: File Extraction Failed!</h1>';\n"
php_script += "        echo '<p>Your web server does not have permission to overwrite the app files.</p>';\n"
php_script += "    }\n"
php_script += "} else {\n"
php_script += "    echo '<h1>Error opening zip</h1>';\n"
php_script += "}\n"
php_script += "?>"

with open('deploy_calendar_header_fixes.php', 'w') as f:
    f.write(php_script)

print("SUCCESS: deploy_calendar_header_fixes.php has been created!")

try:
    os.remove('deploy.zip')
except OSError:
    pass
