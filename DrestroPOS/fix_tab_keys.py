import re

file_path = 'resources/views/components/layouts/app.blade.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the mismatched keys
replacements = {
    "'hotel_rooms'": "'hotel_room_manager'",
    "'room_service'": "'take_room_service'",
    "'hotel_cashier'": "'hotel_reception'",
    "'cashier'": "'cashier_panel'",
    "'waiter_panel'": "'waiter_dashboard'",
    "'kitchen'": "'kitchen_display'",
    "'bar'": "'bar_display'"
}

for old_key, new_key in replacements.items():
    content = content.replace(f"$canAccess({old_key})", f"$canAccess({new_key})")

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

import base64
import zipfile

with zipfile.ZipFile('deploy_keys.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    zipf.write(file_path, arcname=file_path)

with open('deploy_keys.zip', 'rb') as f:
    zip_b64 = base64.b64encode(f.read()).decode('utf-8')

php_script = "<?php\n"
php_script += "$zipData = base64_decode('" + zip_b64 + "');\n"
php_script += "file_put_contents('deploy_keys.zip', $zipData);\n"
php_script += "$zip = new ZipArchive;\n"
php_script += "if ($zip->open('deploy_keys.zip') === TRUE) {\n"
php_script += "    $zip->extractTo('../');\n"
php_script += "    $zip->close();\n"
php_script += "    unlink('deploy_keys.zip');\n"
php_script += "    echo '<h1>Sidebar Sync Bug Fixed!</h1>';\n"
php_script += "} else {\n"
php_script += "    echo '<h1>Error extracting zip</h1>';\n"
php_script += "}\n"
php_script += "?>"

with open('deploy_keys.php', 'w') as f:
    f.write(php_script)
