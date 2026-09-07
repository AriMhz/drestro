import zipfile
import os

files_to_zip = [
    'src/app/api/support/tickets/route.ts',
    'src/app/admin/(dashboard)/support/page.tsx',
    'src/app/admin/(dashboard)/support/SupportClient.tsx'
]

with zipfile.ZipFile('drestro_support_ticket_fix.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file, arcname=file)
            print(f"Added: {file}")
        else:
            print(f"Missing: {file}")

print("Created drestro_support_ticket_fix.zip")
