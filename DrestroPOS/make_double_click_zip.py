import zipfile
import base64

with zipfile.ZipFile('deploy.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    zipf.write('routes/web.php', arcname='routes/web.php')

with open('deploy.zip', 'rb') as f:
    zip_b64 = base64.b64encode(f.read()).decode('utf-8')

php_script = "<?php\n"
php_script += "$zipData = base64_decode('" + zip_b64 + "');\n"
php_script += "file_put_contents('deploy_double_click_fix.zip', $zipData);\n"
php_script += "$zip = new ZipArchive;\n"
php_script += "if ($zip->open('deploy_double_click_fix.zip') === TRUE) {\n"
php_script += "    $zip->extractTo('../');\n"
php_script += "    $zip->close();\n"
php_script += "    unlink('deploy_double_click_fix.zip');\n"
php_script += "    if (file_exists(__DIR__ . '/../bootstrap/cache/routes-v7.php')) unlink(__DIR__ . '/../bootstrap/cache/routes-v7.php');\n"
php_script += "    echo '<h1>Fix Deployed!</h1>';\n"
php_script += "} else {\n"
php_script += "    echo '<h1>Error extracting zip</h1>';\n"
php_script += "}\n"
php_script += "?>"

with open('deploy_double_click_fix.php', 'w') as f:
    f.write(php_script)
