import base64

with open('resources/views/components/layouts/app.blade.php', 'rb') as f:
    b64 = base64.b64encode(f.read()).decode('utf-8')

php = f"""<?php
file_put_contents('../resources/views/components/layouts/app.blade.php', base64_decode('{b64}'));
echo '<h1>Vanilla JS Sidebar completely fixed!</h1>';
?>"""

with open('deploy_vanilla_sidebar.php', 'w') as f:
    f.write(php)
