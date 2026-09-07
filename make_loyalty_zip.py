import zipfile
import os

files_to_zip = [
    'src/components/LoyaltyBanner.tsx',
    'src/components/layout/Footer.tsx',
    'src/app/pricing/PricingClient.tsx',
    'src/app/admin/(dashboard)/clients/ClientsClient.tsx'
]

with zipfile.ZipFile('drestro_loyalty_update.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file, arcname=file)
        else:
            print(f"Missing: {file}")

print("Created drestro_loyalty_update.zip")
