import os
import zipfile
import base64

with zipfile.ZipFile('deploy.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    # Add resources/views
    for root, dirs, files in os.walk('resources/views/livewire/admin'):
        for file in files:
            file_path = os.path.join(root, file)
            zipf.write(file_path, arcname=file_path.replace('\\', '/'))
            
    # Add app (Livewire component)
    for root, dirs, files in os.walk('app/Livewire/Admin'):
        for file in files:
            file_path = os.path.join(root, file)
            if file in ['StaffManager.php', 'Settings.php']:
                zipf.write(file_path, arcname=file_path.replace('\\', '/'))

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
php_script += "        echo '<h1>Settings & Staff Updates Deployed Successfully!</h1>';\n"
php_script += "    } else {\n"
php_script += "        echo '<h1 style=\"color: red;\">ERROR: File Extraction Failed!</h1>';\n"
php_script += "        echo '<p>Your web server does not have permission to overwrite the app files.</p>';\n"
php_script += "    }\n"
php_script += "} else {\n"
php_script += "    echo '<h1>Error opening zip</h1>';\n"
php_script += "}\n"
php_script += "?>"

with open('deploy_fixes3.php', 'w') as f:
    f.write(php_script)
