import os
import zipfile

def main():
    base_dir = os.path.dirname(os.path.abspath(__file__))
    
    files_to_deploy = [
        "prisma/schema.prisma",
        "auth.ts",
        "src/lib/auth.ts",
        "src/app/api/admin/login/route.ts",
        "src/app/api/license/verify/route.ts",
        "src/app/api/admin/staff/route.ts",
        "src/app/api/auth/register/route.ts",
        "src/app/admin/login/page.tsx",
        "src/app/login/page.tsx",
        "src/app/register/page.tsx",
        "src/app/admin/(dashboard)/layout.tsx",
        "src/app/admin/(dashboard)/clients/ClientsClient.tsx",
        "src/app/admin/(dashboard)/staff/page.tsx",
        "src/components/StaffPortalLayout.tsx",
        "src/app/admin/(dashboard)/pricing/ComboForm.tsx",
        "src/app/admin/(dashboard)/pricing/PricingAdminClient.tsx",
        "src/app/admin/(dashboard)/pricing/comparison/page.tsx",
        "src/app/admin/(dashboard)/contact/ContactClient.tsx",
        "src/app/admin/(dashboard)/billing/BillingClient.tsx",
        "src/app/admin/(dashboard)/hardware/HardwareForm.tsx",
        "src/app/admin/(dashboard)/website/WebsiteClient.tsx",
        "src/app/admin/(dashboard)/faq/page.tsx",
        "src/app/sales/inbox/page.tsx",
        "src/app/sales/orders/page.tsx",
        "src/app/sales/billing/page.tsx",
        "src/app/sales/support/page.tsx",
        "src/app/support/inbox/page.tsx",
        "src/app/support/orders/page.tsx",
        "src/app/support/billing/page.tsx",
        "src/app/support/support/page.tsx",
        "src/app/marketing/inbox/page.tsx",
        "src/app/marketing/orders/page.tsx",
        "src/app/marketing/billing/page.tsx",
        "src/app/marketing/support/page.tsx",
        "src/components/layout/Footer.tsx",
        "public/downloads/drestro.apk",
        "src/app/dashboard/page.tsx",
        "src/components/layout/ClientLayoutWrapper.tsx",
        "next.config.mjs"
    ]
    
    zip_filename = 'latest_updates.zip'
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
                
    print(f"\nSUCCESS: Generated deployment zip at {zip_path}")
    print("\nYou can upload this zip file to your server and extract it in /home/drestro/htdocs/drestro.com/.")

if __name__ == '__main__':
    main()
