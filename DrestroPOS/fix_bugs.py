import re

with open('resources/views/livewire/admin/staff-manager.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Move the invite_link_alert inside the root element
invite_link_regex = r"(@if\(session\(\)->has\('invite_link'\)\).*?@endif\n)"
match = re.search(invite_link_regex, content, re.DOTALL)
if match:
    invite_block = match.group(1)
    # Remove it from the top
    content = content.replace(invite_block, '', 1)
    # Insert it right after the root div: <div class="space-y-6">
    content = content.replace('<div class="space-y-6">', '<div class="space-y-6">\n' + invite_block, 1)

# 2. Fix the wire:model="role" to wire:model.live="role" in BOTH INVITATION UI and EDIT UI
content = content.replace('wire:model="role"', 'wire:model.live="role"')

# 3. Fix the modal header colors using standard Tailwind colors
content = content.replace('bg-gradient-to-r from-slate-50 to-white flex-shrink-0 dark:border-[#222] dark:from-[#111] dark:to-[#0a0a0a]', 'bg-white flex-shrink-0 dark:border-slate-800 dark:bg-slate-900')
content = content.replace('bg-gradient-to-r from-slate-50 to-white flex-shrink-0 dark:border-[#222] dark:from-[#111]', 'bg-white flex-shrink-0 dark:border-slate-800 dark:bg-slate-900')

with open('resources/views/livewire/admin/staff-manager.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)

# We also need to add a deployment script
php_script = """<?php
$viewsPath = __DIR__ . '/../storage/framework/views';
$files = glob($viewsPath . '/*.php');
if ($files) {
    foreach ($files as $file) {
        if (is_file($file)) unlink($file);
    }
}
echo '<h1>Fixed!</h1>';
"""
with open('deploy_final_fix.php', 'w') as f:
    f.write(php_script)
