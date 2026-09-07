<?php
$blade_file = __DIR__ . '/../resources/views/livewire/admin/staff-manager.blade.php';
$controller_file = __DIR__ . '/../app/Livewire/Admin/StaffManager.php';
$login_file = __DIR__ . '/../resources/views/auth/login.blade.php';
$auth_controller_file = __DIR__ . '/../app/Http/Controllers/AuthController.php';
$web_routes_file = __DIR__ . '/../routes/web.php';

$blade_content = <<< 'EOT'

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
        <div class="bg-white rounded-3xl w-full max-w-4xl shadow-2xl flex flex-col overflow-hidden max-h-[90vh] dark:bg-[#0a0a0a]">
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
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach($this->availableRoles as $r)
                                    <button type="button" wire:click="$set('role', '{{ $r }}')" class="inline-flex items-center gap-1 text-[11px] px-2.5 py-1 rounded-lg {{ $role === $r ? 'bg-slate-900 text-white font-bold dark:bg-white dark:text-slate-900' : 'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700' }} transition-all cursor-pointer">
                                        {{ ucfirst(str_replace('_', ' ', $r)) }}
                                    </button>
                                @endforeach
                            </div>
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
                                    <button type="button" wire:click="$set('role', '{{ $r }}')" class="inline-flex items-center gap-1 text-[11px] px-2.5 py-1 rounded-lg {{ $role === $r ? 'bg-slate-900 text-white font-bold dark:bg-white dark:text-slate-900' : 'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700' }} transition-all cursor-pointer" title="{{ $roleDescs[$r] ?? '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $r)) }}
                                    </button>
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

