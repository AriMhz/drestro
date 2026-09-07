import os

base_dir = os.path.dirname(os.path.abspath(__file__))

migration_file = 'DrestroPOS/database/migrations/2026_06_04_100000_add_otp_to_users_table.php'
service_file = 'DrestroPOS/app/Services/AakashSmsService.php'
controller_file = 'DrestroPOS/app/Livewire/Auth/OtpLogin.php'
view_file = 'DrestroPOS/resources/views/livewire/auth/otp-login.blade.php'
login_file = 'DrestroPOS/resources/views/auth/login.blade.php'
routes_file = 'DrestroPOS/routes/web.php'

with open(migration_file, 'r', encoding='utf-8') as f:
    migration_content = f.read()

with open(service_file, 'r', encoding='utf-8') as f:
    service_content = f.read()

with open(controller_file, 'r', encoding='utf-8') as f:
    controller_content = f.read()

with open(view_file, 'r', encoding='utf-8') as f:
    view_content = f.read()

with open(login_file, 'r', encoding='utf-8') as f:
    login_content = f.read()

with open(routes_file, 'r', encoding='utf-8') as f:
    routes_content = f.read()

php_template = f"""<?php
$target_migration = __DIR__ . '/../database/migrations/2026_06_04_100000_add_otp_to_users_table.php';
$target_service = __DIR__ . '/../app/Services/AakashSmsService.php';
$target_controller = __DIR__ . '/../app/Livewire/Auth/OtpLogin.php';
$target_view = __DIR__ . '/../resources/views/livewire/auth/otp-login.blade.php';
$target_login = __DIR__ . '/../resources/views/auth/login.blade.php';
$target_routes = __DIR__ . '/../routes/web.php';

$migration_content = <<< 'EOT'
{migration_content}
EOT;

$service_content = <<< 'EOT'
{service_content}
EOT;

$controller_content = <<< 'EOT'
{controller_content}
EOT;

$view_content = <<< 'EOT'
{view_content}
EOT;

$login_content = <<< 'EOT'
{login_content}
EOT;

$routes_content = <<< 'EOT'
{routes_content}
EOT;

// Create parent directories if they don't exist
@mkdir(dirname($target_migration), 0755, true);
@mkdir(dirname($target_service), 0755, true);
@mkdir(dirname($target_controller), 0755, true);
@mkdir(dirname($target_view), 0755, true);
@mkdir(dirname($target_login), 0755, true);
@mkdir(dirname($target_routes), 0755, true);

// Write files
file_put_contents($target_migration, $migration_content);
file_put_contents($target_service, $service_content);
file_put_contents($target_controller, $controller_content);
file_put_contents($target_view, $view_content);
file_put_contents($target_login, $login_content);
file_put_contents($target_routes, $routes_content);

// Bootstrap Laravel and Run Migrations
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Http\\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\\Http\\Request::capture()
);

try {{
    \\Illuminate\\Support\\Facades\\Artisan::call('migrate', ['--force' => true]);
    $migration_status = "Database migrated successfully!";
}} catch (\\Exception $e) {{
    $migration_status = "Migration error: " . $e->getMessage();
}}

// Clear caches
\\Illuminate\\Support\\Facades\\Artisan::call('view:clear');
\\Illuminate\\Support\\Facades\\Artisan::call('cache:clear');

if (function_exists('opcache_reset')) {{
    opcache_reset();
}}

echo "<h1>DRestro POS SMS Login Deployment Successful!</h1>";
echo "<p>Migration and all files written.</p>";
echo "<p><b>Migration Status:</b> " . $migration_status . "</p>";
echo "<p>Cache and OPCache cleared successfully!</p>";
"""

output_path = 'DrestroPOS/public/deploy_sms_login.php'
with open(output_path, 'w', encoding='utf-8') as f:
    f.write(php_template)

print("Created deploy_sms_login.php!")
