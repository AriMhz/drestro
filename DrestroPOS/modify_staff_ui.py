import re

with open('resources/views/livewire/admin/staff-manager.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# I need to change the Add Staff button to "Invite Staff"
content = content.replace('>Add Staff Member<', '>Invite Staff<')
content = content.replace('Add New Staff Member', 'Invite Staff Member')

# I need to find the Modal Body and replace the !$isEditing part.
# The Modal Body starts at: <!-- Modal Body (Scrollable) -->
# Currently it has Section 1, Section 2, Section 3.
# I will use a simple str.replace to insert an @if(!$isEditing) ... @else around the sections.

invite_ui = """
                @if(!$isEditing)
                <!-- INVITATION UI -->
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-6 h-6 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center dark:bg-emerald-900/30 dark:text-emerald-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide dark:text-slate-300">Invite via Email or Link</h4>
                    </div>
                    
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">Enter the staff member's email and select their role. They will receive an invitation to set up their own account and password securely.</p>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1 dark:text-slate-300">Staff Email <span class="text-red-400">*</span></label>
                            <input type="email" wire:model="email" placeholder="e.g. staff@restaurant.com" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-transparent dark:bg-[#111] text-slate-800 dark:text-white outline-none transition-all focus:ring-2 focus:ring-slate-900 focus:border-slate-900 dark:border-[#333]">
                            @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1 dark:text-slate-300">Assign Role <span class="text-red-400">*</span></label>
                            <select wire:model="role" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-transparent dark:bg-[#111] text-slate-800 dark:text-white outline-none transition-all focus:ring-2 focus:ring-slate-900 focus:border-slate-900 dark:border-[#333]">
                                <option value="">Select a role...</option>
                                @foreach($this->availableRoles as $r)
                                    <option value="{{ $r }}">{{ ucfirst($r) }}</option>
                                @endforeach
                            </select>
                            @error('role') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                @else
                <!-- EDIT UI -->
"""

# Now replace the start of Section 1 to insert this logic
content = content.replace('<!-- Section 1: Personal Information -->', invite_ui + '<!-- Section 1: Personal Information -->')

# Now close the @endif before the Print Preview Badge
endif_close = """
                @endif
"""
content = content.replace('<!-- Print Preview Badge -->', endif_close + '<!-- Print Preview Badge -->')

# Also, change the footer button logic.
# Change wire:click="saveStaff" to wire:click="{{ $isEditing ? 'saveStaff' : 'inviteStaff' }}"
# And text to {{ $isEditing ? 'Update Staff' : 'Send Invitation' }}
content = content.replace('wire:click="saveStaff"', 'wire:click="{{ $isEditing ? \'saveStaff\' : \'inviteStaff\' }}"')
content = content.replace("{{ $isEditing ? 'Update Staff' : 'Create Staff' }}", "{{ $isEditing ? 'Update Staff' : 'Send Invitation' }}")

# Remove the PIN input completely from the Edit UI
pin_regex = r'<div class="w-1/2 pr-2">.*?PIN \(4 Digits\).*?</div>'
content = re.sub(pin_regex, '', content, flags=re.DOTALL)

# Add the QR Code display area outside the modal (or at the top of the page) when an invite link is generated
invite_link_alert = """
    @if(session()->has('invite_link'))
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-md">
        <div class="bg-white dark:bg-[#0a0a0a] rounded-3xl w-full max-w-md shadow-2xl p-8 border border-slate-200 dark:border-[#222] text-center relative">
            <button onclick="window.location.reload()" class="absolute top-4 right-4 p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <h3 class="text-2xl font-black text-slate-800 dark:text-white mb-2">Invitation Ready!</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Show this QR code to the staff member so they can scan it with their phone to join immediately.</p>
            
            <div class="bg-white p-4 rounded-2xl inline-block shadow-sm border border-slate-100 dark:border-slate-800 mb-6">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode(session('invite_link')) }}" alt="Invite QR" class="w-48 h-48">
            </div>

            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Or share this link:</p>
            <div class="flex items-center gap-2 bg-slate-50 dark:bg-[#111] p-2 rounded-xl border border-slate-200 dark:border-[#333]">
                <input type="text" readonly value="{{ session('invite_link') }}" class="w-full bg-transparent text-xs text-slate-600 dark:text-slate-300 outline-none px-2 font-mono" id="inviteLinkInput">
                <button onclick="navigator.clipboard.writeText(document.getElementById('inviteLinkInput').value); alert('Copied!')" class="p-2 bg-white dark:bg-[#222] border border-slate-200 dark:border-[#333] rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-[#333] transition-colors shrink-0 text-slate-600 dark:text-slate-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                </button>
            </div>
            
            <button onclick="window.location.reload()" class="w-full mt-6 bg-slate-900 dark:bg-white dark:text-slate-900 text-white font-bold py-3 rounded-xl transition-all shadow-lg hover:shadow-xl">Done</button>
        </div>
    </div>
    @endif
"""

# Insert at the very top of the file
content = invite_link_alert + content

# Also change the "Pending Invite" display in the staff table so it looks like an invitation rather than a real user.
pending_logic = """
                                    @if($staff->name === 'Pending Invite')
                                        <div class="font-bold text-amber-500 dark:text-amber-400 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            Pending Invitation
                                        </div>
                                    @else
                                        <div class="font-bold text-slate-800 dark:text-slate-200">{{ $staff->full_name }}</div>
                                    @endif
"""
content = content.replace('<div class="font-bold text-slate-800 dark:text-slate-200">{{ $staff->full_name }}</div>', pending_logic)


with open('resources/views/livewire/admin/staff-manager.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)
