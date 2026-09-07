import os
import zipfile

files_to_zip = [
    'src/app/admin/(dashboard)/layout.tsx',
    'src/app/admin/(dashboard)/hardware/HardwareForm.tsx',
    'src/app/admin/(dashboard)/inbox/page.tsx',
    'src/app/admin/(dashboard)/inbox/InboxClient.tsx',
    'src/app/admin/(dashboard)/website/page.tsx',
    'src/app/admin/(dashboard)/website/WebsiteClient.tsx',
    'src/app/admin/login/page.tsx'
]

zip_filename = 'drestro_sidebar_consolidation.zip'

with zipfile.ZipFile(zip_filename, 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file)
            print(f"Added {file}")
        else:
            print(f"Warning: {file} not found")

print(f"\nCreated {zip_filename} successfully.")
