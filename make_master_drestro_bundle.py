import zipfile
import os

files_to_zip = [
    # Checkout Price Sync Fix
    'src/app/api/plans/route.ts',
    'src/app/dashboard/billing/checkout/page.tsx',

    # Admin Clients Modal Scroll & Restaurant Update Sync Fix
    'src/app/admin/(dashboard)/clients/ClientsClient.tsx',
    'src/app/admin/(dashboard)/clients/page.tsx',

    # Admin Full Database Download System
    'src/app/api/admin/database/download/route.ts',
    'src/app/admin/(dashboard)/database/page.tsx',

    # Admin Orders & Discount Fix
    'src/app/admin/(dashboard)/orders/OrdersClient.tsx',

    # Inbox Sender Phone & Date Filter Fixes
    'src/app/admin/(dashboard)/contact/ContactClient.tsx',
    'src/app/admin/(dashboard)/billing/BillingClient.tsx',

    # Mobile View Logout Fixes
    'src/components/StaffPortalLayout.tsx',
    'src/app/admin/(dashboard)/layout.tsx',

    # Location-based Marketing Staff User Access & Assign Location Fixes
    'src/app/marketing/clients/page.tsx',
    'prisma/schema.prisma',

    # Welcome Email Helper & Registration Route
    'src/lib/email.ts',
    'src/app/api/auth/register/route.ts'
]

zip_name = 'drestro_all_updates.zip'

with zipfile.ZipFile(zip_name, 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file, arcname=file)
            print(f"Added: {file}")
        else:
            print(f"Warning: {file} not found locally.")

print(f"\nSUCCESS: Created master bundle {zip_name} containing ALL Next.js updates.")
