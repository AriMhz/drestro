import os
import zipfile

files_to_zip = [
    'src/app/dashboard/billing/checkout/page.tsx',
    'src/app/pricing/page.tsx',
    'src/app/pricing/PricingClient.tsx',
]

with zipfile.ZipFile('drestro_frontend_fixes.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file)
            print(f"Added {file}")
        else:
            print(f"Warning: {file} not found")

print("Created drestro_frontend_fixes.zip successfully.")
