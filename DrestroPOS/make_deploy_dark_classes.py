import os
import zipfile
import base64

# Create zip file
with zipfile.ZipFile('deploy.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    # Add public/build
    for root, dirs, files in os.walk('public/build'):
        for file in files:
            file_path = os.path.join(root, file)
            zipf.write(file_path, arcname=file_path.replace('\\', '/'))
            
    # Add resources/views
    for root, dirs, files in os.walk('resources/views'):
        for file in files:
            file_path = os.path.join(root, file)
            zipf.write(file_path, arcname=file_path.replace('\\', '/'))

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
php_script += "    echo '<h1>Dark Mode Native Classes Deployed!</h1>';\n"
php_script += "    echo '<p>All Blade templates now have native Tailwind dark mode classes!</p>';\n"
php_script += "} else {\n"
php_script += "    echo '<h1>Error extracting zip</h1>';\n"
php_script += "}\n"
php_script += "?>"

with open('deploy_dark_classes.php', 'w') as f:
    f.write(php_script)
