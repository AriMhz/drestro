import zipfile
import os

files_to_zip = [
    'src/app/admin/login/page.tsx',
    'src/app/forgot-password/page.tsx',
    'src/components/HardwareCheckout.tsx',
    'src/app/api/orders/route.ts',
    'src/app/admin/(dashboard)/clients/page.tsx',
    'src/app/sales/clients/page.tsx',
    'src/app/support/clients/page.tsx',
    'src/app/marketing/clients/page.tsx',
    'src/app/admin/(dashboard)/clients/ClientsClient.tsx',
    'src/app/admin/(dashboard)/pricing/ComboForm.tsx',
    'src/app/admin/(dashboard)/pricing/PricingAdminClient.tsx',
    'src/app/pricing/PricingClient.tsx',
    'src/app/offer/[id]/page.tsx',
    'src/app/login/page.tsx',
    'src/app/register/page.tsx',
    'src/app/reset-password/page.tsx',
    'src/app/demo/page.tsx'
]

zip_name = 'drestro_login_location_updates.zip'

with zipfile.ZipFile(zip_name, 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file, arcname=file)
            print(f"Added: {file}")
        else:
            print(f"Warning: {file} does not exist!")

print(f"Created {zip_name} successfully containing all login location, session, checkout email, and clients edit updates.")
