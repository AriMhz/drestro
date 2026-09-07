import os
import zipfile
import base64

def main():
    base_dir = os.path.dirname(os.path.abspath(__file__))
    
    files_to_deploy = [
        "src/lib/nepalEbilling.ts",
        "src/lib/nepalEbillingApi.ts",
        "src/app/api/admin/settings/route.ts",
        "src/app/api/admin/settings/test-ebilling/route.ts",
        "src/app/admin/(dashboard)/layout.tsx",
        "src/app/admin/(dashboard)/billing/page.tsx",
        "src/app/admin/(dashboard)/billing/BillingClient.tsx",
        "src/app/api/admin/orders/[id]/route.ts",
        "src/app/api/admin/orders/[id]/sync/route.ts",
        "src/app/admin/(dashboard)/orders/OrdersClient.tsx"
    ]
    
    zip_filename = 'ebilling_updates.zip'
    zip_path = os.path.join(base_dir, zip_filename)
    
    # Create zip file
    with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
        for rel_path in files_to_deploy:
            full_path = os.path.join(base_dir, rel_path)
            if os.path.exists(full_path):
                # Ensure forward slashes in zip
                zipf.write(full_path, arcname=rel_path.replace('\\', '/'))
                print(f"Added: {rel_path}")
            else:
                print(f"ERROR: File not found: {rel_path}")
                
    # Read zip and encode in base64
    with open(zip_path, 'rb') as f:
        zip_b64 = base64.b64encode(f.read()).decode('utf-8')
        
    php_script = f"""<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$zipData = base64_decode('{zip_b64}');
$zipPath = __DIR__ . '/ebilling_updates.zip';
file_put_contents($zipPath, $zipData);

$zip = new ZipArchive;
if ($zip->open($zipPath) === TRUE) {{
    // Extract to the root folder (one level up from public/)
    $extracted = $zip->extractTo(__DIR__ . '/../');
    $zip->close();
    unlink($zipPath);
    
    if ($extracted) {{
        echo "<h1>Files Extracted Successfully!</h1>";
        
        // Execute prisma migration and client generation
        echo "<h2>Running Database Updates and Builds...</h2>";
        
        $output = [];
        $return_var = 0;
        
        echo "<pre>";
        
        // Run Prisma generate
        echo "Running: npx prisma generate\\n";
        exec("npx prisma generate 2>&1", $output, $return_var);
        echo implode("\\n", $output) . "\\n";
        echo "Return code: $return_var\\n\\n";
        
        // Run Prisma db push
        $output = [];
        echo "Running: npx prisma db push\\n";
        exec("npx prisma db push --accept-data-loss 2>&1", $output, $return_var);
        echo implode("\\n", $output) . "\\n";
        echo "Return code: $return_var\\n\\n";
        
        // Run Next.js build
        $output = [];
        echo "Running: npm run build\\n";
        exec("npm run build 2>&1", $output, $return_var);
        echo implode("\\n", $output) . "\\n";
        echo "Return code: $return_var\\n\\n";
        
        echo "</pre>";
        echo "<h3>All actions complete! Now restart your Next.js application using SSH (e.g. pm2 restart all).</h3>";
    }} else {{
        echo "<h1 style='color: red;'>ERROR: Extraction Failed!</h1>";
    }}
}} else {{
    echo "<h1 style='color: red;'>ERROR: Could not open ZIP archive!</h1>";
}}
?>"""

    output_path = os.path.join(base_dir, 'public', 'deploy_portal_ebilling.php')
    with open(output_path, 'w', encoding='utf-8') as f:
        f.write(php_script)
        
    print(f"SUCCESS: Generated deployment script at {output_path}")
    
    # Do not delete local zip so user can scp it directly
    print(f"SUCCESS: Kept local updates zip at {zip_path}")

if __name__ == '__main__':
    main()
