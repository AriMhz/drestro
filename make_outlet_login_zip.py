import zipfile
import os

files_to_zip = [
    'prisma/schema.prisma',
    'auth.ts',
    'src/app/dashboard/page.tsx',
    'src/app/onboarding/OnboardingForm.tsx',
    'src/app/onboarding/actions.ts'
]

zip_filename = 'outlet_login_update.zip'

with zipfile.ZipFile(zip_filename, 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file, arcname=file)
            print(f"Added: {file}")
        else:
            print(f"ERROR: Missing file: {file}")

print(f"\nSUCCESS: Created {zip_filename} containing Outlet Login updates.")
