import zipfile
import os

files_to_zip = [
    'src/app/api/admin/settings/route.ts',
    'src/app/api/settings/public/route.ts',
    'src/app/admin/(dashboard)/footer/page.tsx',
    'src/app/refund/page.tsx',
    'src/app/pricing/PricingClient.tsx'
]

zip_name = 'drestro_refund_and_buttons_update.zip'

with zipfile.ZipFile(zip_name, 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file, arcname=file)
            print(f"Added: {file}")
        else:
            print(f"Missing: {file}")

print(f"Created {zip_name} successfully.")
