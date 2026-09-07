import base64

with open('resources/views/components/layouts/app.blade.php', 'rb') as f:
    b64 = base64.b64encode(f.read()).decode('utf-8')

php = f"""<?php
file_put_contents('../resources/views/components/layouts/app.blade.php', base64_decode('{b64}'));
echo '<h1>Sign Out button removed!</h1>';
?>"""

with open('remove_sign_out.php', 'w') as f:
    f.write(php)
