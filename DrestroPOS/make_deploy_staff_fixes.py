import os
import zipfile
import base64

with zipfile.ZipFile('deploy.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    # Add view
    file_path = 'resources/views/livewire/admin/staff-manager.blade.php'
    zipf.write(file_path, arcname=file_path.replace('\\', '/'))
    
    # Add backend component
    file_path2 = 'app/Livewire/Admin/StaffManager.php'
    zipf.write(file_path2, arcname=file_path2.replace('\\', '/'))

with open('deploy.zip', 'rb') as f:
    zip_b64 = base64.b64encode(f.read()).decode('utf-8')

php_script = "<?php\n"
php_script += "$zipData = base64_decode('" + zip_b64 + "');\n"
php_script += "file_put_contents('deploy_staff.zip', $zipData);\n"
php_script += "$zip = new ZipArchive;\n"
php_script += "if ($zip->open('deploy_staff.zip') === TRUE) {\n"
php_script += "    $zip->extractTo('../');\n"
php_script += "    $zip->close();\n"
php_script += "    unlink('deploy_staff.zip');\n"
php_script += "    // Clear compiled views manually\n"
php_script += "    $viewsPath = __DIR__ . '/../storage/framework/views';\n"
php_script += "    $files = glob($viewsPath . '/*.php');\n"
php_script += "    foreach ($files as $file) {\n"
php_script += "        if (is_file($file)) unlink($file);\n"
php_script += "    }\n"
php_script += "    echo '<h1>Staff Modal Colors & PIN Validation Fixed!</h1>';\n"
php_script += "    echo '<p>Colors are darker and PINs are now validated for uniqueness.</p>';\n"
php_script += "} else {\n"
php_script += "    echo '<h1>Error extracting zip</h1>';\n"
php_script += "}\n"
php_script += "?>"

with open('deploy_staff_fixes.php', 'w') as f:
    f.write(php_script)
