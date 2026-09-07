import zipfile
import os

files_to_zip = [
    'src/app/admin/(dashboard)/clients/ClientsClient.tsx',
    'src/app/admin/(dashboard)/website/WebsiteClient.tsx'
]

with zipfile.ZipFile('drestro_admin_updates.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file, arcname=file)
            print(f"Added: {file}")
        else:
            print(f"Warning: {file} does not exist!")

print("Created drestro_admin_updates.zip successfully.")
