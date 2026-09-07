import os
import re

base_dir = 'DrestroPOS'

# 1. Fix MenuManager.php
file_path = os.path.join(base_dir, 'app/Livewire/Admin/MenuManager.php')
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()
content = content.replace(
    "$this->restaurant = Restaurant::firstOrCreate(",
    "$this->restaurant = current_restaurant() ?? Restaurant::firstOrCreate("
)
with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

# 2. Fix TableManager.php
file_path = os.path.join(base_dir, 'app/Livewire/Admin/TableManager.php')
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()
content = content.replace(
    "$this->restaurant = Restaurant::firstOrCreate(",
    "$this->restaurant = current_restaurant() ?? Restaurant::firstOrCreate("
)
with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

# 3. Add @error to menu-manager.blade.php
file_path = os.path.join(base_dir, 'resources/views/livewire/admin/menu-manager.blade.php')
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace(
    'placeholder="e.g. Momo">',
    'placeholder="e.g. Momo">\n                      @error(\'newCategoryName\') <span class="text-xs font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror'
)
content = content.replace(
    '<option value="bar">Bar (Drinks/Beverages)</option>\n                      </select>',
    '<option value="bar">Bar (Drinks/Beverages)</option>\n                      </select>\n                      @error(\'newCategoryDepartment\') <span class="text-xs font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror'
)
content = content.replace(
    'accept="image/*">',
    'accept="image/*">\n                      @error(\'newCategoryImage\') <span class="text-xs font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror'
)
with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

# 4. Add @error to table-manager.blade.php
file_path = os.path.join(base_dir, 'resources/views/livewire/admin/table-manager.blade.php')
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace(
    'placeholder="e.g. Table 1">',
    'placeholder="e.g. Table 1">\n                      @error(\'newTableName\') <span class="text-xs font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror'
)
content = content.replace(
    'min="1" max="20">',
    'min="1" max="20">\n                      @error(\'newTableCapacity\') <span class="text-xs font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror'
)
with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

# 5. Fix room-manager.blade.php mobile delete button
file_path = os.path.join(base_dir, 'resources/views/livewire/admin/room-manager.blade.php')
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()
content = content.replace(
    'opacity-0 group-hover:opacity-100',
    'opacity-100 lg:opacity-0 lg:group-hover:opacity-100'
)
with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

# 6. Fix waiter-order-taking.blade.php gradient
file_path = os.path.join(base_dir, 'resources/views/livewire/staff/waiter-order-taking.blade.php')
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()
content = content.replace(
    'dark:from-slate-900/60',
    'dark:from-slate-900 dark:to-slate-950'
)
with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

# 7. Fix room-service.blade.php background
file_path = os.path.join(base_dir, 'resources/views/livewire/staff/room-service.blade.php')
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()
content = content.replace(
    'bg-[#f1f5f9]/30">',
    'bg-[#f1f5f9]/30 dark:bg-slate-950">'
)
with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

# 8. Fix cashier-panel.blade.php tabs
file_path = os.path.join(base_dir, 'resources/views/livewire/staff/cashier-panel.blade.php')
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()
content = content.replace(
    'bg-slate-200/50 p-1',
    'bg-slate-200/50 dark:bg-slate-800 p-1'
)
content = content.replace(
    'shadow-sm\' : \'text-slate-500 hover:text-slate-700\' }} dark:bg-slate-900 dark:text-slate-400 dark:text-emerald-400',
    'shadow-sm dark:bg-slate-900 dark:text-emerald-400\' : \'text-slate-500 hover:text-slate-700 dark:text-slate-400\' }}'
)
content = content.replace(
    'shadow-sm\' : \'text-slate-500 hover:text-slate-700\' }} dark:bg-slate-900 dark:text-slate-400',
    'shadow-sm dark:bg-slate-900 dark:text-indigo-400\' : \'text-slate-500 hover:text-slate-700 dark:text-slate-400\' }}'
)
content = content.replace(
    'bg-slate-100/80 p-0.5',
    'bg-slate-100/80 dark:bg-slate-800 p-0.5'
)
content = content.replace(
    'shadow-sm\' : \'text-slate-400 hover:text-slate-600\' }} dark:bg-slate-900 dark:text-amber-400',
    'shadow-sm dark:bg-slate-900 dark:text-amber-400\' : \'text-slate-400 hover:text-slate-600 dark:text-slate-400\' }}'
)
content = content.replace(
    'shadow-sm\' : \'text-slate-400 hover:text-slate-600\' }} dark:bg-slate-900 dark:text-emerald-400',
    'shadow-sm dark:bg-slate-900 dark:text-emerald-400\' : \'text-slate-400 hover:text-slate-600 dark:text-slate-400\' }}'
)
content = content.replace(
    'shadow-sm\' : \'text-slate-400 hover:text-slate-600\' }} dark:bg-slate-900 dark:text-red-400',
    'shadow-sm dark:bg-slate-900 dark:text-red-400\' : \'text-slate-400 hover:text-slate-600 dark:text-slate-400\' }}'
)
content = content.replace(
    'shadow-sm\' : \'text-slate-400 hover:text-slate-600\' }} dark:bg-slate-900 dark:text-slate-300',
    'shadow-sm dark:bg-slate-900 dark:text-slate-300\' : \'text-slate-400 hover:text-slate-600 dark:text-slate-400\' }}'
)
with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

# 9. Fix kitchen-panel.blade.php and bar-panel.blade.php
for file_name in ['kitchen-panel.blade.php', 'bar-panel.blade.php']:
    file_path = os.path.join(base_dir, 'resources/views/livewire/staff', file_name)
    if not os.path.exists(file_path):
        continue
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    content = content.replace('bg-slate-900 -mx-4', 'bg-slate-50 dark:bg-slate-900 -mx-4')
    content = content.replace('bg-slate-800 px-4', 'bg-white dark:bg-slate-800 px-4')
    content = content.replace('bg-slate-800/50', 'bg-slate-200/50 dark:bg-slate-800/50')
    content = content.replace('bg-slate-800 rounded-2xl', 'bg-white dark:bg-slate-800 rounded-2xl')
    content = content.replace('bg-slate-800 flex', 'bg-slate-100 dark:bg-slate-800 flex')
    content = content.replace('bg-slate-700/50', 'bg-slate-100 dark:bg-slate-700/50')
    content = content.replace('text-white tracking-tight', 'text-slate-800 dark:text-white tracking-tight')
    content = content.replace('text-white\n                                            </div>', 'text-slate-800 dark:text-white\n                                            </div>')
    content = content.replace('text-slate-400 mt-1', 'text-slate-500 dark:text-slate-400 mt-1')
    content = content.replace('text-slate-400 uppercase', 'text-slate-500 dark:text-slate-400 uppercase')
    content = content.replace('text-white border-b', 'text-slate-800 dark:text-white border-b')
    content = content.replace('text-slate-300 flex', 'text-slate-600 dark:text-slate-300 flex')
    content = content.replace('border-slate-700', 'border-slate-200 dark:border-slate-700')
    content = content.replace('text-white\n                                                <span', 'text-slate-800 dark:text-white\n                                                <span')
    
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)

print("Patches applied successfully.")

import zipfile
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
    'DrestroPOS/resources/views/livewire/staff/bar-panel.blade.php',
]

with zipfile.ZipFile('drestropos_fixes_batch3.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            arcname = file.replace('DrestroPOS/', '', 1)
            zipf.write(file, arcname=arcname)
        else:
            print(f"File not found: {file}")

print("Created drestropos_fixes_batch3.zip")
