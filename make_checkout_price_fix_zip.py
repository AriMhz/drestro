import zipfile
import os

files_to_zip = [
    'src/app/api/plans/route.ts',
    'src/app/dashboard/billing/checkout/page.tsx'
]

zip_filename = 'checkout_price_fix.zip'

with zipfile.ZipFile(zip_filename, 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file, arcname=file)
            print(f"Added: {file}")
        else:
            print(f"ERROR: Missing file: {file}")

print(f"\nSUCCESS: Created {zip_filename} containing Checkout Price Fix.")
