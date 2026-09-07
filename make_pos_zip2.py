import zipfile
import os

files_to_zip = [
    'DrestroPOS/app/Livewire/Admin/LicenseManager.php',
    'DrestroPOS/app/Livewire/Admin/SupportTickets.php',
    'DrestroPOS/app/Services/LicenseManager.php',
    'DrestroPOS/resources/views/components/layouts/app.blade.php',
    'DrestroPOS/resources/views/livewire/admin/license-manager.blade.php'
]

with zipfile.ZipFile('drestropos_url_fixes2.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            # Strip 'DrestroPOS/' from the beginning of the file path inside the zip!
            arcname = file.replace('DrestroPOS/', '', 1)
            zipf.write(file, arcname=arcname)
        else:
            print(f"Missing: {file}")

print("Created drestropos_url_fixes2.zip without prefix")
