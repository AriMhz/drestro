import os
import zipfile
import base64

# Create zip file
with zipfile.ZipFile('deploy.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    # Add app.blade.php
    file_path = 'resources/views/components/layouts/app.blade.php'
    if os.path.exists(file_path):
        zipf.write(file_path, arcname=file_path.replace('\\', '/'))
    else:
        print(f"Error: {file_path} not found")

# Read the zip file
with open('deploy.zip', 'rb') as f:
    zip_b64 = base64.b64encode(f.read()).decode('utf-8')

php_script = "<?php\n"
php_script += "$zipData = base64_decode('" + zip_b64 + "');\n"
php_script += "file_put_contents('deploy.zip', $zipData);\n"
php_script += "$zip = new ZipArchive;\n"
php_script += "if ($zip->open('deploy.zip') === TRUE) {\n"
php_script += "    // Extract everything to the parent directory where public/ is located\n"
php_script += "    $zip->extractTo('../');\n"
php_script += "    $zip->close();\n"
php_script += "    unlink('deploy.zip');\n"
php_script += "    \\Illuminate\\Support\\Facades\\Artisan::call('view:clear');\n"
php_script += "    echo '<h1>Layout Fixed!</h1>';\n"
php_script += "    echo '<p>The javascript layout conflicts have been resolved.</p>';\n"
php_script += "} else {\n"
php_script += "    echo '<h1>Error extracting zip</h1>';\n"
php_script += "}\n"
php_script += "?>"

with open('deploy_layout_fix.php', 'w') as f:
    f.write(php_script)
