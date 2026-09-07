import base64
import zipfile
import os
import time
import subprocess

# Step 1: Run production build to compile latest Tailwind v4 classes and design color fixes
print("Running production build...")
try:
    result = subprocess.run(["npm", "run", "build"], capture_output=True, text=True, check=True, shell=True)
    print("Build output:")
    print(result.stdout)
except Exception as e:
    print(f"Error compiling assets: {e}")
    if hasattr(e, 'stderr') and e.stderr:
        print(e.stderr)
    print("Will attempt packaging anyway using existing build files...")

files_to_deploy = [
    'resources/views/components/layouts/app.blade.php',
    'resources/views/livewire/staff/waiter-order-taking.blade.php',
    'resources/views/livewire/admin/settings.blade.php',
    'app/Http/Controllers/AuthController.php',
    'app/Livewire/Admin/Settings.php',
    'resources/views/livewire/admin/menu-manager.blade.php',
    'resources/views/livewire/admin/table-manager.blade.php',
    'resources/views/print/qr.blade.php',
    'routes/web.php',
    'app/Livewire/Auth/OtpLogin.php',
    'app/Livewire/Auth/PinLogin.php',
    'app/Livewire/Auth/ScreenLock.php',
    'resources/views/livewire/auth/otp-login.blade.php',
    'resources/views/livewire/auth/pin-login.blade.php',
    'resources/views/livewire/auth/screen-lock.blade.php',
    'resources/views/livewire/admin/room-manager.blade.php',
    'resources/views/livewire/admin/inventory-manager.blade.php',
    'resources/views/livewire/admin/reports-manager.blade.php',
    'resources/views/livewire/admin/dashboard.blade.php',
    'resources/views/livewire/staff/cashier-panel.blade.php',
    'resources/views/livewire/staff/hotel-cashier.blade.php',
    'resources/views/livewire/staff/waiter-dashboard.blade.php',
    'resources/views/livewire/staff/room-service.blade.php',
    'resources/views/livewire/staff/kitchen-panel.blade.php',
    'resources/views/livewire/customer/digital-menu.blade.php',
    'resources/views/auth/invite.blade.php',
    'resources/views/livewire/admin/support-tickets.blade.php'
]

# Verify files exist and show sizes
print("Verifying files to package:")
for f_path in files_to_deploy:
    if os.path.exists(f_path):
        size = os.path.getsize(f_path)
        mtime = time.ctime(os.path.getmtime(f_path))
        print(f"  {f_path}: {size} bytes, modified {mtime}")
    else:
        print(f"  ERROR: {f_path} not found!")

# Zip them up along with public/build
zip_filename = 'deploy_ui.zip'
print(f"Creating {zip_filename}...")
with zipfile.ZipFile(zip_filename, 'w', zipfile.ZIP_DEFLATED) as zipf:
    # Add files
    for f_path in files_to_deploy:
        zipf.write(f_path, arcname=f_path)
        print(f"  Added view: {f_path}")
    
    # Add public/build files
    build_count = 0
    for root, dirs, files in os.walk('public/build'):
        for file in files:
            file_path = os.path.join(root, file)
            arcname = file_path.replace('\\', '/')
            zipf.write(file_path, arcname=arcname)
            build_count += 1
    print(f"  Added {build_count} build assets from public/build")

# Read the zip file and encode to base64
with open(zip_filename, 'rb') as f:
    zip_b64 = base64.b64encode(f.read()).decode('utf-8')

# Delete local zip file after encoding
try:
    os.remove(zip_filename)
except Exception as e:
    pass

