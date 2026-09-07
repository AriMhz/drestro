import os

def main():
    base_dir = os.path.dirname(os.path.abspath(__file__))
    
    # Files map
    files = {
        'routes_content': os.path.join(base_dir, 'DrestroPOS', 'routes', 'web.php'),
        'middleware_content': os.path.join(base_dir, 'DrestroPOS', 'app', 'Http', 'Middleware', 'TenantMiddleware.php'),
        'app_layout_content': os.path.join(base_dir, 'DrestroPOS', 'resources', 'views', 'components', 'layouts', 'app.blade.php'),
        'helpers_content': os.path.join(base_dir, 'DrestroPOS', 'app', 'helpers.php'),
        'customer_manager_php': os.path.join(base_dir, 'DrestroPOS', 'app', 'Livewire', 'Admin', 'CustomerManager.php'),
        'customer_manager_blade': os.path.join(base_dir, 'DrestroPOS', 'resources', 'views', 'livewire', 'admin', 'customer-manager.blade.php'),
        'finance_manager_php': os.path.join(base_dir, 'DrestroPOS', 'app', 'Livewire', 'Admin', 'FinanceManager.php'),
        'finance_manager_blade': os.path.join(base_dir, 'DrestroPOS', 'resources', 'views', 'livewire', 'admin', 'finance-manager.blade.php'),
        'print_qr_blade': os.path.join(base_dir, 'DrestroPOS', 'resources', 'views', 'print', 'qr.blade.php'),
        'table_manager_blade': os.path.join(base_dir, 'DrestroPOS', 'resources', 'views', 'livewire', 'admin', 'table-manager.blade.php')
    }

    contents = {}
    for key, path in files.items():
        with open(path, 'r', encoding='utf-8') as f:
            contents[key] = f.read()

    php_script = f"""<?php
// Unified Deployment Script for Customer, Finance, and QR Fixes
$routes_file = __DIR__ . '/../routes/web.php';
$middleware_file = __DIR__ . '/../app/Http/Middleware/TenantMiddleware.php';
$app_layout_file = __DIR__ . '/../resources/views/components/layouts/app.blade.php';
$helpers_file = __DIR__ . '/../app/helpers.php';
$customer_php_file = __DIR__ . '/../app/Livewire/Admin/CustomerManager.php';
$customer_blade_file = __DIR__ . '/../resources/views/livewire/admin/customer-manager.blade.php';
$finance_php_file = __DIR__ . '/../app/Livewire/Admin/FinanceManager.php';
$finance_blade_file = __DIR__ . '/../resources/views/livewire/admin/finance-manager.blade.php';
$print_qr_file = __DIR__ . '/../resources/views/print/qr.blade.php';
$table_manager_file = __DIR__ . '/../resources/views/livewire/admin/table-manager.blade.php';

$routes_content = <<<'EOT'
{contents['routes_content']}
EOT;

$middleware_content = <<<'EOT'
{contents['middleware_content']}
EOT;

$app_layout_content = <<<'EOT'
{contents['app_layout_content']}
EOT;

$helpers_content = <<<'EOT'
{contents['helpers_content']}
EOT;

$customer_php = <<<'EOT'
{contents['customer_manager_php']}
EOT;

$customer_blade = <<<'EOT'
{contents['customer_manager_blade']}
EOT;

$finance_php = <<<'EOT'
{contents['finance_manager_php']}
EOT;

$finance_blade = <<<'EOT'
{contents['finance_manager_blade']}
EOT;

$print_qr = <<<'EOT'
{contents['print_qr_blade']}
EOT;

$table_manager = <<<'EOT'
{contents['table_manager_blade']}
EOT;

// Ensure directories exist
if (!is_dir(dirname($routes_file))) {{ mkdir(dirname($routes_file), 0755, true); }}
if (!is_dir(dirname($middleware_file))) {{ mkdir(dirname($middleware_file), 0755, true); }}
if (!is_dir(dirname($app_layout_file))) {{ mkdir(dirname($app_layout_file), 0755, true); }}
if (!is_dir(dirname($helpers_file))) {{ mkdir(dirname($helpers_file), 0755, true); }}
if (!is_dir(dirname($customer_php_file))) {{ mkdir(dirname($customer_php_file), 0755, true); }}
if (!is_dir(dirname($customer_blade_file))) {{ mkdir(dirname($customer_blade_file), 0755, true); }}
if (!is_dir(dirname($finance_php_file))) {{ mkdir(dirname($finance_php_file), 0755, true); }}
if (!is_dir(dirname($finance_blade_file))) {{ mkdir(dirname($finance_blade_file), 0755, true); }}
if (!is_dir(dirname($print_qr_file))) {{ mkdir(dirname($print_qr_file), 0755, true); }}
if (!is_dir(dirname($table_manager_file))) {{ mkdir(dirname($table_manager_file), 0755, true); }}

// Write files
file_put_contents($routes_file, $routes_content);
file_put_contents($middleware_file, $middleware_content);
file_put_contents($app_layout_file, $app_layout_content);
file_put_contents($helpers_file, $helpers_content);
file_put_contents($customer_php_file, $customer_php);
file_put_contents($customer_blade_file, $customer_blade);
file_put_contents($finance_php_file, $finance_php);
file_put_contents($finance_blade_file, $finance_blade);
file_put_contents($print_qr_file, $print_qr);
file_put_contents($table_manager_file, $table_manager);

// Reset Cache
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

echo "<h1>DRestro Portal Updates Deployed Successfully!</h1>";
echo "<ul>";
echo "<li>routes/web.php</li>";
echo "<li>TenantMiddleware.php</li>";
echo "<li>app.blade.php</li>";
echo "<li>helpers.php</li>";
echo "<li>CustomerManager (PHP & Blade)</li>";
echo "<li>FinanceManager (PHP & Blade)</li>";
echo "<li>print/qr.blade.php</li>";
echo "<li>table-manager.blade.php</li>";
echo "</ul>";
echo "<p>All Laravel caches and OPCache cleared successfully.</p>";
"""

    output_path = os.path.join(base_dir, 'deploy_portal_complete.php')
    with open(output_path, 'w', encoding='utf-8') as f:
        f.write(php_script)
    print(f"Generated complete deployment script at: {output_path}")

if __name__ == '__main__':
    main()
