import os
import zipfile
import base64

# Ensure the zip file is created
with zipfile.ZipFile('deploy.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    # We want to deploy the public/vendor folder
    for root, dirs, files in os.walk('public/vendor'):
        for file in files:
            file_path = os.path.join(root, file)
            # In the zip, we want it to extract into 'vendor/...' when extracted in the public folder.
            # So the arcname should be just the part after 'public/'
            arc_name = file_path.replace('\\', '/').replace('public/', '')
            zipf.write(file_path, arcname=arc_name)

# Read the zip file
with open('deploy.zip', 'rb') as f:
    zip_b64 = base64.b64encode(f.read()).decode('utf-8')

php_script = "<?php\n"
php_script += "$zipData = base64_decode('" + zip_b64 + "');\n"
php_script += "file_put_contents('deploy_livewire.zip', $zipData);\n"
php_script += "$zip = new ZipArchive;\n"
php_script += "if ($zip->open('deploy_livewire.zip') === TRUE) {\n"
php_script += "    // Extract directly into the current directory (which is public/)\n"
php_script += "    $zip->extractTo(__DIR__);\n"
php_script += "    $zip->close();\n"
php_script += "    unlink('deploy_livewire.zip');\n"
php_script += "    echo '<h1>Livewire Assets Fixed!</h1>';\n"
php_script += "    echo '<p>The missing livewire.js file has been physically installed.</p>';\n"
php_script += "} else {\n"
php_script += "    echo '<h1>Error extracting zip</h1>';\n"
php_script += "}\n"
php_script += "?>"

with open('deploy_livewire_fix.php', 'w') as f:
    f.write(php_script)
