import zipfile
import os

files_to_zip = [
    'src/components/layout/Navbar.tsx',
    'src/components/LoyaltyBanner.tsx',
    'src/app/api/plans/route.ts',
    'src/app/api/restaurant/sync/route.ts',
    'src/app/admin/(dashboard)/clients/ClientsClient.tsx',
    'src/app/admin/(dashboard)/support/page.tsx',
    'src/app/admin/(dashboard)/support/SupportClient.tsx',
    'src/app/api/support/tickets/route.ts',
    'src/app/admin/login/page.tsx',
    'src/middleware.ts',
    'src/app/api/admin/logout/route.ts',
    'prisma/schema.prisma'
]

with zipfile.ZipFile('drestro_website_update.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file, arcname=file)
        else:
            print(f"Missing: {file}")

print("Created drestro_website_update.zip containing recent Next.js website updates.")

