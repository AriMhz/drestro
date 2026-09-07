import os
import base64

# Read the local app.blade.php which has been fixed
with open('resources/views/components/layouts/app.blade.php', 'rb') as f:
    blade_content_b64 = base64.b64encode(f.read()).decode('utf-8')

php_script = f"""<?php
$bladeContent = base64_decode('{blade_content_b64}');
$targetPath = __DIR__ . '/../resources/views/components/layouts/app.blade.php';

if (file_put_contents($targetPath, $bladeContent)) {{
    echo '<h1>Layout Fixed Successfully!</h1>';
    
    // Manually clear compiled views cache
    $viewsPath = __DIR__ . '/../storage/framework/views';
    $files = glob($viewsPath . '/*.php');
    $cleared = 0;
    foreach ($files as $file) {{
        if (is_file($file)) {{
            unlink($file);
            $cleared++;
        }}
    }}
    echo '<p>Cleared ' . $cleared . ' compiled view files.</p>';
    echo '<p>Please hard refresh the Staff page now!</p>';
}} else {{
    echo '<h1>Error: Could not write to app.blade.php. Check permissions.</h1>';
}}
?>"""

with open('deploy_layout_fix2.php', 'w') as f:
    f.write(php_script)
