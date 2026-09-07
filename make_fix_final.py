import zipfile
import os

files_to_zip = [
    'DrestroPOS/app/Livewire/Admin/MenuManager.php',
    'DrestroPOS/app/Livewire/Admin/TableManager.php',
    'DrestroPOS/resources/views/livewire/admin/menu-manager.blade.php',
    'DrestroPOS/resources/views/livewire/admin/table-manager.blade.php',
    'DrestroPOS/resources/views/livewire/admin/room-manager.blade.php',
    'DrestroPOS/resources/views/livewire/staff/waiter-order-taking.blade.php',
    'DrestroPOS/resources/views/livewire/staff/room-service.blade.php',
    'DrestroPOS/resources/views/livewire/staff/cashier-panel.blade.php',
    'DrestroPOS/resources/views/livewire/staff/kitchen-panel.blade.php',
]

with zipfile.ZipFile('drestropos_fixes_final.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file)
        else:
            print(f"File not found: {file}")

print("Created drestropos_fixes_final.zip")
