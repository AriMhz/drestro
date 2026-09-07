import os
import zipfile
import base64

files_to_deploy = [
    "app/Services/NepalEBillingService.php",
    "app/Livewire/Admin/Settings.php",
    "resources/views/livewire/admin/settings.blade.php",
    "app/Livewire/Staff/CashierPanel.php",
    "app/Livewire/Staff/HotelCashier.php",
    "app/Livewire/Admin/ReportsManager.php",
    "resources/views/livewire/admin/reports-manager.blade.php",
]

with zipfile.ZipFile('ebilling_pos_updates.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    for rel_path in files_to_deploy:
        if os.path.exists(rel_path):
            zipf.write(rel_path, arcname=rel_path.replace('\\', '/'))
            print(f"Added: {rel_path}")
        else:
            print(f"ERROR: File not found: {rel_path}")

with open('ebilling_pos_updates.zip', 'rb') as f:
    zip_b64 = base64.b64encode(f.read()).decode('utf-8')

php_script = "<?php\n"
php_script += "require __DIR__.'/../vendor/autoload.php';\n"
php_script += "$app = require_once __DIR__.'/../bootstrap/app.php';\n"
php_script += "$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);\n"
php_script += "$kernel->bootstrap();\n"
php_script += "$zipData = base64_decode('" + zip_b64 + "');\n"
php_script += "file_put_contents('ebilling_pos_updates.zip', $zipData);\n"
php_script += "$zip = new ZipArchive;\n"
php_script += "if ($zip->open('ebilling_pos_updates.zip') === TRUE) {\n"
php_script += "    $extracted = $zip->extractTo('../');\n"
php_script += "    $zip->close();\n"
php_script += "    unlink('ebilling_pos_updates.zip');\n"
php_script += "    \\Illuminate\\Support\\Facades\\Artisan::call('view:clear');\n"
php_script += "    \\Illuminate\\Support\\Facades\\Artisan::call('cache:clear');\n"
php_script += "    if ($extracted) {\n"
php_script += "        echo '<h1>Nepal E-Billing Integration Deployed Successfully!</h1>';\n"
php_script += "    } else {\n"
php_script += "        echo '<h1 style=\"color: red;\">ERROR: File Extraction Failed!</h1>';\n"
php_script += "        echo '<p>Your web server does not have permission to overwrite the app files.</p>';\n"
php_script += "    }\n"
php_script += "} else {\n"
php_script += "    echo '<h1>Error opening zip</h1>';\n"
php_script += "}\n"
php_script += "?>"

with open('deploy_ebilling.php', 'w') as f:
    f.write(php_script)

print("SUCCESS: deploy_ebilling.php has been created!")
print("SUCCESS: Kept local updates zip at ebilling_pos_updates.zip")
