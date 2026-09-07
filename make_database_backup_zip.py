import zipfile
import os

files_to_zip = [
    'src/app/api/admin/database/download/route.ts',
    'src/app/admin/(dashboard)/layout.tsx',
    'src/app/admin/(dashboard)/database/page.tsx'
]

zip_filename = 'database_backup_feature.zip'

with zipfile.ZipFile(zip_filename, 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file, arcname=file)
            print(f"Added: {file}")
        else:
            print(f"ERROR: Missing file: {file}")

print(f"\nSUCCESS: Created {zip_filename} containing Database Backup feature.")
