import zipfile
import os

files_to_zip = [
    'src/components/layout/Navbar.tsx',
    'src/app/onboarding/page.tsx',
    'src/app/onboarding/OnboardingForm.tsx'
]

zip_name = 'drestro_onboarding_dark_fix.zip'

with zipfile.ZipFile(zip_name, 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file, arcname=file)
            print(f"Added: {file}")
        else:
            print(f"WARNING: File not found: {file}")

print(f"\nSuccessfully created {zip_name} containing the onboarding page & navbar dark mode improvements.")
