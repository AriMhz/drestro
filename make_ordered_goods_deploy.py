import base64
import os

files = [
    'DrestroPOS/app/Livewire/Admin/OrderedGoods.php',
    'DrestroPOS/resources/views/livewire/admin/ordered-goods.blade.php',
    'DrestroPOS/routes/web.php',
    'DrestroPOS/resources/views/components/layouts/app.blade.php'
]

php_script = "<?php\n"
php_script += "$base_path = dirname(__DIR__);\n"

for f in files:
    full_path = os.path.join(os.getcwd(), f)
    with open(full_path, 'rb') as file:
        content = file.read()
        b64 = base64.b64encode(content).decode('utf-8')
        
        # Remove 'DrestroPOS/' from the beginning of the path for the server
        server_path = f.replace('DrestroPOS/', '')
        
        # Ensure directories exist
        dirname = os.path.dirname(server_path)
        php_script += f"@mkdir($base_path . '/{dirname}', 0755, true);\n"
        
        php_script += f"$file = $base_path . '/{server_path}';\n"
        php_script += f"$content = base64_decode('{b64}');\n"
        php_script += f"file_put_contents($file, $content);\n"
        php_script += f"echo \"<p style='color:green;'>Successfully updated: $file</p>\";\n"

php_script += "\n// Clear view cache\n"
php_script += "@array_map('unlink', glob($base_path . '/storage/framework/views/*.php'));\n"
php_script += "echo \"<p style='color:green;'>View cache cleared!</p>\";\n"

php_script += "echo \"<h3>Ordered Goods feature deployed successfully!</h3>\";\n"
php_script += "echo \"<p>You can now delete this file.</p>\";\n"

with open('deploy_ordered_goods.php', 'w') as out:
    out.write(php_script)
