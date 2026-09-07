import zipfile
import os

files_to_zip = [
    'DrestroPOS/app/helpers.php',
    'DrestroPOS/app/Http/Middleware/TenantMiddleware.php',
    'DrestroPOS/resources/views/livewire/staff/cashier-panel.blade.php',
    'DrestroPOS/resources/views/livewire/staff/hotel-cashier.blade.php',
    'DrestroPOS/resources/views/livewire/staff/waiter-order-taking.blade.php',
    'DrestroPOS/resources/views/livewire/admin/finance-manager.blade.php',
    'DrestroPOS/resources/views/components/layouts/app.blade.php',
    'DrestroPOS/app/Http/Controllers/SSOController.php',
    'DrestroPOS/resources/views/livewire/admin/settings.blade.php',
    'DrestroPOS/resources/views/livewire/admin/license-manager.blade.php',
    'DrestroPOS/app/Livewire/Admin/Settings.php',
    'DrestroPOS/app/Livewire/Admin/StaffManager.php',
    'DrestroPOS/app/Livewire/Admin/TableManager.php',
    'DrestroPOS/app/Livewire/Admin/RoomManager.php',
    'DrestroPOS/app/Livewire/Admin/MenuManager.php',
    'DrestroPOS/app/Services/PrinterService.php'
]

zip_filename = 'drestropos_updates.zip'

with zipfile.ZipFile(zip_filename, 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            # Strip the DrestroPOS/ prefix so it extracts directly to Laravel root on server
            arcname = file.replace('DrestroPOS/', '')
            zipf.write(file, arcname=arcname)
            print(f"Added: {file} -> {arcname}")
        else:
            print(f"ERROR: Missing file: {file}")

print(f"\nSUCCESS: Created {zip_filename} containing all portal updates.")
