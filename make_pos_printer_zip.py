import zipfile
import os

files_to_zip = [
    'DrestroPOS/routes/web.php',
    'DrestroPOS/app/Livewire/Admin/Settings.php',
    'DrestroPOS/app/Livewire/Admin/StaffManager.php',
    'DrestroPOS/app/Livewire/Admin/FinanceManager.php',
    'DrestroPOS/app/Services/PrinterService.php',
    'DrestroPOS/app/Services/LicenseManager.php',
    'DrestroPOS/app/Http/Middleware/CheckLicense.php',
    'DrestroPOS/app/Livewire/Staff/CashierPanel.php',
    'DrestroPOS/app/Livewire/Staff/WaiterOrderTaking.php',
    'DrestroPOS/app/Livewire/Staff/WaiterDashboard.php',
    'DrestroPOS/app/Livewire/Customer/DigitalMenu.php',
    'DrestroPOS/app/Http/Controllers/SSOController.php',
    'DrestroPOS/resources/views/livewire/admin/settings.blade.php',
    'DrestroPOS/resources/views/livewire/admin/finance-manager.blade.php',
    'DrestroPOS/resources/views/livewire/admin/license-manager.blade.php',
    'DrestroPOS/resources/views/livewire/staff/cashier-panel.blade.php',
    'DrestroPOS/resources/views/livewire/staff/waiter-order-taking.blade.php',
    'DrestroPOS/resources/views/livewire/staff/waiter-dashboard.blade.php',
    'DrestroPOS/resources/views/livewire/customer/digital-menu.blade.php',
    'DrestroPOS/resources/views/livewire/staff/kitchen-panel.blade.php',
    'DrestroPOS/resources/views/livewire/admin/staff-manager.blade.php',
    'DrestroPOS/resources/views/components/layouts/app.blade.php',
    'DrestroPOS/resources/views/print/kot.blade.php',
    'DrestroPOS/resources/views/print/bot.blade.php',
    'DrestroPOS/resources/views/print/receipt.blade.php'
]

zip_name = 'drestropos_printer_fix.zip'

with zipfile.ZipFile(zip_name, 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            arcname = file.replace('DrestroPOS/', '', 1)
            zipf.write(file, arcname=arcname)
            print(f"Added: {file} as {arcname}")
        else:
            print(f"Warning: {file} does not exist!")

print(f"Created {zip_name} successfully.")