php_script = """<?php
// Deploy UI Fix v3 (Views + Assets) - """ + time.strftime('%Y-%m-%d %H:%M:%S') + """
$zipData = base64_decode('""" + zip_b64 + """');
file_put_contents('deploy_ui_fix.zip', $zipData);
$zip = new ZipArchive;
$results = [];
if ($zip->open('deploy_ui_fix.zip') === TRUE) {
    $zip->extractTo('../');
    $zip->close();
    unlink('deploy_ui_fix.zip');
    
    // Clear ALL compiled blade views
    $viewsPath = realpath(__DIR__ . '/../storage/framework/views');
    $count = 0;
    if ($viewsPath && is_dir($viewsPath)) {
        $files = glob($viewsPath . '/*.php');
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) { unlink($file); $count++; }
            }
        }
    }
    $results[] = "Cleared $count cached view files from: $viewsPath";
    
    // Reset OPcache if available
    if (function_exists('opcache_reset')) {
        $reset = opcache_reset();
        $results[] = "OPcache reset status: " . ($reset ? "SUCCESS" : "FAILED/NO CHANGES");
    } else {
        $results[] = "OPcache not available (skipped)";
    }
    
    // Verify deployed view files
    $deployedFiles = [
        'resources/views/components/layouts/app.blade.php',
        'resources/views/livewire/staff/waiter-order-taking.blade.php',
        'resources/views/livewire/admin/settings.blade.php',
        'app/Http/Controllers/AuthController.php',
        'app/Livewire/Admin/Settings.php',
        'resources/views/livewire/admin/menu-manager.blade.php',
        'resources/views/livewire/admin/table-manager.blade.php',
        'resources/views/print/qr.blade.php',
        'routes/web.php',
        'app/Livewire/Auth/OtpLogin.php',
        'app/Livewire/Auth/PinLogin.php',
        'app/Livewire/Auth/ScreenLock.php',
        'resources/views/livewire/auth/otp-login.blade.php',
        'resources/views/livewire/auth/pin-login.blade.php',
        'resources/views/livewire/auth/screen-lock.blade.php',
        'resources/views/livewire/admin/room-manager.blade.php',
        'resources/views/livewire/admin/inventory-manager.blade.php',
        'resources/views/livewire/admin/reports-manager.blade.php',
        'resources/views/livewire/admin/dashboard.blade.php',
        'resources/views/livewire/staff/cashier-panel.blade.php',
        'resources/views/livewire/staff/hotel-cashier.blade.php',
        'resources/views/livewire/staff/waiter-dashboard.blade.php',
        'resources/views/livewire/staff/room-service.blade.php',
        'resources/views/livewire/staff/kitchen-panel.blade.php',
        'resources/views/livewire/customer/digital-menu.blade.php',
        'resources/views/auth/invite.blade.php',
        'resources/views/livewire/admin/support-tickets.blade.php',
        'public/build/manifest.json'
    ];
    foreach ($deployedFiles as $df) {
        $fullPath = realpath(__DIR__ . '/../' . $df);
        if ($fullPath && file_exists($fullPath)) {
            $size = filesize($fullPath);
            $mtime = date('Y-m-d H:i:s', filemtime($fullPath));
            $results[] = "OK: $df ($size bytes, modified $mtime)";
        } else {
            $results[] = "ERROR: $df NOT FOUND!";
        }
    }
    
    // Find and verify active compiled CSS from manifest
    $manifestPath = realpath(__DIR__ . '/../public/build/manifest.json');
    if ($manifestPath && file_exists($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true);
        if (isset($manifest['resources/css/app.css']['file'])) {
            $cssFile = 'public/build/' . $manifest['resources/css/app.css']['file'];
            $cssFullPath = realpath(__DIR__ . '/../' . $cssFile);
            if ($cssFullPath && file_exists($cssFullPath)) {
                $size = filesize($cssFullPath);
                $mtime = date('Y-m-d H:i:s', filemtime($cssFullPath));
                $results[] = "OK: Active Compiled CSS is at $cssFile ($size bytes, modified $mtime)";
            } else {
                $results[] = "ERROR: Active CSS file at $cssFile NOT found!";
            }
        } else {
            $results[] = "ERROR: app.css key not found in manifest.json!";
        }
    } else {
        $results[] = "ERROR: manifest.json is missing!";
    }
    
    echo '<h1>UI Fix & Production CSS Deployed Successfully!</h1>';
    echo '<ul>';
    foreach ($results as $r) {
        echo "<li>$r</li>";
    }
    echo '</ul>';
    echo '<p><strong>IMPORTANT: Now please hard-refresh the page (Ctrl+Shift+R or clear browser/device cache) to see the changes.</strong></p>';
} else {
    echo '<h1>Error extracting zip</h1>';
}
?>"""

# Save to public/deploy_ui_fix.php and deploy_ui_fix_v3.php
with open('public/deploy_ui_fix.php', 'w', encoding='utf-8') as f:
    f.write(php_script)

with open('public/deploy_ui_fix_v3.php', 'w', encoding='utf-8') as f:
    f.write(php_script)

print("Created public/deploy_ui_fix.php and public/deploy_ui_fix_v3.php successfully!")

