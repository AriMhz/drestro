import os
import zipfile

def main():
    base_dir = os.path.dirname(os.path.abspath(__file__))
    
    files_to_deploy = [
        "prisma/schema.prisma",
        "src/lib/nepalEbilling.ts",
        "src/app/admin/(dashboard)/layout.tsx",
        "src/app/admin/(dashboard)/billing/page.tsx",
        "src/app/admin/(dashboard)/billing/BillingClient.tsx",
        "src/app/api/admin/orders/[id]/route.ts",
        "src/app/api/admin/orders/[id]/sync/route.ts",
        "src/app/admin/(dashboard)/orders/OrdersClient.tsx",
        "src/app/api/admin/orders/route.ts",
        "src/app/api/admin/settings/route.ts",
        "src/app/api/admin/settings/test-ebilling/route.ts"
    ]
    
    zip_path = os.path.join(base_dir, 'ebilling_updates.zip')
    
    # Create zip file preserving directory structure
    with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
        for rel_path in files_to_deploy:
            full_path = os.path.join(base_dir, rel_path)
            if os.path.exists(full_path):
                zipf.write(full_path, arcname=rel_path.replace('\\', '/'))
                print(f"Added to zip: {rel_path}")
            else:
                print(f"ERROR: File not found: {rel_path}")
                
    print("SUCCESS: ebilling_updates.zip generated with correct folder structure!")

if __name__ == '__main__':
    main()
