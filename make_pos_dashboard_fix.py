import zipfile
import os

files_to_zip = [
    'DrestroPOS/resources/views/livewire/admin/dashboard.blade.php'
]

zip_name = 'drestropos_dashboard_mobile_fix.zip'

with zipfile.ZipFile(zip_name, 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file, arcname=file)
            print(f"Added: {file}")
        else:
            print(f"WARNING: File not found: {file}")

print(f"\nSuccessfully created {zip_name} containing the POS dashboard mobile title fix.")
