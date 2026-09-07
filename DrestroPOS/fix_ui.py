import re

with open('resources/views/livewire/admin/staff-manager.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Fix Modal Header color
content = content.replace('dark:from-[#111]">', 'dark:from-[#111] dark:to-[#0a0a0a]">')

# The Page Access Control block:
access_control_regex = r"(\{\{-- Page Access Control — super_admin can configure for any role --\}\}.*?@endif)"
# Wait, let's extract it manually from the file using regex.
match = re.search(r"(\{\{-- Page Access Control — super_admin can configure for any role --\}\}.*?@endif)", content, re.DOTALL)
if match:
    access_control_block = match.group(1)
    
    # We want to insert this block into the Invitation UI.
    # Look for the end of the INVITATION UI, specifically after the Role select.
    # The Role select ends with:
    # @error('role') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
    # </div>
    # </div>
    # </div>
    # @else
    
    insertion_point = r"(@error\('role'\).*?</div>\s*</div>\s*</div>)(\s*@else)"
    
    # Replace it by inserting the access control block before the @else
    # Let's add some margin to the access control block
    wrapped_access_control = f"\n<div class=\"mt-4\">\n{access_control_block}\n</div>\n"
    
    content = re.sub(insertion_point, r"\1" + wrapped_access_control.replace('\\', '\\\\') + r"\2", content, flags=re.DOTALL)

with open('resources/views/livewire/admin/staff-manager.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)

