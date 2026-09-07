import os
import zipfile
import base64

def main():
    base_dir = os.path.dirname(os.path.abspath(__file__))
    zip_path = os.path.join(base_dir, 'complete_updates.zip')
    
    # Files to include in the zip relative to project root
    files_to_include = [
        'resources/views/components/layouts/app.blade.php',
        'app/Http/Controllers/AuthController.php',
        'routes/web.php',
        'app/Http/Middleware/TenantMiddleware.php',
        'public/icon.svg',
        'public/images/icon.svg'
    ]
    
    with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
        # 1. Add specific project files
        for rel_path in files_to_include:
            full_path = os.path.join(base_dir, rel_path)
            if os.path.exists(full_path):
                zipf.write(full_path, arcname=rel_path)
                print(f"Added file: {rel_path}")
            else:
                print(f"Warning: file {rel_path} not found")
                
        # 2. Add build folder contents recursively
        build_dir = os.path.join(base_dir, 'public', 'build')
        for root, dirs, files in os.walk(build_dir):
            for file in files:
                full_path = os.path.join(root, file)
                rel_path = os.path.relpath(full_path, base_dir)
                zipf.write(full_path, arcname=rel_path)
                print(f"Added build asset: {rel_path}")

    # Read zip and base64 encode
    with open(zip_path, 'rb') as f:
        b64_zip = base64.b64encode(f.read()).decode('utf-8')
        
    # Clean up local zip
    os.remove(zip_path)

    php_script = f"""<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$zipPath = __DIR__ . '/complete_updates.zip';
file_put_contents($zipPath, base64_decode('{b64_zip}'));

$zip = new ZipArchive;
if ($zip->open($zipPath) === TRUE) {{
    // Extract everything to the project root (../ relative to public/)
    $zip->extractTo(__DIR__ . '/../');
    $zip->close();
    unlink($zipPath);
    
    // Clear caches
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\\Contracts\\Http\\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\\Http\\Request::capture()
    );

    if (function_exists('opcache_reset')) {{
        opcache_reset();
    }}
    \\Illuminate\\Support\\Facades\\Artisan::call('config:clear');
    \\Illuminate\\Support\\Facades\\Artisan::call('route:clear');
    \\Illuminate\\Support\\Facades\\Artisan::call('view:clear');
    
    echo '<h1>Unified Deployment Successful!</h1>';
    echo '<p>All source code, layouts, favicon SVG files, and rebuilt Vite assets successfully updated.</p>';
    echo '<p>Laravel view and route caches cleared successfully.</p>';
}} else {{
    echo '<h1>Failed to extract deployment ZIP!</h1>';
}}
?>"""

    output_path = os.path.join(os.path.dirname(base_dir), 'deploy_portal_complete.php')
    with open(output_path, 'w', encoding='utf-8') as f:
        f.write(php_script)
    print(f"Generated unified deployment script at: {output_path}")

if __name__ == '__main__':
    main()
