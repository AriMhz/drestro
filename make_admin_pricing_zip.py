import zipfile
import os

files_to_zip = [
    'src/app/admin/(dashboard)/pricing/page.tsx',
    'src/app/admin/(dashboard)/pricing/[id]/page.tsx'
]

with zipfile.ZipFile('drestro_admin_pricing_update.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file, arcname=file)
        else:
            print(f"Missing: {file}")

print("Created drestro_admin_pricing_update.zip")
