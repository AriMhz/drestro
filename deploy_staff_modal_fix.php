<?php
$target_file = __DIR__ . '/../resources/views/livewire/admin/staff-manager.blade.php';
$content = <<< 'EOT'

    <div class="space-y-6">
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

    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Staff & Roles</h1>
            <p class="text-sm text-slate-500 mt-1 dark:text-slate-400">Manage employees, access levels, and printed identity.</p>
        </div>
        @if(auth()->user()->role === 'super_admin')
        <button wire:click="openModal" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-xl shadow-sm transition-all flex items-center gap-2 text-sm shrink-0 self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add Staff Member
        </button>
        @endif
    </div>

    <!-- Staff Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm dark:bg-[#0a0a0a] dark:border-[#333]">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase tracking-wider text-xs dark:border-[#222] dark:bg-[#0a0a0a] dark:text-slate-400">
                        <th class="px-4 sm:px-6 py-4 font-semibold">Staff Member</th>
                        <th class="px-4 sm:px-6 py-4 font-semibold hidden sm:table-cell">Contact</th>
                        <th class="px-4 sm:px-6 py-4 font-semibold">Role</th>
                        <th class="px-4 sm:px-6 py-4 font-semibold hidden md:table-cell">Login Details</th>
                        <th class="px-4 sm:px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($staffMembers as $staff)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 sm:px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center font-bold text-white text-sm border-2 border-slate-200 shadow-sm dark:border-[#333]">
                                    {{ strtoupper(substr($staff->first_name ?? $staff->name, 0, 1)) }}{{ strtoupper(substr($staff->last_name ?? '', 0, 1)) }}
                                </div>
                                <div>
                                    
                                    @if($staff->name === 'Pending Invite')
                                        <div class="font-bold text-amber-500 dark:text-amber-400 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            Pending Invitation
                                        </div>
                                    @else
                                        <div class="font-bold text-slate-800 dark:text-slate-200">{{ $staff->full_name }}</div>
                                    @endif

                                    @if($staff->description)
                                        <div class="text-[10px] text-slate-400 mt-0.5 max-w-[180px] truncate">{{ $staff->description }}</div>
                                    @else
                                        <div class="text-xs text-slate-400">Joined {{ $staff->created_at->format('M Y') }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 sm:px-6 py-4 hidden sm:table-cell">
                            <div class="text-slate-600 text-xs dark:text-slate-400">{{ $staff->email }}</div>
                            @if($staff->phone)
                                <div class="text-xs text-slate-400 mt-0.5">📞 {{ $staff->phone }}</div>
                            @endif
                        </td>
                        <td class="px-4 sm:px-6 py-4">
                            @php
                                $role = ucfirst(str_replace('_', ' ', $staff->role ?? 'waiter'));
                                $roleColors = [
                                    'Super admin' => 'bg-red-100 text-red-700 border-red-200',
                                    'Admin' => 'bg-purple-100 text-purple-700 border-purple-200',
                                    'Manager' => 'bg-violet-100 text-violet-700 border-violet-200',
                                    'Cashier' => 'bg-blue-100 text-blue-700 border-blue-200',
                                    'Waiter' => 'bg-rose-100 text-rose-700 border-rose-200',
                                    'Hotel' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                    'Receptionist' => 'bg-cyan-100 text-cyan-700 border-cyan-200',
                                    'Kitchen' => 'bg-orange-100 text-orange-700 border-orange-200',
                                    'Bar' => 'bg-amber-100 text-amber-700 border-amber-200',
                                ];
                                $color = $roleColors[$role] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                            @endphp
                            <span class="inline-block whitespace-nowrap px-3 py-1 text-xs font-bold rounded-full border {{ $color }}">
                                {{ $role }}
                            </span>
                        </td>
                        <td class="px-4 sm:px-6 py-4 hidden md:table-cell">
                            @if($staff->username)
                                <div class="text-xs text-slate-600 font-medium dark:text-slate-400">@{{ $staff->username }}</div>
                            @endif
                            <div class="text-xs font-mono text-slate-400 mt-0.5">PIN: {{ $staff->pin ?: '—' }}</div>
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-right">
                            @if(auth()->user()->role === 'super_admin')
                                @if($staff->role !== 'super_admin')
                                <div class="flex justify-end gap-2">
                                    <button wire:click="editStaff({{ $staff->id }})" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    <button onclick="confirm('Are you sure you want to remove {{ $staff->full_name }}?') || event.stopImmediatePropagation()" wire:click="deleteStaff({{ $staff->id }})" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                                @else
                                <span class="text-[10px] text-slate-400 font-bold">🔒 Protected</span>
                                @endif
                            @else
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Read-only</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                            <div class="flex flex-col items-center">
                                <svg class="w-10 h-10 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                <p class="font-semibold text-slate-600 dark:text-slate-400">No staff members yet</p>
                                <p class="text-xs text-slate-400 mt-1">Click "Add Staff Member" to get started.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Printer Info Card -->
    <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-2xl p-5 text-white shadow-lg">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            </div>
            <div>
                <h3 class="font-bold text-sm text-white/90">Print Identity</h3>
                <p class="text-xs text-slate-400 mt-1">Each receipt, KOT, and hotel bill will automatically print <strong class="text-rose-400">"By [First Name] [Last Name]"</strong> of the staff who took the order, along with the restaurant/company name from Settings.</p>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 bg-slate-900/60 backdrop-blur-sm" wire:click.self="closeModal">
        <div class="bg-white rounded-3xl w-full max-w-2xl shadow-2xl flex flex-col overflow-hidden max-h-[90vh] dark:bg-[#0a0a0a]">
            <!-- Modal Header -->
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-white flex-shrink-0 dark:border-slate-800 dark:bg-slate-900">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-slate-200">{{ $isEditing ? 'Edit Staff Member' : 'Invite Staff Member' }}</h3>
                    <p class="text-xs text-slate-400 mt-1">{{ $isEditing ? 'Update staff profile and access' : 'Fill in the details to create a new team member' }}</p>
                </div>
                <button wire:click="closeModal" class="p-2 text-slate-400 hover:bg-slate-200 rounded-full transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <!-- Modal Body (Scrollable) -->
            <div class="p-6 overflow-y-auto space-y-6 flex-1 min-h-0">

                @if(session()->has('error'))
                    <div class="p-4 bg-red-100 border border-red-200 text-red-700 rounded-xl flex items-start gap-3">
                        <svg class="w-5 h-5 shrink-0 mt-0.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <div class="font-bold text-sm">{{ session('error') }}</div>
                    </div>
                @endif

                
                @if(!$isEditing)
                <!-- INVITATION UI -->
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-6 h-6 bg-rose-100 text-rose-600 rounded-lg flex items-center justify-center dark:bg-rose-900/30 dark:text-rose-400">
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
                            <select wire:model.live="role" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-transparent dark:bg-[#111] text-slate-800 dark:text-white outline-none transition-all focus:ring-2 focus:ring-slate-900 focus:border-slate-900 dark:border-[#333]">
                                <option value="">Select a role...</option>
                                @foreach($this->availableRoles as $r)
                                    <option value="{{ $r }}">{{ ucfirst($r) }}</option>
                                @endforeach
                            </select>
                            @error('role') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
<div class="mt-4">
{{-- Page Access Control — super_admin can configure for any role --}}
                        @if(auth()->user()->role === 'super_admin' && $role && $role !== 'super_admin')
                        <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-200 space-y-3 dark:bg-indigo-900/20">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-black uppercase tracking-widest text-indigo-400">Page Access Control</h4>
                                <div class="flex gap-2">
                                    <button type="button" wire:click="selectAllPages" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 underline">Select All</button>
                                    <span class="text-slate-300">|</span>
                                    <button type="button" wire:click="deselectAllPages" class="text-[10px] font-bold text-red-500 hover:text-red-700 underline">Deselect All</button>
                                </div>
                            </div>
                            <p class="text-[10px] text-indigo-500">Check which pages this user can access. Unchecked pages will be hidden from their sidebar.</p>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($allPages as $pageKey => $pageLabel)
                                <label class="flex items-center gap-2 p-2 rounded-lg cursor-pointer transition-all {{ in_array($pageKey, $allowedPages) ? 'bg-indigo-100 border border-indigo-300' : 'bg-white border border-slate-200 hover:bg-slate-50' }} dark:bg-[#0a0a0a] dark:border-[#333]">
                                    <input type="checkbox" wire:click="togglePage('{{ $pageKey }}')" {{ in_array($pageKey, $allowedPages) ? 'checked' : '' }} class="w-3.5 h-3.5 text-indigo-600 rounded">
                                    <span class="text-xs font-semibold {{ in_array($pageKey, $allowedPages) ? 'text-indigo-800' : 'text-slate-500' }} dark:text-slate-400 dark:text-indigo-300">{{ $pageLabel }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endif
</div>

                @else
                <!-- EDIT UI -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Left Column -->
                    <div class="space-y-8">
                        <!-- Section 1: Personal Information -->
                        <div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-6 h-6 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center dark:text-blue-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide dark:text-slate-300">Personal Information</h4>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1 dark:text-slate-300">First Name <span class="text-red-400">*</span></label>
                                <input type="text" wire:model="firstName" placeholder="e.g. Ram" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-slate-900 focus:border-slate-900 bg-transparent dark:bg-[#111] text-slate-800 dark:text-white outline-none transition-all dark:border-[#333]">
                                @error('firstName') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1 dark:text-slate-300">Last Name <span class="text-red-400">*</span></label>
                                <input type="text" wire:model="lastName" placeholder="e.g. Sharma" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-slate-900 focus:border-slate-900 bg-transparent dark:bg-[#111] text-slate-800 dark:text-white outline-none transition-all dark:border-[#333]">
                                @error('lastName') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1 dark:text-slate-300">Email <span class="text-red-400">*</span></label>
                                <input type="email" wire:model="email" placeholder="staff@restaurant.com" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-slate-900 focus:border-slate-900 bg-transparent dark:bg-[#111] text-slate-800 dark:text-white outline-none transition-all dark:border-[#333]">
                                @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1 dark:text-slate-300">Alternative Email</label>
                                <input type="email" wire:model="altEmail" placeholder="personal@email.com" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-slate-900 focus:border-slate-900 bg-transparent dark:bg-[#111] text-slate-800 dark:text-white outline-none transition-all dark:border-[#333]">
                                @error('altEmail') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1 dark:text-slate-300">Address</label>
                            <input type="text" wire:model="staffAddress" placeholder="e.g. Lakeside, Pokhara" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-slate-900 focus:border-slate-900 bg-transparent dark:bg-[#111] text-slate-800 dark:text-white outline-none transition-all dark:border-[#333]">
                            @error('staffAddress') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1 dark:text-slate-300">Phone Number</label>
                                <input type="tel" wire:model.live="staffPhone" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" placeholder="e.g. 9800000000" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-slate-900 focus:border-slate-900 bg-transparent dark:bg-[#111] text-slate-800 dark:text-white outline-none transition-all dark:border-[#333]">
                                @error('staffPhone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div></div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Login Credentials -->
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-6 h-6 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center dark:text-amber-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                        </div>
                        <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide dark:text-slate-300">Login Credentials</h4>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1 dark:text-slate-300">Username</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">@</span>
                                <input type="text" wire:model="username" placeholder="ramstaff" class="w-full pl-8 pr-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-slate-900 focus:border-slate-900 bg-transparent dark:bg-[#111] text-slate-800 dark:text-white outline-none transition-all dark:border-[#333]">
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1">Can be used for login instead of email</p>
                            @error('username') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1 dark:text-slate-300">Password {{ $isEditing ? '' : '*' }}</label>
                                <input type="password" wire:model="password" placeholder="{{ $isEditing ? 'Leave blank to keep' : '••••••' }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-slate-900 focus:border-slate-900 bg-transparent dark:bg-[#111] text-slate-800 dark:text-white outline-none transition-all dark:border-[#333]">
                                @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1 dark:text-slate-300">Re-enter Password {{ $isEditing ? '' : '*' }}</label>
                                <input type="password" wire:model="passwordConfirmation" placeholder="••••••" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-slate-900 focus:border-slate-900 bg-transparent dark:bg-[#111] text-slate-800 dark:text-white outline-none transition-all dark:border-[#333]">
                                @error('passwordConfirmation') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1 dark:text-slate-300">PIN Login</label>
                            <input type="text" wire:model="pin" placeholder="e.g. 1234" maxlength="4" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-slate-900 focus:border-slate-900 bg-transparent dark:bg-[#111] text-slate-800 dark:text-white outline-none transition-all font-mono tracking-widest text-center text-lg dark:border-[#333]">
                            <p class="text-[10px] text-slate-400 mt-1">Quick PIN login for POS terminals</p>
                            @error('pin') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>
                    <!-- Right Column -->
                    <div class="space-y-8">
                        <!-- Section 3: Access & Description -->
                        <div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-6 h-6 bg-rose-100 text-rose-600 rounded-lg flex items-center justify-center dark:text-rose-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                        <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide dark:text-slate-300">Access & Description</h4>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1 dark:text-slate-300">Assign Role / Access <span class="text-red-400">*</span></label>
                            <select wire:model.live="role" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-slate-900 focus:border-slate-900 bg-transparent dark:bg-[#111] text-slate-800 dark:text-white outline-none transition-all appearance-none bg-white dark:bg-[#0a0a0a] dark:border-[#333]">
                                <option value="">Select a role...</option>
                                @foreach($this->availableRoles as $r)
                                    <option value="{{ $r }}">{{ ucfirst($r) }}</option>
                                @endforeach
                            </select>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @php
                                    $roleDescs = [
                                        'super_admin' => 'Full unrestricted access. Developer/owner level.',
                                        'admin' => 'Access to pages enabled by Super Admin.',
                                        'manager' => 'Access to pages enabled by Super Admin.',
                                        'waiter' => 'Take restaurant orders, view table status.',
                                        'cashier' => 'Process payments, view invoices, print receipts.',
                                        'hotel' => 'Room service, hotel reception, room booking.',
                                        'receptionist' => 'Hotel front desk, room booking, check-in/check-out.',
                                        'kitchen' => 'View incoming orders and mark them as ready.',
                                        'bar' => 'View bar-specific orders and mark them as ready.',
                                    ];
                                @endphp
                                @foreach($this->availableRoles as $r)
                                    <span class="inline-flex items-center gap-1 text-[10px] px-2 py-1 rounded-lg {{ $role === $r ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-500' }} transition-all cursor-default dark:text-slate-400 dark:bg-slate-800" title="{{ $roleDescs[$r] ?? '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $r)) }}
                                    </span>
                                @endforeach
                            </div>
                            @error('role') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Page Access Control — super_admin can configure for any role --}}
                        @if(auth()->user()->role === 'super_admin' && $role && $role !== 'super_admin')
                        <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-200 space-y-3 dark:bg-indigo-900/20">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-black uppercase tracking-widest text-indigo-400">Page Access Control</h4>
                                <div class="flex gap-2">
                                    <button type="button" wire:click="selectAllPages" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 underline">Select All</button>
                                    <span class="text-slate-300">|</span>
                                    <button type="button" wire:click="deselectAllPages" class="text-[10px] font-bold text-red-500 hover:text-red-700 underline">Deselect All</button>
                                </div>
                            </div>
                            <p class="text-[10px] text-indigo-500">Check which pages this user can access. Unchecked pages will be hidden from their sidebar.</p>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($allPages as $pageKey => $pageLabel)
                                <label class="flex items-center gap-2 p-2 rounded-lg cursor-pointer transition-all {{ in_array($pageKey, $allowedPages) ? 'bg-indigo-100 border border-indigo-300' : 'bg-white border border-slate-200 hover:bg-slate-50' }} dark:bg-[#0a0a0a] dark:border-[#333]">
                                    <input type="checkbox" wire:click="togglePage('{{ $pageKey }}')" {{ in_array($pageKey, $allowedPages) ? 'checked' : '' }} class="w-3.5 h-3.5 text-indigo-600 rounded">
                                    <span class="text-xs font-semibold {{ in_array($pageKey, $allowedPages) ? 'text-indigo-800' : 'text-slate-500' }} dark:text-slate-400 dark:text-indigo-300">{{ $pageLabel }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1 dark:text-slate-300">Description</label>
                            <textarea wire:model="description" rows="3" placeholder="e.g. Senior waiter, handles VIP tables" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-slate-900 focus:border-slate-900 bg-transparent dark:bg-[#111] text-slate-800 dark:text-white outline-none transition-all resize-none dark:border-[#333]"></textarea>
                            <p class="text-[10px] text-slate-400 mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                This description is for internal reference only.
                            </p>
                            @error('description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                
                    </div>

                
                </div>

                
                @endif

                
                <!-- Print Preview Badge -->
                @if($firstName || $lastName)
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 dark:bg-[#0a0a0a] dark:border-[#333]">
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mb-2 dark:text-slate-400">Receipt Print Preview</p>
                    <div class="bg-white rounded-lg p-3 border border-dashed border-slate-300 font-mono text-xs text-center text-slate-700 space-y-1 dark:bg-[#0a0a0a] dark:border-slate-600 dark:text-slate-300">
                        <div class="font-bold">{{ current_restaurant()->name ?? 'Restaurant Name' }}</div>
                        <div class="text-slate-400">- - - - - - - - - - -</div>
                        <div>By: <strong>{{ trim(($firstName ?? '') . ' ' . ($lastName ?? '')) }}</strong></div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Modal Footer -->
            <div class="p-6 border-t border-slate-100 bg-slate-50 flex gap-3 justify-end mt-auto flex-shrink-0 dark:border-[#222] dark:bg-[#0a0a0a]">
                <button wire:click="closeModal" class="px-6 py-2.5 font-bold text-slate-600 hover:bg-slate-200 rounded-xl transition-all dark:text-slate-400">
                    Cancel
                </button>
                <button wire:click="{{ $isEditing ? 'saveStaff' : 'inviteStaff' }}" class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow-sm transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ $isEditing ? 'Update Staff' : 'Send Invitation' }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

EOT;

file_put_contents($target_file, $content);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Clear caches
\Illuminate\Support\Facades\Artisan::call('view:clear');
\Illuminate\Support\Facades\Artisan::call('cache:clear');

// Clear OPCache if enabled
if (function_exists('opcache_reset')) {
    opcache_reset();
}

echo "<h1>Deployment Successful!</h1>";
echo "<p>staff-manager.blade.php has been updated.</p>";
echo "<p>View Cache Cleared.</p>";
echo "<p>OPCache Cleared.</p>";
echo "<p>Please check your phone and desktop now!</p>";
