import os
import re

directories = [
    'src/app/admin',
    'src/app/marketing',
    'src/app/sales',
    'src/app/support',
    'src/components'
]

replacements = [
    # Backgrounds
    (r'bg-\[\#111111\]', r'bg-slate-50 dark:bg-[#111111]'),
    (r'bg-\[\#1A1A1A\]', r'bg-white dark:bg-[#1A1A1A]'),
    (r'bg-\[\#222222\]', r'bg-slate-100 dark:bg-[#222222]'),
    (r'bg-\[\#333333\]', r'bg-slate-200 dark:bg-[#333333]'),
    
    # Text colors
    (r'text-white', r'text-[#111111] dark:text-white'),
    (r'hover:text-white', r'hover:text-[#111111] dark:hover:text-white'),
    (r'text-gray-400', r'text-slate-500 dark:text-gray-400'),
    (r'text-gray-500', r'text-slate-400 dark:text-gray-500'),
    (r'text-gray-300', r'text-slate-600 dark:text-gray-300'),
    
    # Borders
    (r'border-\[\#333333\]', r'border-slate-200 dark:border-[#333333]'),
    (r'border-\[\#222222\]', r'border-slate-300 dark:border-[#222222]'),
    (r'border-gray-800', r'border-slate-200 dark:border-gray-800'),
    
    # Specific edge cases that might get doubled up (e.g. text-[#111111] dark:text-[#111111] dark:text-white)
    (r'text-\[\#111111\] dark:text-\[\#111111\] dark:text-white', r'text-[#111111] dark:text-white'),
    (r'bg-slate-50 dark:bg-slate-50 dark:bg-\[\#111111\]', r'bg-slate-50 dark:bg-[#111111]')
]

def process_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    original = content
    
    for old, new in replacements:
        # Avoid replacing if already replaced by checking a negative lookbehind/lookahead if we could, 
        # but since we might run multiple passes, let's just do a blind replace and then clean up doubles.
        content = re.sub(old, new, content)
        
    # Cleanup any accidental doubling
    content = content.replace('text-[#111111] dark:text-[#111111] dark:text-white', 'text-[#111111] dark:text-white')
    content = content.replace('bg-slate-50 dark:bg-slate-50 dark:bg-[#111111]', 'bg-slate-50 dark:bg-[#111111]')
    content = content.replace('bg-white dark:bg-white dark:bg-[#1A1A1A]', 'bg-white dark:bg-[#1A1A1A]')
    content = content.replace('text-slate-500 dark:text-slate-500 dark:text-gray-400', 'text-slate-500 dark:text-gray-400')
    content = content.replace('border-slate-200 dark:border-slate-200 dark:border-[#333333]', 'border-slate-200 dark:border-[#333333]')

    if original != content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated {filepath}")

for d in directories:
    for root, dirs, files in os.walk(d):
        for file in files:
            if file.endswith(('.tsx', '.ts')):
                process_file(os.path.join(root, file))

print("Done updating colors.")
