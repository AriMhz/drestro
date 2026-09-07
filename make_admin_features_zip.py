import zipfile
import os

files_to_zip = [
    # Schema
    'prisma/schema.prisma',
    # Public pages
    'src/app/contact/page.tsx',
    'src/components/HardwareCheckout.tsx',
    # Admin dashboard layout
    'src/app/admin/(dashboard)/layout.tsx',
    # API endpoints
    'src/app/api/contact/route.ts',
    'src/app/api/orders/route.ts',
    'src/app/api/admin/contact/route.ts',
    'src/app/api/admin/orders/route.ts',
    'src/app/api/admin/orders/[id]/route.ts',
    # Admin dashboard pages
    'src/app/admin/(dashboard)/contact/page.tsx',
    'src/app/admin/(dashboard)/contact/ContactClient.tsx',
    'src/app/admin/(dashboard)/orders/page.tsx',
    'src/app/admin/(dashboard)/orders/OrdersClient.tsx'
]

zip_name = 'drestro_admin_features.zip'

with zipfile.ZipFile(zip_name, 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file, arcname=file)
            print(f"Added: {file}")
        else:
            print(f"WARNING: File not found: {file}")

print(f"\nSuccessfully created {zip_name} containing the admin features and integrations.")
