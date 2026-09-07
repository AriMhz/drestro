import base64

with open('resources/views/components/layouts/app.blade.php', 'rb') as f:
    b64 = base64.b64encode(f.read()).decode('utf-8')

php = f"""<?php
file_put_contents('../resources/views/components/layouts/app.blade.php', base64_decode('{b64}'));
echo '<h1>Sidebar FOUC fixed!</h1>';
?>"""

with open('fix_sidebar_fouc.php', 'w') as f:
    f.write(php)
