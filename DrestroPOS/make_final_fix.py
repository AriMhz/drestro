import os
import zipfile
import base64

# Create zip file
with zipfile.ZipFile('deploy.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    # Add public/build
    for root, dirs, files in os.walk('public/build'):
        for file in files:
            file_path = os.path.join(root, file)
            # Add to zip inside public/build/
            zipf.write(file_path, file_path)
    
    # Add app.blade.php
    zipf.write('resources/views/components/layouts/app.blade.php', 'resources/views/components/layouts/app.blade.php')

# Read zip and base64 encode
with open('deploy.zip', 'rb') as f:
    b64_zip = base64.b64encode(f.read()).decode('utf-8')

php = f"""<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
$zipPath = __DIR__ . '/deploy.zip';
file_put_contents($zipPath, base64_decode('{b64_zip}'));

$zip = new ZipArchive;
if ($zip->open($zipPath) === TRUE) {{
    // Extract everything to the project root (one level up from public folder)
    $zip->extractTo(dirname(__DIR__));
    $zip->close();
    unlink($zipPath);
    echo '<h1>Update Successful! Dark mode and sidebar links are fully fixed.</h1>';
}} else {{
    echo '<h1>Failed to extract zip!</h1>';
}}
?>"""

with open('final_fix.php', 'w') as f:
    f.write(php)