$controller_content = <<< 'EOT'
<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StaffManager extends Component
{
    public $staffMembers;
    public $activeLicense;
    
    // Form fields
    public $staffId;
    public $firstName;
    public $lastName;
    public $email;
    public $altEmail;
    public $staffAddress;
    public $staffPhone;
    public $username;
    public $password;
    public $passwordConfirmation;
    public $pin;
    public $role;
    public $description;
    public $allowedPages = [];

    public $showModal = false;
    public $isEditing = false;

    // All available pages for access control
    public $allPages = [
        'dashboard' => 'Dashboard',
        'menu_manager' => 'Menu Manager',
        'restaurant_tables' => 'Restaurant Tables',
        'hotel_room_manager' => 'Hotel Room Manager',
        'inventory' => 'Inventory',
        'take_order' => 'Take Restaurant Order',
        'take_room_service' => 'Take Room Service',
        'hotel_reception' => 'Hotel Reception Cashier',
        'cashier_panel' => 'Cashier Dashboard',
        'waiter_dashboard' => 'Waiter Dashboard',
        'kitchen_display' => 'Kitchen Display',
        'bar_display' => 'Bar Display',
        'staff_roles' => 'Staff & Roles',
        'reports' => 'Reports & Analytics',
        'license' => 'License & Billing',
        'settings' => 'Settings',
    ];

    // Available roles (super_admin only visible to super_admins)
    public function getAvailableRolesProperty()
    {
        $roles = ['admin', 'manager', 'waiter', 'cashier', 'hotel', 'receptionist', 'kitchen', 'bar'];
        if (auth()->user()->role === 'super_admin') {
            array_unshift($roles, 'super_admin');
        }
        return $roles;
    }

    public function mount()
    {
        $this->activeLicense = current_restaurant()?->license_data ?? \App\Services\LicenseManager::getFreeLimits();
        $this->loadData();
    }

    /**
     * Livewire lifecycle hook: sanitize staffPhone on every update.
     */
    public function updatedStaffPhone($value)
    {
        $this->staffPhone = substr(preg_replace('/[^0-9]/', '', $value), 0, 10);
    }

    public function loadData()
    {
        $currentUser = auth()->user();
        if ($currentUser->role === 'super_admin') {
            $this->staffMembers = User::where('restaurant_id', $currentUser->restaurant_id)->get();
        } else {
            // Admins can't see super_admin users
            $this->staffMembers = User::where('restaurant_id', $currentUser->restaurant_id)
                ->where('role', '!=', 'super_admin')
                ->get();
        }
    }

    public function openModal()
    {
        if (auth()->user()->role !== 'super_admin') return;
        $this->resetFields();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function editStaff($id)
    {
        if (auth()->user()->role !== 'super_admin') return;
        $this->resetFields();
        $staff = User::find($id);
        if ($staff) {
            // Non-super_admins can't edit super_admin users
            if ($staff->role === 'super_admin' && auth()->user()->role !== 'super_admin') {
                return;
            }

            $this->staffId = $staff->id;
            $this->firstName = $staff->first_name;
            $this->lastName = $staff->last_name;
            $this->email = $staff->email;
            $this->altEmail = $staff->alt_email;
            $this->staffAddress = $staff->address;
            $this->staffPhone = $staff->phone;
            $this->username = $staff->username;
            $this->pin = $staff->pin;
            $this->role = $staff->role;
            $this->description = $staff->description;
            $this->allowedPages = $staff->allowed_pages ?? [];
            
            $this->isEditing = true;
            $this->showModal = true;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function resetFields()
    {
        $this->staffId = null;
        $this->firstName = '';
        $this->lastName = '';
        $this->email = '';
        $this->altEmail = '';
        $this->staffAddress = '';
        $this->staffPhone = '';
        $this->username = '';
        $this->password = '';
        $this->passwordConfirmation = '';
        $this->pin = '';
        $this->role = '';
        $this->description = '';
        $this->allowedPages = [];
    }

    public function generatePin()
    {
        $this->pin = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    public function togglePage($page)
    {
        if (in_array($page, $this->allowedPages)) {
            $this->allowedPages = array_values(array_diff($this->allowedPages, [$page]));
        } else {
            $this->allowedPages[] = $page;
        }
    }

    
    public function updatedRole($value)
    {
        $presets = [
            'super_admin' => ['dashboard', 'menu_manager', 'restaurant_tables', 'hotel_room_manager', 'inventory', 'take_order', 'take_room_service', 'hotel_reception', 'cashier_panel', 'waiter_dashboard', 'kitchen_display', 'bar_display', 'staff_roles', 'reports', 'license', 'settings'],
            'admin' => ['dashboard', 'menu_manager', 'restaurant_tables', 'hotel_room_manager', 'inventory', 'take_order', 'take_room_service', 'hotel_reception', 'cashier_panel', 'waiter_dashboard', 'kitchen_display', 'bar_display', 'staff_roles', 'reports', 'license', 'settings'],
            'manager' => ['dashboard', 'menu_manager', 'restaurant_tables', 'hotel_room_manager', 'inventory', 'take_order', 'take_room_service', 'hotel_reception', 'cashier_panel', 'waiter_dashboard', 'kitchen_display', 'bar_display', 'staff_roles', 'reports'],
            'waiter' => ['restaurant_tables', 'take_order', 'waiter_dashboard'],
            'cashier' => ['cashier_panel', 'take_order', 'restaurant_tables'],
            'hotel' => ['hotel_room_manager', 'take_room_service', 'hotel_reception'],
            'receptionist' => ['hotel_room_manager', 'hotel_reception', 'take_room_service'],
            'kitchen' => ['kitchen_display'],
            'bar' => ['bar_display']
        ];
        
        if (array_key_exists($value, $presets)) {
            $this->allowedPages = $presets[$value];
        } else {
            $this->allowedPages = [];
        }
    }

    public function selectAllPages()
    {
        $this->allowedPages = array_keys($this->allPages);
    }

    public function deselectAllPages()
    {
        $this->allowedPages = [];
    }

    public function saveStaff()
    {
        if (auth()->user()->role !== 'super_admin') return;

        $rules = [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->staffId,
            'altEmail' => 'nullable|email',
            'staffAddress' => 'nullable|string|max:500',
            'staffPhone' => 'nullable|string|max:10',
            'username' => 'nullable|string|max:100|unique:users,username,' . $this->staffId,
            'pin' => [
                'nullable',
                'string',
                'max:4',
                \Illuminate\Validation\Rule::unique('users', 'pin')
                    ->ignore($this->staffId)
                    ->where('restaurant_id', auth()->user()->restaurant_id)
            ],
            'role' => 'required|string',
            'description' => 'nullable|string|max:500',
        ];

        if (!$this->isEditing) {
            $rules['password'] = 'required|min:6';
            $rules['passwordConfirmation'] = 'required|same:password';
        } else {
            if (!empty($this->password)) {
                $rules['password'] = 'min:6';
                $rules['passwordConfirmation'] = 'required|same:password';
            }
        }

        $this->validate($rules, [
            'passwordConfirmation.same' => 'Passwords do not match.',
            'passwordConfirmation.required' => 'Please re-enter the password.',
        ]);

        // Prevent non-super_admins from creating super_admin users
        if ($this->role === 'super_admin' && auth()->user()->role !== 'super_admin') {
            $this->role = 'admin';
        }

        $fullName = trim($this->firstName . ' ' . $this->lastName);

        $data = [
            'name' => $fullName,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'alt_email' => $this->altEmail ?: null,
            'address' => $this->staffAddress ?: null,
            'phone' => $this->staffPhone ?: null,
            'username' => $this->username ?: null,
            'pin' => $this->pin,
            'role' => $this->role,
            'description' => $this->description ?: null,
            'allowed_pages' => !empty($this->allowedPages) ? $this->allowedPages : null,
        ];

        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->isEditing) {
            $staff = User::find($this->staffId);
            $staff->update($data);
        } else {
            // Check limits for new users (bypass for super_admin)
            $limit = $this->activeLicense['limits']['users'] ?? 0;
            if ($limit > 0 && auth()->user()->role !== 'super_admin' && User::count() >= $limit) {
                session()->flash('error', "Limit reached: Your plan allows a maximum of {$limit} users. Please upgrade to add more.");
                return;
            }
            User::create($data);
        }

        $this->closeModal();
        $this->loadData();
    }

    public function inviteStaff()
    {
        if (auth()->user()->role !== 'super_admin') return;

        $this->validate([
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string',
        ]);

        if ($this->role === 'super_admin' && auth()->user()->role !== 'super_admin') {
            $this->role = 'admin';
        }

        $limit = $this->activeLicense['limits']['users'] ?? 0;
        if ($limit > 0 && auth()->user()->role !== 'super_admin' && User::count() >= $limit) {
            session()->flash('error', "Limit reached: Your plan allows a maximum of {$limit} users. Please upgrade to add more.");
            return;
        }

        $token = \Illuminate\Support\Str::random(60);
        
        $user = User::create([
            'name' => 'Pending Invite',
            'first_name' => 'Pending',
            'last_name' => 'Invite',
            'email' => $this->email,
            'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)),
            'role' => $this->role,
            'restaurant_id' => auth()->user()->restaurant_id, // Important if tenancy uses this directly
            'allowed_pages' => !empty($this->allowedPages) ? $this->allowedPages : null,
        ]);
        
        $user->remember_token = $token;
        $user->save();

        
        $inviteUrl = url('/invite/' . $token);
        
        try {
            $restaurant = auth()->user()->restaurant;
            $restaurantName = $restaurant ? $restaurant->name : 'DRestro POS';
            $roleName = ucfirst(str_replace('_', ' ', $this->role));
            
            $html = "
            <div style='font-family: Arial, sans-serif; background-color: #f8fafc; padding: 20px;'>
                <div style='max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);'>
                    <h2 style='color: #1e293b;'>You're Invited!</h2>
                    <p style='color: #475569; font-size: 16px;'>You have been invited to join <strong>{$restaurantName}</strong> as a <strong>{$roleName}</strong>.</p>
                    <p style='color: #475569; font-size: 16px;'>Click the button below to accept your invitation and securely set up your account.</p>
                    
                    <a href='{$inviteUrl}' style='display: inline-block; background-color: #10b981; color: white; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; margin-top: 20px;'>Accept Invitation</a>
                    
                    <p style='margin-top: 30px; font-size: 12px; color: #94a3b8;'>If you didn't expect this invitation, you can safely ignore this email.</p>
                </div>
            </div>
            ";
            
            \Illuminate\Support\Facades\Mail::html($html, function ($message) use ($restaurantName) {
                $message->to($this->email)
                        ->subject("You're invited to join {$restaurantName}!");
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send invitation email: " . $e->getMessage());
        }

        
        $this->closeModal();
        $this->loadData();
        
        session()->flash('invite_link', $inviteUrl);
        session()->flash('success', "Invitation created successfully!");
    }

    public function deleteStaff($id)
    {
        if (auth()->user()->role !== 'super_admin') return;

        $staff = User::find($id);
        if ($staff && $staff->id !== auth()->id()) {
            $staff->delete();
            $this->loadData();
        }
    }

    public function render()
    {
        return view('livewire.admin.staff-manager')->layout('components.layouts.app', ['title' => 'Staff & Roles']);
    }
}

EOT;

$login_content = <<< 'EOT'
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - Drestro POS</title>
        <!-- Offline Fonts -->
        <link rel="stylesheet" href="{{ asset('fonts/inter/inter.css') }}">
        @vite(['resources/css/app.css'])
    </head>
    <body class="bg-slate-50 font-[Inter] antialiased text-slate-800 flex items-center justify-center min-h-screen relative overflow-hidden dark:bg-slate-900 dark:text-slate-200">
        <!-- Abstract Background -->
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-500 rounded-full blur-3xl opacity-10"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-teal-500 rounded-full blur-3xl opacity-10"></div>

        <div class="w-full max-w-md bg-white/80 backdrop-blur-xl p-8 sm:p-10 rounded-3xl shadow-[0_8px_40px_-12px_rgba(0,0,0,0.1)] border border-slate-200/50 z-10 mx-4 dark:bg-slate-900/80">
            <div class="text-center mb-8">
                    <img src="{{ asset('images/logo.svg') }}" class="h-10 w-auto mx-auto mb-3 object-contain dark:hidden" alt="DRestro Logo">
                    <img src="{{ asset('images/logo-light.svg') }}" class="h-10 w-auto mx-auto mb-3 object-contain hidden dark:block" alt="DRestro Logo">
                <p class="text-slate-500 font-medium mt-1 dark:text-slate-400">Sign in to manage your restaurant</p>
            </div>

            <form method="POST" action="{{ route('login.post') }}" class="space-y-6">
                @csrf
                @if($errors->any())
                    <div class="bg-red-50 text-red-600 p-4 rounded-2xl text-sm font-semibold border border-red-100 flex items-start gap-3 dark:border-red-800/50 dark:text-red-400 dark:bg-red-900/20">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-red-50 text-red-600 p-4 rounded-2xl text-sm font-semibold border border-red-100 flex items-start gap-3 dark:border-red-800/50 dark:text-red-400 dark:bg-red-900/20">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide dark:text-slate-300">Email or Username</label>
                    <input type="text" name="login" value="{{ old('login') }}" required autofocus class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white outline-none transition-all font-medium placeholder-slate-400 dark:bg-slate-900 dark:border-slate-700" placeholder="admin@drestro.com or username">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide dark:text-slate-300">Password</label>
                    <input type="password" name="password" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white outline-none transition-all font-medium placeholder-slate-400 dark:bg-slate-900 dark:border-slate-700" placeholder="••••••••">
                </div>

                <button type="submit" class="w-full py-4 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white rounded-2xl font-bold shadow-[0_8px_20px_-8px_rgba(16,185,129,0.5)] hover:shadow-[0_8px_30px_-8px_rgba(16,185,129,0.6)] hover:-translate-y-0.5 active:scale-[0.98] transition-all text-lg tracking-wide">
                    Sign In
                </button>

                <div class="flex items-center my-4">
                    <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
                    <span class="px-3 text-xs text-slate-400 font-bold uppercase tracking-wider bg-transparent">or</span>
                    <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
                </div>

                <a href="https://drestro.com/login" class="w-full py-3.5 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-2xl font-bold transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Continue with Google
                </a>


            </form>
        </div>
    </body>
</html>

EOT;

$auth_controller_content = <<< 'EOT'
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        // If already logged in, redirect to admin
        if (Auth::check()) {
            return redirect('/admin');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $loginValue = $request->input('login');

        // Determine if the user is logging in via email or username
        $field = filter_var($loginValue, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Always 'Remember Me' so staff stay logged in across sessions
        if (Auth::attempt([$field => $loginValue, 'password' => $request->password], true)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            if (in_array($user->role, ['admin', 'manager', 'super_admin'])) {
                return redirect()->intended('/admin');
            }
            
            $roleMap = [
                'waiter' => '/staff/waiter',
                'cashier' => '/staff/cashier',
                'receptionist' => '/staff/hotel-reception',
                'hotel' => '/staff/room-service',
                'kitchen' => '/staff/kitchen',
                'bar' => '/staff/bar'
            ];
            
            return redirect()->intended($roleMap[$user->role] ?? '/login');
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ])->onlyInput('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}

EOT;

$web_routes_content = <<< 'EOT'
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/pin-login', App\Livewire\Auth\PinLogin::class)->name('pin-login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// SSO Route for Next.js Bridge
Route::get('/sso/login', [\App\Http\Controllers\SSOController::class, 'login'])->name('sso.login');

Route::get('/debug-user', function() {
    return [
        'logged_in_user' => auth()->user() ? auth()->user()->toArray() : null,
        'users_all' => \App\Models\User::withoutGlobalScopes()->get()->toArray(),
        'restaurants_all' => \App\Models\Restaurant::all()->toArray(),
        'current_restaurant' => current_restaurant() ? current_restaurant()->toArray() : null,
    ];
});

Route::get('/', function () {
    return redirect('/login');
});

// Digital Menu (Public)
Route::get('/menu', App\Livewire\Customer\DigitalMenu::class)->name('menu');

// Image Proxy (Fix for broken Windows symlinks)
Route::get('/storage/{folder}/{filename}', function ($folder, $filename) {
    $path = storage_path("app/public/{$folder}/{$filename}");
    if (!file_exists($path)) abort(404);
    
    $extension = pathinfo($path, PATHINFO_EXTENSION);
    $mimeTypes = [
        'webp' => 'image/webp',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
    ];
    
    return response()->file($path, [
        'Content-Type' => $mimeTypes[strtolower($extension)] ?? 'image/jpeg',
        'Cache-Control' => 'public, max-age=86400'
    ]);
})->where('folder', 'menu_items|categories|restaurant|logos')->where('filename', '.*');

Route::middleware(['auth'])->group(function () {
    
    // Toggle calendar type dynamically (AD/BS) - Super Admin Only!
    Route::get('/toggle-calendar/{type}', function ($type) {
        if (auth()->user()->role === 'super_admin' && in_array($type, ['ad', 'bs'])) {
            $restaurant = current_restaurant();
            if ($restaurant) {
                $restaurant->date_calendar_type = strtoupper($type);
                $restaurant->save();
            }
        }
        return back();
    })->name('toggle-calendar');

    // Admin Routes
    Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
        Route::get('/', App\Livewire\Admin\Dashboard::class)->name('dashboard');
        
        // These pages are ALWAYS accessible for admins
        Route::get('/license', App\Livewire\Admin\LicenseManager::class)->name('license');
        Route::get('/settings', App\Livewire\Admin\Settings::class)->name('settings');
        
        // License activation must be accessible even when license is invalid
        Route::post('/license/activate', function (Illuminate\Http\Request $request) {
            $key = trim($request->input('license_key'));
            $restaurant = current_restaurant();
            
            if ($key === 'CLEAR') {
                $restaurant->update(['license_key' => null, 'license_data' => null]);
                \Illuminate\Support\Facades\Cache::flush();
                return redirect()->route('admin.license')->with('success', 'System Reset: All errors cleared.');
            }
            
            $parts = explode('.', $key);
            if (count($parts) === 2) {
                $payload = json_decode(base64_decode($parts[0]), true);
                $signature = $parts[1];
                $expected = hash_hmac('sha256', $parts[0], 'DrestroPOS_Secure_Key_2026_X9P2');
                
                if (hash_equals($expected, $signature)) {
                    $plan = $payload['plan'] ?? 'Premium';
                    $restaurant->update([
                        'license_key' => $key,
                        'license_data' => $payload,
                        'machine_id' => $payload['machine_id'] ?? 'UNIVERSAL'
                    ]);
                    
                    \Illuminate\Support\Facades\Cache::flush();
                    return redirect()->route('admin.license')->with('success', "✅ Activation Successful! Welcome to $plan.");
                }
            }
            return back()->with('error', '❌ Invalid License Key Signature.');
        })->name('license.activate');

        // Note: CheckLicense middleware is applied globally in bootstrap/app.php
        Route::get('/menus', App\Livewire\Admin\MenuManager::class)->name('menus');
        Route::get('/tables', App\Livewire\Admin\TableManager::class)->name('tables');
        Route::get('/rooms', App\Livewire\Admin\RoomManager::class)->name('rooms');
        Route::get('/inventory', App\Livewire\Admin\InventoryManager::class)->name('inventory');
        Route::get('/staff', App\Livewire\Admin\StaffManager::class)->name('staff');
        Route::get('/reports', App\Livewire\Admin\ReportsManager::class)->name('reports');
        Route::get('/support', App\Livewire\Admin\SupportTickets::class)->name('support');
        
        // SAFE CLEANUP ROUTE FOR CLIENT DELIVERY
        Route::get('/clean-database', function() {
            try {
                \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = OFF');
                \Illuminate\Support\Facades\DB::table('order_items')->delete();
                \Illuminate\Support\Facades\DB::table('orders')->delete();
                \Illuminate\Support\Facades\DB::table('invoices')->delete();
                \Illuminate\Support\Facades\DB::table('payments')->delete();
                \Illuminate\Support\Facades\DB::table('inventory_transactions')->delete();
                \Illuminate\Support\Facades\DB::table('notifications')->delete();
                \Illuminate\Support\Facades\DB::statement("DELETE FROM sqlite_sequence WHERE name IN ('order_items', 'orders', 'invoices', 'payments', 'inventory_transactions', 'notifications')");
                
                \App\Models\Table::query()->update([
                    'status' => 'available',
                    'room_status' => 'available',
                    'guest_name' => null,
                    'guest_phone' => null,
                    'check_in_at' => null,
                    'current_order_id' => null
                ]);
                
                \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = ON');
                \Illuminate\Support\Facades\Cache::flush();
                return "✅ SUCCESS: All test orders, invoices, and payments have been wiped clean! The menu and settings are preserved. Ready for your client!";
            } catch (\Exception $e) {
                return "❌ ERROR: " . $e->getMessage();
            }
        })->name('clean-database');
    });

    // Staff Panel Routes
    Route::group(['prefix' => 'staff', 'as' => 'staff.'], function () {
        Route::get('/take-order', App\Livewire\Staff\WaiterOrderTaking::class)->name('take-order');
        Route::get('/room-service', App\Livewire\Staff\RoomService::class)->name('room-service');
        Route::get('/cashier', App\Livewire\Staff\CashierPanel::class)->name('cashier');
        Route::get('/waiter', App\Livewire\Staff\WaiterDashboard::class)->name('waiter');
        Route::get('/kitchen', App\Livewire\Staff\KitchenPanel::class)->name('kitchen');
        Route::get('/bar', App\Livewire\Staff\BarPanel::class)->name('bar');
        Route::get('/hotel-reception', App\Livewire\Staff\HotelCashier::class)->name('hotel-reception');
    });
});

// TOTAL FACTORY RESET (USE WITH CAUTION)
Route::get('/admin/factory-reset', function() {
    if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'super_admin'])) {
        return "Access Denied.";
    }

    try {
        \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = OFF');
        
        // List of all transaction and config tables
        $tables = [
            'order_items', 'orders', 'invoices', 'payments', 
            'inventory_transactions', 'inventory_items', 
            'menu_items', 'menu_categories', 'tables', 
            'notifications', 'users', 'restaurants', 'failed_jobs'
        ];

        foreach ($tables as $table) {
            if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                \Illuminate\Support\Facades\DB::table($table)->delete();
                \Illuminate\Support\Facades\DB::statement("DELETE FROM sqlite_sequence WHERE name = '$table'");
            }
        }
        
        // 1. Create Fresh Users
        \App\Models\User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'role' => 'admin',
        ]);
        \App\Models\User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@admin.com',
            'password' => \Illuminate\Support\Facades\Hash::make('superadmin123'),
            'role' => 'super_admin',
        ]);
        
        // 2. Create Fresh Restaurant (No License)
        \App\Models\Restaurant::create([
            'name' => 'Drestro POS',
            'email' => 'admin@admin.com',
            'license_key' => null,
            'license_data' => null
        ]);

        // 3. Clear Storage Images (Categories & Menu Items)
        $folders = ['categories', 'menu_items', 'restaurants'];
        foreach ($folders as $folder) {
            $path = storage_path("app/public/$folder");
            if (file_exists($path)) {
                $files = glob($path . '/*'); 
                foreach($files as $file){
                    if(is_file($file)) unlink($file);
                }
            }
        }
        
        \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = ON');
        \Illuminate\Support\Facades\Cache::flush();
        \Illuminate\Support\Facades\Auth::logout();
        
        return "<h1>🏁 FACTORY RESET COMPLETE</h1>
                <p>The system is now in a 'Fresh Install' state for your client.</p>
                <ul>
                    <li>✅ All Data & Licenses Wiped</li>
                    <li>✅ Storage Images Cleared</li>
                    <li>✅ Default Login Restored: <b>admin@admin.com</b> / <b>admin123</b></li>
                </ul>
                <p><a href='/login'>Go to Login</a></p>";
    } catch (\Exception $e) {
        return "❌ ERROR: " . $e->getMessage();
    }
})->name('factory-reset');


