import os
import re

MAPPING = {
    # Backgrounds
    "bg-white": "dark:bg-slate-900",
    "bg-slate-50": "dark:bg-slate-900",
    "bg-slate-100": "dark:bg-slate-800",
    "bg-slate-200": "dark:bg-slate-800",
    "bg-slate-300": "dark:bg-slate-700",
    "bg-slate-50/50": "dark:bg-slate-900/50",
    "bg-slate-50/30": "dark:bg-slate-900/30",
    "bg-slate-100/60": "dark:bg-slate-800/60",
    "bg-white/40": "dark:bg-slate-900/40",
    "bg-white/80": "dark:bg-slate-900/80",
    "bg-white/90": "dark:bg-slate-900/90",
    "bg-slate-900/40": "dark:bg-slate-900/80",
    
    # Text colors
    "text-slate-900": "dark:text-slate-100",
    "text-slate-800": "dark:text-slate-200",
    "text-slate-700": "dark:text-slate-300",
    "text-slate-600": "dark:text-slate-400",
    "text-slate-500": "dark:text-slate-400",
    
    # Borders
    "border-slate-100": "dark:border-slate-800",
    "border-slate-200": "dark:border-slate-700",
    "border-slate-200/60": "dark:border-slate-700/60",
    "border-slate-300": "dark:border-slate-600",
    
    # Colored backgrounds
    "bg-blue-50": "dark:bg-blue-900/20",
    "bg-amber-50": "dark:bg-amber-900/20",
    "bg-amber-50/20": "dark:bg-amber-900/20",
    "bg-emerald-50": "dark:bg-emerald-900/20",
    "bg-emerald-50/50": "dark:bg-emerald-900/20",
    "bg-red-50": "dark:bg-red-900/20",
    "bg-red-50/50": "dark:bg-red-900/20",
    "bg-orange-50": "dark:bg-orange-900/20",
    "bg-orange-50/50": "dark:bg-orange-900/20",
    "bg-rose-50": "dark:bg-rose-900/20",
    "bg-indigo-50": "dark:bg-indigo-900/20",
    "bg-teal-50": "dark:bg-teal-900/20",
    "bg-cyan-50": "dark:bg-cyan-900/20",
    
    # Colored text
    "text-blue-800": "dark:text-blue-300",
    "text-blue-700": "dark:text-blue-400",
    "text-blue-600": "dark:text-blue-400",
    "text-amber-800": "dark:text-amber-300",
    "text-amber-700": "dark:text-amber-400",
    "text-amber-600": "dark:text-amber-400",
    "text-emerald-900": "dark:text-emerald-100",
    "text-emerald-800": "dark:text-emerald-300",
    "text-emerald-700": "dark:text-emerald-400",
    "text-emerald-600": "dark:text-emerald-400",
    "text-red-800": "dark:text-red-300",
    "text-red-700": "dark:text-red-400",
    "text-red-600": "dark:text-red-400",
    "text-orange-800": "dark:text-orange-300",
    "text-orange-700": "dark:text-orange-400",
    "text-orange-600": "dark:text-orange-400",
    "text-rose-800": "dark:text-rose-300",
    "text-rose-700": "dark:text-rose-400",
    "text-indigo-800": "dark:text-indigo-300",
    "text-indigo-700": "dark:text-indigo-400",
    
    # Colored borders
    "border-blue-100": "dark:border-blue-800/50",
    "border-blue-200": "dark:border-blue-800",
    "border-amber-100": "dark:border-amber-800/50",
    "border-amber-200": "dark:border-amber-800",
    "border-emerald-100": "dark:border-emerald-800/50",
    "border-emerald-200": "dark:border-emerald-800",
    "border-emerald-200/30": "dark:border-emerald-800/30",
    "border-red-100": "dark:border-red-800/50",
    "border-red-200": "dark:border-red-800",
    "border-orange-100": "dark:border-orange-800/50",
    "border-orange-200": "dark:border-orange-800",
    "border-rose-100": "dark:border-rose-800/50",
    "border-indigo-100": "dark:border-indigo-800/50",

    # Gradients
    "from-emerald-50": "dark:from-emerald-900/40",
    "to-emerald-100/50": "dark:to-emerald-900/10",
    "to-teal-50": "dark:to-teal-900/20",
    "from-slate-50": "dark:from-slate-900/60",
    "to-slate-100/50": "dark:to-slate-900/20",
    "from-indigo-50": "dark:from-indigo-900/40",
    "to-indigo-100/50": "dark:to-indigo-900/10",
    "from-amber-50": "dark:from-amber-900/40",
    "to-amber-100/50": "dark:to-amber-900/10",
}

def process_classes(match):
    full_class_str = match.group(0) # e.g. class="bg-white text-slate-800"
    inner_str = match.group(1) # e.g. bg-white text-slate-800
    
    classes_to_add = []
    
    # Find all tokens by splitting by whitespace and single quotes (for blade ternary)
    tokens = re.split(r'[\s\'"]+', inner_str)
    
    for token in tokens:
        if token in MAPPING:
            dark_class = MAPPING[token]
            # Check if this dark class or a generic dark equivalent is already there
            if f"dark:{token.split('-')[0]}" not in inner_str and dark_class not in inner_str:
                 classes_to_add.append(dark_class)
                 
    if classes_to_add:
        # Deduplicate
        classes_to_add = list(set(classes_to_add))
        # Ensure we return it EXACTLY as it was, but append the dark classes inside the quotes
        # We need to determine if it used double or single quotes
        quote_char = full_class_str[-1]
        return f"class={quote_char}{inner_str} {' '.join(classes_to_add)}{quote_char}"
    
    return full_class_str

views_dir = 'resources/views'
modified_count = 0

for root, dirs, files in os.walk(views_dir):
    for file in files:
        if file.endswith('.blade.php'):
            filepath = os.path.join(root, file)
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
                
            # Regex to match class="..." or class='...' but NOT :class="..." or x-bind:class="..."
            # Negative lookbehind to ensure the character before "class=" is not a colon or word character
            new_content = re.sub(r'(?<![:\w-])class="([^"]+)"', process_classes, content)
            new_content = re.sub(r"(?<![:\w-])class='([^']+)'", process_classes, new_content)
            
            if new_content != content:
                with open(filepath, 'w', encoding='utf-8') as f:
                    f.write(new_content)
                modified_count += 1

print(f"Modified {modified_count} files!")
