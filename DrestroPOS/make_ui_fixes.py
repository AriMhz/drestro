import re

# 1. Fix staff-manager.blade.php UI
with open('resources/views/livewire/admin/staff-manager.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# I need to find where <!-- EDIT UI --> starts and replace the structure.
# Let's replace the start of Section 1 to add the wrapper
edit_ui_start_pattern = r"<!-- EDIT UI -->\s*<!-- Section 1: Personal Information -->\s*<div>"
edit_ui_start_replacement = """<!-- EDIT UI -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Left Column -->
                    <div class="space-y-8">
                        <!-- Section 1: Personal Information -->
                        <div>"""

content = re.sub(edit_ui_start_pattern, edit_ui_start_replacement, content)

# Replace the start of Section 3 to close the left column and open the right column
sec3_pattern = r"<!-- Section 3: Access & Description -->\s*<div>"
sec3_replacement = """</div>
                    <!-- Right Column -->
                    <div class="space-y-8">
                        <!-- Section 3: Access & Description -->
                        <div>"""

content = re.sub(sec3_pattern, sec3_replacement, content)

# Now, we need to close the right column and the grid before the Print Preview Badge
print_preview_pattern = r"(<!-- Print Preview Badge -->)"
print_preview_replacement = """    </div>
                </div>
                @endif
                \\1"""

# But wait, there is an existing @endif before Print Preview Badge!
# Let's just find the @endif before Print Preview Badge and replace it with closing tags + @endif
endif_pattern = r"(\s*)@endif\s*<!-- Print Preview Badge -->"
endif_replacement = r"\1    </div>\1</div>\1@endif\1<!-- Print Preview Badge -->"

content = re.sub(endif_pattern, endif_replacement, content)

with open('resources/views/livewire/admin/staff-manager.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)


# 2. Add updatedRole to StaffManager.php
with open('app/Livewire/Admin/StaffManager.php', 'r', encoding='utf-8') as f:
    staff_php = f.read()

updated_role_code = """
    public function updatedRole($value)
    {
        $presets = [
            'waiter' => ['take_order', 'waiter_dashboard'],
            'cashier' => ['cashier_panel'],
            'hotel' => ['take_room_service'],
            'receptionist' => ['hotel_reception'],
            'kitchen' => ['kitchen_display'],
            'bar' => ['bar_display'],
            'admin' => ['take_order', 'take_room_service', 'cashier_panel', 'kitchen_display', 'waiter_dashboard', 'bar_display', 'inventory', 'reports', 'menu_manager', 'restaurant_tables', 'hotel_room_manager', 'hotel_reception', 'qr_menu', 'staff_management'],
            'manager' => ['take_order', 'take_room_service', 'cashier_panel', 'kitchen_display', 'waiter_dashboard', 'bar_display', 'inventory', 'reports', 'menu_manager', 'restaurant_tables', 'hotel_room_manager', 'hotel_reception', 'qr_menu']
        ];
        
        if (array_key_exists($value, $presets)) {
            $this->allowedPages = $presets[$value];
        } else {
            $this->allowedPages = [];
        }
    }
"""

# Insert before public function selectAllPages()
staff_php = staff_php.replace('public function selectAllPages()', updated_role_code + '\n    public function selectAllPages()')

with open('app/Livewire/Admin/StaffManager.php', 'w', encoding='utf-8') as f:
    f.write(staff_php)


# 3. Remove "Switch to PIN Login" from login.blade.php
with open('resources/views/auth/login.blade.php', 'r', encoding='utf-8') as f:
    login_html = f.read()

# The block to remove is:
pin_block_pattern = r"<div class=\"mt-6 pt-6 border-t border-slate-100.*?Switch to PIN Login.*?</div>"
login_html = re.sub(pin_block_pattern, "", login_html, flags=re.DOTALL)

with open('resources/views/auth/login.blade.php', 'w', encoding='utf-8') as f:
    f.write(login_html)

# Create Deployment ZIP
import zipfile
import base64

with zipfile.ZipFile('deploy.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    zipf.write('resources/views/livewire/admin/staff-manager.blade.php', arcname='resources/views/livewire/admin/staff-manager.blade.php')
    zipf.write('app/Livewire/Admin/StaffManager.php', arcname='app/Livewire/Admin/StaffManager.php')
    zipf.write('resources/views/auth/login.blade.php', arcname='resources/views/auth/login.blade.php')

with open('deploy.zip', 'rb') as f:
    zip_b64 = base64.b64encode(f.read()).decode('utf-8')

php_script = "<?php\n"
php_script += "$zipData = base64_decode('" + zip_b64 + "');\n"
php_script += "file_put_contents('deploy_ui_fixes.zip', $zipData);\n"
php_script += "$zip = new ZipArchive;\n"
php_script += "if ($zip->open('deploy_ui_fixes.zip') === TRUE) {\n"
php_script += "    $zip->extractTo('../');\n"
php_script += "    $zip->close();\n"
php_script += "    unlink('deploy_ui_fixes.zip');\n"
php_script += "    echo '<h1>UI Fixes Deployed!</h1>';\n"
php_script += "} else {\n"
php_script += "    echo '<h1>Error extracting zip</h1>';\n"
php_script += "}\n"
php_script += "?>"

with open('deploy_ui_fixes.php', 'w') as f:
    f.write(php_script)
