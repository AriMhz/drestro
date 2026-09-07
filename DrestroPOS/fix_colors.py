import re

with open('resources/views/livewire/admin/staff-manager.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Add bg-transparent dark:bg-[#111] dark:text-white to all inputs and textareas
content = content.replace('outline-none transition-all', 'bg-transparent dark:bg-[#111] text-slate-800 dark:text-white outline-none transition-all')

# Write back
with open('resources/views/livewire/admin/staff-manager.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)
