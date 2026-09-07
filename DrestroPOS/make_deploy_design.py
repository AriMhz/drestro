import os
import zipfile
import base64

# Create zip file of public/build
with zipfile.ZipFile('build.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    for root, dirs, files in os.walk('public/build'):
        for file in files:
            file_path = os.path.join(root, file)
            # Add file to zip with relative path starting from build/
            arcname = os.path.relpath(file_path, 'public')
            zipf.write(file_path, arcname)

# Read zip and base64 encode
with open('build.zip', 'rb') as f:
    b64_zip = base64.b64encode(f.read()).decode('utf-8')

php = f"""<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
$zipPath = __DIR__ . '/build.zip';
file_put_contents($zipPath, base64_decode('{b64_zip}'));

$zip = new ZipArchive;
if ($zip->open($zipPath) === TRUE) {{
    // Extract everything to public folder (it contains build/ folder inside)
    $zip->extractTo(__DIR__);
    $zip->close();
    unlink($zipPath);
    echo '<h1>Design completely fixed! New CSS successfully deployed.</h1>';
}} else {{
    echo '<h1>Failed to extract zip!</h1>';
}}
?>"""

with open('deploy_design.php', 'w') as f:
    f.write(php)
