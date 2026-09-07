import os

def main():
    base_dir = os.path.dirname(os.path.abspath(__file__))
    blade_path = os.path.join(base_dir, 'DrestroPOS', 'resources', 'views', 'livewire', 'admin', 'staff-manager.blade.php')
    controller_path = os.path.join(base_dir, 'DrestroPOS', 'app', 'Livewire', 'Admin', 'StaffManager.php')
    login_path = os.path.join(base_dir, 'DrestroPOS', 'resources', 'views', 'auth', 'login.blade.php')
    auth_controller_path = os.path.join(base_dir, 'DrestroPOS', 'app', 'Http', 'Controllers', 'AuthController.php')
    web_routes_path = os.path.join(base_dir, 'DrestroPOS', 'routes', 'web.php')
    
    with open(blade_path, 'r', encoding='utf-8') as f:
        blade_content = f.read()
        
    with open(controller_path, 'r', encoding='utf-8') as f:
        controller_content = f.read()
        
    with open(login_path, 'r', encoding='utf-8') as f:
        login_content = f.read()
        
    with open(auth_controller_path, 'r', encoding='utf-8') as f:
        auth_controller_content = f.read()

    with open(web_routes_path, 'r', encoding='utf-8') as f:
        web_routes_content = f.read()
        
    php_template = f"""<?php
$blade_file = __DIR__ . '/../resources/views/livewire/admin/staff-manager.blade.php';
$controller_file = __DIR__ . '/../app/Livewire/Admin/StaffManager.php';
$login_file = __DIR__ . '/../resources/views/auth/login.blade.php';
$auth_controller_file = __DIR__ . '/../app/Http/Controllers/AuthController.php';
$web_routes_file = __DIR__ . '/../routes/web.php';

$blade_content = <<< 'EOT'
{blade_content}
EOT;

$controller_content = <<< 'EOT'
{controller_content}
EOT;

$login_content = <<< 'EOT'
{login_content}
EOT;

$auth_controller_content = <<< 'EOT'
{auth_controller_content}
EOT;

$web_routes_content = <<< 'EOT'
{web_routes_content}
EOT;

// Ensure target directories exist
@mkdir(dirname($blade_file), 0755, true);
@mkdir(dirname($controller_file), 0755, true);
@mkdir(dirname($login_file), 0755, true);
@mkdir(dirname($auth_controller_file), 0755, true);
@mkdir(dirname($web_routes_file), 0755, true);

file_put_contents($blade_file, $blade_content);
file_put_contents($controller_file, $controller_content);
file_put_contents($login_file, $login_content);
file_put_contents($auth_controller_file, $auth_controller_content);
file_put_contents($web_routes_file, $web_routes_content);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Http\\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\\Http\\Request::capture()
);

// Clear caches
\\Illuminate\\Support\\Facades\\Artisan::call('view:clear');
\\Illuminate\\Support\\Facades\\Artisan::call('cache:clear');

// Fix database records for cross-tenant demo accounts
try {{
    \\Illuminate\\Support\\Facades\\DB::table('users')->where('email', 'pacmhz2004@gmail.com')->update(['restaurant_id' => 4]);
    \\Illuminate\\Support\\Facades\\DB::table('users')->where('email', 'deepbalami729@gmail.com')->update(['restaurant_id' => 6]);
    $db_status = "Database records fixed: pacmhz2004@gmail.com set to restaurant 4, deepbalami729@gmail.com set to restaurant 6.";
}} catch (\\Exception $e) {{
    $db_status = "Database fix error: " . $e->getMessage();
}}

// Clear OPCache if enabled
if (function_exists('opcache_reset')) {{
    opcache_reset();
}}

echo "<h1>Deployment Successful!</h1>";
echo "<p>staff-manager.blade.php, StaffManager.php, login.blade.php, AuthController.php, and web.php have been updated.</p>";
echo "<p>Cache and OPCache cleared successfully!</p>";
echo "<p><b>DB Update:</b> " . $db_status . "</p>";
"""

    output_path = os.path.join(base_dir, 'DrestroPOS', 'public', 'deploy_staff_modal_update.php')
    with open(output_path, 'w', encoding='utf-8') as f:
        f.write(php_template)
    print(f"Generated deployment script at: {output_path}")

if __name__ == '__main__':
    main()
