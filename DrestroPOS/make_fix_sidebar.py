import base64

with open('resources/views/components/layouts/app.blade.php', 'rb') as f:
    b64 = base64.b64encode(f.read()).decode('utf-8')

php = f"""<?php
file_put_contents('../resources/views/components/layouts/app.blade.php', base64_decode('{b64}'));
$views = glob('../storage/framework/views/*.php');
if ($views !== false) {{ foreach($views as $v) {{ @unlink($v); }} }}
echo '<h1>Sidebar links fixed!</h1>';
?>"""

with open('fix_sidebar.php', 'w') as f:
    f.write(php)
