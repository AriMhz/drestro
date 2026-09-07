$content = [Convert]::ToBase64String([IO.File]::ReadAllBytes('app_fixed.blade.php'))
$phpCode = '<?php file_put_contents("../resources/views/components/layouts/app.blade.php", base64_decode("' + $content + '")); opcache_reset(); echo "<h1>SUCCESS! The layout has been forcefully overwritten and cache cleared. Return to your dashboard and refresh.</h1>"; ?>'
[IO.File]::WriteAllText('force_fix.php', $phpCode)
