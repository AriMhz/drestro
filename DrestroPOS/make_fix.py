import os
import base64

files = os.listdir('resources/views/livewire/admin')
code = "<?php\n"
code += "ini_set('display_errors', 1); error_reporting(E_ALL);\n"

for f in files:
    if f.endswith('.blade.php'):
        with open('resources/views/livewire/admin/' + f, 'rb') as file:
            encoded = base64.b64encode(file.read()).decode('utf-8')
            code += f"file_put_contents('../resources/views/livewire/admin/{f}', base64_decode('{encoded}'));\n"

code += "$views = glob('../storage/framework/views/*.php');\n"
code += "if ($views !== false) { foreach($views as $v) { @unlink($v); } }\n"
code += "if (function_exists('opcache_reset')) { opcache_reset(); }\n"
code += "echo '<h1>SUCCESS! ALL admin views forcefully rewritten!</h1>';\n"
code += "?>"

with open('fix_all_views.php', 'w', encoding='utf-8') as out:
    out.write(code)
