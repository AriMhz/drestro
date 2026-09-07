import os
import base64

# Read the local dashboard.blade.php which has been fixed
# We use relative path because the script will run in the DrestroPOS directory
with open('resources/views/livewire/admin/dashboard.blade.php', 'rb') as f:
    blade_content_b64 = base64.b64encode(f.read()).decode('utf-8')

php_script = f"""<?php
$bladeContent = base64_decode('{blade_content_b64}');
$targetPath = __DIR__ . '/../resources/views/livewire/admin/dashboard.blade.php';

// Ensure the directory exists
$dir = dirname($targetPath);
if (!is_dir($dir)) {{
    mkdir($dir, 0755, true);
}}

if (file_put_contents($targetPath, $bladeContent)) {{
    echo '<h1>Dashboard Mobile Title Fix Deployed!</h1>';
    
    // Manually clear compiled views cache
    $viewsPath = __DIR__ . '/../storage/framework/views';
    $cleared = 0;
    if (is_dir($viewsPath)) {{
        $files = glob($viewsPath . '/*.php');
        foreach ($files as $file) {{
            if (is_file($file)) {{
                unlink($file);
                $cleared++;
            }}
        }}
    }}
    echo '<p>Cleared ' . $cleared . ' compiled view files.</p>';
    echo '<p>Please refresh the dashboard page now!</p>';
}} else {{
    echo '<h1>Error: Could not write to dashboard.blade.php. Check permissions.</h1>';
}}
?>"""

# Determine path to write the php script relative to this script's directory
script_dir = os.path.dirname(os.path.abspath(__file__))
output_path = os.path.join(script_dir, 'deploy_dashboard_mobile_fix.php')

with open(output_path, 'w') as f:
    f.write(php_script)

print(f"Generated {output_path} successfully!")
