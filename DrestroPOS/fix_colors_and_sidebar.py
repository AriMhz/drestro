import re

# Fix app.blade.php
file_path = 'resources/views/components/layouts/app.blade.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace emerald with rose in the nav block specifically (or entire file, but mass_replace already did the rest)
content = content.replace('emerald', 'rose')

# Remove the $lf checks to make the sidebar exactly match the checked boxes in the modal
content = re.sub(r" && \(\$isSuperAdmin \|\| in_array\('[a-zA-Z_]+', \$lf\)\)", "", content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

# Fix staff-manager.blade.php
file_path2 = 'resources/views/livewire/admin/staff-manager.blade.php'
with open(file_path2, 'r', encoding='utf-8') as f:
    content2 = f.read()

content2 = content2.replace('emerald', 'rose')

with open(file_path2, 'w', encoding='utf-8') as f:
    f.write(content2)

import base64
import zipfile

with zipfile.ZipFile('deploy_rose.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    zipf.write(file_path, arcname=file_path)
    zipf.write(file_path2, arcname=file_path2)

with open('deploy_rose.zip', 'rb') as f:
    zip_b64 = base64.b64encode(f.read()).decode('utf-8')

php_script = "<?php\n"
php_script += "$zipData = base64_decode('" + zip_b64 + "');\n"
php_script += "file_put_contents('deploy_rose.zip', $zipData);\n"
php_script += "$zip = new ZipArchive;\n"
php_script += "if ($zip->open('deploy_rose.zip') === TRUE) {\n"
php_script += "    $zip->extractTo('../');\n"
php_script += "    $zip->close();\n"
php_script += "    unlink('deploy_rose.zip');\n"
php_script += "    echo '<h1>Colors and Sidebar Accuracy Fixed!</h1>';\n"
php_script += "} else {\n"
php_script += "    echo '<h1>Error extracting zip</h1>';\n"
php_script += "}\n"
php_script += "?>"

with open('deploy_rose.php', 'w') as f:
    f.write(php_script)
