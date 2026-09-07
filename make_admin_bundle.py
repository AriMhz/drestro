import os
import zipfile

files_to_zip = [
    'src/app/admin/(dashboard)/layout.tsx',
    'src/app/admin/(dashboard)/pricing/page.tsx',
    'src/app/admin/(dashboard)/pricing/PricingAdminClient.tsx',
    'src/app/admin/(dashboard)/pricing/ComboForm.tsx'
]

with zipfile.ZipFile('drestro_admin_merge.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file)
            print(f"Added {file}")
        else:
            print(f"Warning: {file} not found")

print("Created drestro_admin_merge.zip successfully.")
