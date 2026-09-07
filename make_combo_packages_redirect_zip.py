import zipfile
import os

files_to_zip = [
    'src/app/pricing/page.tsx',
    'src/app/pricing/PricingClient.tsx',
    'src/app/products/page.tsx',
    'src/components/layout/Footer.tsx',
    'src/app/admin/(dashboard)/pricing/page.tsx',
    'src/app/admin/(dashboard)/website/page.tsx'
]

zip_name = 'drestro_combo_redirect_update.zip'

with zipfile.ZipFile(zip_name, 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file, arcname=file)
            print(f"Added: {file}")
        else:
            print(f"Missing: {file}")

print(f"Created {zip_name} successfully.")
