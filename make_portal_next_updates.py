import zipfile
import os

files_to_zip = [
    'src/app/admin/(dashboard)/layout.tsx',
    'src/components/StaffPortalLayout.tsx',
    'src/app/dashboard/page.tsx',
    'src/app/admin/(dashboard)/clients/ClientsClient.tsx',
    'src/app/admin/(dashboard)/clients/page.tsx',
    'src/app/onboarding/page.tsx',
    'src/app/onboarding/OnboardingForm.tsx',
    'src/app/onboarding/actions.ts',
    'src/app/admin/(dashboard)/billing/page.tsx',
    'src/app/admin/(dashboard)/billing/BillingClient.tsx',
    'src/app/api/contact/route.ts',
    'src/app/contact/ContactClient.tsx',
    'prisma/schema.prisma'
]

zip_filename = 'drestro_portal_next_updates.zip'

with zipfile.ZipFile(zip_filename, 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file, arcname=file)
            print(f"Added: {file}")
        else:
            print(f"ERROR: Missing file: {file}")

print(f"\nSUCCESS: Created {zip_filename} containing all Next.js portal updates.")
