$content = Get-Content 'app_fixed.blade.php' -Raw
$content += "</html>"
[IO.File]::WriteAllText('app_fixed_html.blade.php', $content)
$base64 = [Convert]::ToBase64String([IO.File]::ReadAllBytes('app_fixed_html.blade.php'))
$phpCode = '<?php file_put_contents("../resources/views/components/layouts/app.blade.php", base64_decode("' + $base64 + '")); opcache_reset(); echo "<h1>SUCCESS! The missing HTML tag was added, layout forcefully overwritten, and cache cleared. Return to your dashboard and refresh.</h1>"; ?>'
[IO.File]::WriteAllText('force_fix_html.php', $phpCode)
