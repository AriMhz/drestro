import zipfile
import os

files_to_zip = [
    'prisma/schema.prisma',
    'auth.ts',
    'src/lib/sms.ts',
    'src/app/api/auth/otp/route.ts',
    'src/app/api/auth/register/route.ts',
    'src/app/api/auth/forgot-password/route.ts',
    'src/app/api/auth/reset-password/route.ts',
    'src/app/login/page.tsx',
    'src/app/register/page.tsx',
    'src/app/forgot-password/page.tsx'
]

zip_name = 'drestro_phone_otp_updates.zip'

with zipfile.ZipFile(zip_name, 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file, arcname=file)
            print(f"Added: {file}")
        else:
            print(f"WARNING: File not found: {file}")

print(f"\nSuccessfully created {zip_name} containing the phone registration & OTP reset updates.")
