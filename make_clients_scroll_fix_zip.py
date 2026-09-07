import zipfile
import os

files_to_zip = [
    'src/app/admin/(dashboard)/clients/ClientsClient.tsx'
]

zip_name = 'clients_scroll_fix.zip'

with zipfile.ZipFile(zip_name, 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file, arcname=file)
            print(f"Added: {file}")
        else:
            print(f"ERROR: Missing file: {file}")

print(f"\nSUCCESS: Created {zip_name} containing Clients Modal Scroll Fix.")
