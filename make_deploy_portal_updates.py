import os

def main():
    base_dir = os.path.dirname(os.path.abspath(__file__))
    
    # Path to local updated files in the repo
    auth_controller_path = os.path.join(base_dir, 'DrestroPOS', 'app', 'Http', 'Controllers', 'AuthController.php')
    web_routes_path = os.path.join(base_dir, 'DrestroPOS', 'routes', 'web.php')
    middleware_path = os.path.join(base_dir, 'DrestroPOS', 'app', 'Http', 'Middleware', 'TenantMiddleware.php')
    app_layout_path = os.path.join(base_dir, 'DrestroPOS', 'resources', 'views', 'components', 'layouts', 'app.blade.php')
    icon_path = os.path.join(base_dir, 'DrestroPOS', 'public', 'icon.svg')

    # Read their updated contents
    with open(auth_controller_path, 'r', encoding='utf-8') as f:
        auth_controller_content = f.read()

    with open(web_routes_path, 'r', encoding='utf-8') as f:
        web_routes_content = f.read()

    with open(middleware_path, 'r', encoding='utf-8') as f:
        middleware_content = f.read()

    with open(app_layout_path, 'r', encoding='utf-8') as f:
        app_layout_content = f.read()

    with open(icon_path, 'r', encoding='utf-8') as f:
        icon_content = f.read()

    # Generate deploy PHP file contents
    php_template = f"""<?php
$auth_controller_file = __DIR__ . '/../app/Http/Controllers/AuthController.php';
$routes_file = __DIR__ . '/../routes/web.php';
$middleware_file = __DIR__ . '/../app/Http/Middleware/TenantMiddleware.php';
$app_layout_file = __DIR__ . '/../resources/views/components/layouts/app.blade.php';
$icon_file = __DIR__ . '/icon.svg';
$images_icon_file = __DIR__ . '/images/icon.svg';

$auth_controller_content = <<<'EOT'
{auth_controller_content}
EOT;

$routes_content = <<<'EOT'
{web_routes_content}
EOT;

$middleware_content = <<<'EOT'
{middleware_content}
EOT;

$app_layout_content = <<<'EOT'
{app_layout_content}
EOT;

$icon_content = <<<'EOT'
{icon_content}
EOT;

// Ensure target directories exist
if (!is_dir(dirname($auth_controller_file))) {{ mkdir(dirname($auth_controller_file), 0755, true); }}
if (!is_dir(dirname($routes_file))) {{ mkdir(dirname($routes_file), 0755, true); }}
if (!is_dir(dirname($middleware_file))) {{ mkdir(dirname($middleware_file), 0755, true); }}
if (!is_dir(dirname($app_layout_file))) {{ mkdir(dirname($app_layout_file), 0755, true); }}
if (!is_dir(dirname($images_icon_file))) {{ mkdir(dirname($images_icon_file), 0755, true); }}

file_put_contents($auth_controller_file, $auth_controller_content);
file_put_contents($routes_file, $routes_content);
file_put_contents($middleware_file, $middleware_content);
file_put_contents($app_layout_file, $app_layout_content);
file_put_contents($icon_file, $icon_content);
file_put_contents($images_icon_file, $icon_content);

// Clean views and config caches
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Http\\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\\Http\\Request::capture()
);

if (function_exists('opcache_reset')) {{ opcache_reset(); }}
\\Illuminate\\Support\\Facades\\Artisan::call('config:clear');
\\Illuminate\\Support\\Facades\\Artisan::call('route:clear');
\\Illuminate\\Support\\Facades\\Artisan::call('view:clear');

echo "<h1>Deployment Successful!</h1>";
echo "<p>All files deployed successfully:</p>";
echo "<ul>";
echo "<li>AuthController.php</li>";
echo "<li>routes/web.php</li>";
echo "<li>TenantMiddleware.php</li>";
echo "<li>app.blade.php</li>";
echo "<li>public/icon.svg</li>";
echo "<li>public/images/icon.svg</li>";
echo "</ul>";
echo "<p>Cache, Route Cache, and View Cache cleared.</p>";
echo "<p>OPCache reset successfully.</p>";
"""

    output_path = os.path.join(base_dir, 'deploy_portal_updates.php')
    with open(output_path, 'w', encoding='utf-8') as f:
        f.write(php_template)
    print(f"Generated unified deployment script at: {output_path}")

if __name__ == '__main__':
    main()
