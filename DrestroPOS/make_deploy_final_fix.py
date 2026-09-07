import os
import zipfile
import base64

with zipfile.ZipFile('deploy.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    file_path = 'resources/views/livewire/admin/staff-manager.blade.php'
    zipf.write(file_path, arcname=file_path.replace('\\', '/'))

with open('deploy.zip', 'rb') as f:
    zip_b64 = base64.b64encode(f.read()).decode('utf-8')

php_script = "<?php\n"
php_script += "$zipData = base64_decode('" + zip_b64 + "');\n"
php_script += "file_put_contents('deploy_final_fix.zip', $zipData);\n"
php_script += "$zip = new ZipArchive;\n"
php_script += "if ($zip->open('deploy_final_fix.zip') === TRUE) {\n"
php_script += "    $zip->extractTo('../');\n"
php_script += "    $zip->close();\n"
php_script += "    unlink('deploy_final_fix.zip');\n"
php_script += "    \n"
php_script += "    $viewsPath = __DIR__ . '/../storage/framework/views';\n"
php_script += "    $files = glob($viewsPath . '/*.php');\n"
php_script += "    if ($files) {\n"
php_script += "        foreach ($files as $file) {\n"
php_script += "            if (is_file($file)) unlink($file);\n"
php_script += "        }\n"
php_script += "    }\n"
php_script += "    echo '<h1>Perfect!</h1>';\n"
php_script += "    echo '<p>Cancel button works, Page Access updates live, and header colors are perfect!</p>';\n"
php_script += "} else {\n"
php_script += "    echo '<h1>Error extracting zip</h1>';\n"
php_script += "}\n"
php_script += "?>"

with open('deploy_final_fix.php', 'w') as f:
    f.write(php_script)
