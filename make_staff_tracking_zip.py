import zipfile
import os

files_to_zip = [
    'prisma/schema.prisma',
    'src/app/admin/(dashboard)/inbox/page.tsx',
    'src/app/admin/(dashboard)/clients/page.tsx',
    'src/app/api/admin/support/[id]/route.ts',
    'src/app/admin/(dashboard)/support/SupportClient.tsx',
    'src/app/admin/(dashboard)/clients/ClientsClient.tsx'
]

zip_name = 'drestro_staff_tracking.zip'

with zipfile.ZipFile(zip_name, 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file, arcname=file)
            print(f"Added: {file}")
        else:
            print(f"WARNING: File not found: {file}")

print(f"\nSuccessfully created {zip_name} containing the staff activity tracking updates.")
