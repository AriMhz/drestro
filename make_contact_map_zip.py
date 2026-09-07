import zipfile
import os

files_to_zip = [
    'src/app/api/admin/settings/route.ts',
    'src/app/api/settings/public/route.ts',
    'src/app/admin/(dashboard)/footer/page.tsx',
    'src/app/contact/page.tsx',
    'src/app/contact/ContactClient.tsx'
]

with zipfile.ZipFile('drestro_contact_map_update.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file, arcname=file)
            print(f"Added: {file}")
        else:
            print(f"Missing: {file}")

print("Created drestro_contact_map_update.zip successfully.")
