import zipfile
import os

files_to_zip = [
    'DrestroPOS/app/Livewire/Admin/LicenseManager.php',
    'DrestroPOS/app/Livewire/Admin/SupportTickets.php',
    'DrestroPOS/app/Services/LicenseManager.php',
    'DrestroPOS/resources/views/components/layouts/app.blade.php',
    'DrestroPOS/resources/views/livewire/admin/license-manager.blade.php',
    'DrestroPOS/app/Livewire/Admin/Settings.php',
    'DrestroPOS/resources/views/livewire/admin/settings.blade.php',
    'DrestroPOS/resources/views/livewire/staff/cashier-panel.blade.php',
    'DrestroPOS/resources/views/livewire/staff/hotel-cashier.blade.php',
    'DrestroPOS/app/Livewire/Admin/StaffManager.php',
    'DrestroPOS/resources/views/livewire/admin/staff-manager.blade.php',
    'DrestroPOS/public/images/logo.svg',
    'DrestroPOS/public/images/logo-light.svg',
    'DrestroPOS/public/images/icon.svg',
    'DrestroPOS/public/icon.svg',
    'DrestroPOS/public/favicon.ico'
]

with zipfile.ZipFile('drestropos_printer_fix.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            # Strip the DrestroPOS/ prefix so it extracts directly to Laravel root on server
            arcname = file.replace('DrestroPOS/', '')
            zipf.write(file, arcname=arcname)
        else:
            print(f"Missing: {file}")

print("Created drestropos_printer_fix.zip without folder prefix")
