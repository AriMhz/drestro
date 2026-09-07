import base64
import os

files = [
    'DrestroPOS/resources/views/livewire/staff/cashier-panel.blade.php',
    'DrestroPOS/resources/views/livewire/admin/staff-manager.blade.php',
    'DrestroPOS/resources/views/livewire/staff/kitchen-panel.blade.php',
    'DrestroPOS/resources/views/livewire/staff/waiter-dashboard.blade.php'
]

php_code = '<?php\n\n'
php_code += '$base_path = dirname(__DIR__);\n\n'

for f in files:
    with open(f, 'rb') as file:
        content = file.read()
    b64 = base64.b64encode(content).decode('utf-8')
    php_code += f'''
$file = $base_path . '/{f.replace("DrestroPOS/", "")}';
$content = base64_decode('{b64}');
if (file_put_contents($file, $content) !== false) {{
    echo "<p style='color:green;'>Successfully updated: $file</p>";
}} else {{
    echo "<p style='color:red;'>Failed to update: $file</p>";
}}
'''

php_code += '''
// Clear view cache
@array_map('unlink', glob($base_path . '/storage/framework/views/*.php'));
echo "<p style='color:green;'>View cache cleared!</p>";
echo "<h3>All UI fixes deployed successfully!</h3>";
echo "<p>You can now delete this file.</p>";
?>
'''

with open('deploy_ui_fixes.php', 'w') as out:
    out.write(php_code)

print('Generated deploy_ui_fixes.php')