// Staff Invitation Routes (Public)
Route::get('/invite/{token}', function($token) {
    if (auth()->check()) {
        $user = auth()->user();
        if (in_array($user->role, ['admin', 'manager', 'super_admin'])) return redirect('/admin');
        $roleMap = ['waiter' => '/staff/waiter', 'cashier' => '/staff/cashier', 'receptionist' => '/staff/hotel-reception', 'hotel' => '/staff/room-service', 'kitchen' => '/staff/kitchen', 'bar' => '/staff/bar'];
        return redirect($roleMap[$user->role] ?? '/login');
    }
    
    $user = \App\Models\User::withoutGlobalScope('restaurant')->where('remember_token', $token)->first();
    if (!$user) {
        return redirect('/login')->with('error', 'Invalid or expired invitation link. You may have already accepted it.');
    }
    
    $restaurant = \App\Models\Restaurant::find($user->restaurant_id);
    if ($restaurant) {
        session(['tenant_slug' => $restaurant->slug]);
        app()->instance('restaurant', $restaurant);
    }
    
$html = <<<'HTML'
<x-layouts.guest title="Accept Invitation">
    <div class="min-h-screen flex items-center justify-center bg-slate-50 dark:bg-[#0a0a0a] p-4 font-[Inter]">
        <div class="w-full max-w-md bg-white dark:bg-[#111111] rounded-3xl shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="p-8 text-center bg-gradient-to-b from-emerald-50 to-white dark:from-emerald-900/20 dark:to-[#111111] border-b border-slate-100 dark:border-slate-800">
                <div class="w-16 h-16 bg-emerald-500 text-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-500/30">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2-2v10a2 2 0 002 2z"></path></svg>
                </div>
                <h2 class="text-2xl font-black text-slate-800 dark:text-white">You're Invited!</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-2 text-sm">Join <strong class="text-emerald-600 dark:text-emerald-400">{{ $restaurant->name ?? 'DRestro POS' }}</strong> as a {{ ucfirst(str_replace('_', ' ', $user->role)) }}.</p>
            </div>

            <div class="p-8">
                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="/invite/{{ $token }}" method="POST" class="space-y-5">
                    @csrf
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">First Name</label>
                            <input type="text" name="first_name" required placeholder="Ram" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 outline-none transition-all dark:bg-[#1a1a1a] dark:border-slate-800 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">Last Name</label>
                            <input type="text" name="last_name" required placeholder="Sharma" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 outline-none transition-all dark:bg-[#1a1a1a] dark:border-slate-800 dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">Create Password</label>
                        <input type="password" name="password" required placeholder="••••••••" minlength="6" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 outline-none transition-all dark:bg-[#1a1a1a] dark:border-slate-800 dark:text-white">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wider">Confirm Password</label>
                        <input type="password" name="password_confirmation" required placeholder="••••••••" minlength="6" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 outline-none transition-all dark:bg-[#1a1a1a] dark:border-slate-800 dark:text-white">
                    </div>

                    <button type="submit" class="w-full mt-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-emerald-500/30 flex justify-center items-center gap-2">
                        Accept Invitation & Login
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.guest>
HTML;
    return \Illuminate\Support\Facades\Blade::render($html, ['user' => $user, 'token' => $token, 'restaurant' => $restaurant]);

})->name('invite.accept');

Route::post('/invite/{token}', function(\Illuminate\Http\Request $request, $token) {
    if (auth()->check()) {
        $user = auth()->user();
        if (in_array($user->role, ['admin', 'manager', 'super_admin'])) return redirect('/admin');
        $roleMap = ['waiter' => '/staff/waiter', 'cashier' => '/staff/cashier', 'receptionist' => '/staff/hotel-reception', 'hotel' => '/staff/room-service', 'kitchen' => '/staff/kitchen', 'bar' => '/staff/bar'];
        return redirect($roleMap[$user->role] ?? '/login');
    }

    $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'password' => 'required|min:6|confirmed'
    ]);

    $user = \App\Models\User::withoutGlobalScope('restaurant')->where('remember_token', $token)->first();
    if (!$user) {
        return redirect('/login')->with('error', 'Invalid or expired invitation link. You may have already accepted it.');
    }
    
    $restaurant = \App\Models\Restaurant::find($user->restaurant_id);
    if ($restaurant) {
        session(['tenant_slug' => $restaurant->slug]);
        app()->instance('restaurant', $restaurant);
    }

    $user->update([
        'first_name' => $request->first_name,
        'last_name' => $request->last_name,
        'name' => $request->first_name . ' ' . $request->last_name,
        'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        'remember_token' => null // Consume the token
    ]);

    \Illuminate\Support\Facades\Auth::login($user);
    
    // Redirect based on role
    if (in_array($user->role, ['admin', 'manager', 'super_admin'])) {
        return redirect('/admin');
    }
    
    $roleMap = [
        'waiter' => '/staff/waiter',
        'cashier' => '/staff/cashier',
        'receptionist' => '/staff/hotel-reception',
        'hotel' => '/staff/room-service',
        'kitchen' => '/staff/kitchen',
        'bar' => '/staff/bar'
    ];
    
    return redirect($roleMap[$user->role] ?? '/login');
});

EOT;

// Ensure target directories exist
@mkdir(dirname($blade_file), 0755, true);
@mkdir(dirname($controller_file), 0755, true);
@mkdir(dirname($login_file), 0755, true);
@mkdir(dirname($auth_controller_file), 0755, true);
@mkdir(dirname($web_routes_file), 0755, true);

file_put_contents($blade_file, $blade_content);
file_put_contents($controller_file, $controller_content);
file_put_contents($login_file, $login_content);
file_put_contents($auth_controller_file, $auth_controller_content);
file_put_contents($web_routes_file, $web_routes_content);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Clear caches
\Illuminate\Support\Facades\Artisan::call('view:clear');
\Illuminate\Support\Facades\Artisan::call('cache:clear');

// Fix database records for cross-tenant demo accounts
try {
    \Illuminate\Support\Facades\DB::table('users')->where('email', 'pacmhz2004@gmail.com')->update(['restaurant_id' => 4]);
    \Illuminate\Support\Facades\DB::table('users')->where('email', 'deepbalami729@gmail.com')->update(['restaurant_id' => 6]);
    $db_status = "Database records fixed: pacmhz2004@gmail.com set to restaurant 4, deepbalami729@gmail.com set to restaurant 6.";
} catch (\Exception $e) {
    $db_status = "Database fix error: " . $e->getMessage();
}

// Clear OPCache if enabled
if (function_exists('opcache_reset')) {
    opcache_reset();
}

echo "<h1>Deployment Successful!</h1>";
echo "<p>staff-manager.blade.php, StaffManager.php, login.blade.php, AuthController.php, and web.php have been updated.</p>";
echo "<p>Cache and OPCache cleared successfully!</p>";
echo "<p><b>DB Update:</b> " . $db_status . "</p>";
