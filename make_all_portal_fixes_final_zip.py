import zipfile
import os

def main():
    base_dir = os.path.dirname(os.path.abspath(__file__))
    
    files_to_zip = [
        'DrestroPOS/app/helpers.php',
        'DrestroPOS/app/Livewire/Admin/CustomerManager.php',
        'DrestroPOS/resources/views/livewire/admin/customer-manager.blade.php',
        'DrestroPOS/app/Livewire/Admin/FinanceManager.php',
        'DrestroPOS/resources/views/livewire/admin/finance-manager.blade.php',
        'DrestroPOS/resources/views/print/qr.blade.php',
        'DrestroPOS/resources/views/livewire/admin/table-manager.blade.php',
        'DrestroPOS/app/Http/Middleware/TenantMiddleware.php',
        'DrestroPOS/resources/views/components/layouts/app.blade.php',
        'DrestroPOS/routes/web.php',
        'DrestroPOS/app/Http/Controllers/SSOController.php',
        'DrestroPOS/resources/views/livewire/admin/dashboard.blade.php',
        'DrestroPOS/resources/views/livewire/admin/ordered-goods.blade.php',
        'DrestroPOS/app/Livewire/Admin/Settings.php',
        'DrestroPOS/resources/views/livewire/admin/settings.blade.php',
        'DrestroPOS/resources/views/livewire/staff/hotel-cashier.blade.php',
        'DrestroPOS/resources/views/livewire/staff/cashier-panel.blade.php',
        'DrestroPOS/public/images/logo.svg',
        'DrestroPOS/public/images/logo-light.svg',
        'DrestroPOS/public/icon.svg',
        'DrestroPOS/public/images/icon.svg',
        'DrestroPOS/public/favicon.ico'
    ]

    zip_path = os.path.join(base_dir, 'all_portal_fixes_final.zip')
    with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
        for file_path in files_to_zip:
            full_path = os.path.join(base_dir, file_path)
            if os.path.exists(full_path):
                arcname = file_path.replace('DrestroPOS/', '', 1)
                zipf.write(full_path, arcname=arcname)
                print(f"Added: {file_path} -> {arcname}")
            else:
                print(f"MISSING: {file_path}")

    print(f"\nSuccessfully generated {zip_path}")

if __name__ == '__main__':
    main()
