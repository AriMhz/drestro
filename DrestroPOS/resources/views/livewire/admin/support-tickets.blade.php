<div class="space-y-6">
    <!-- Premium Glassy Header -->
    <div class="bg-white dark:bg-[#111111] p-6 rounded-3xl border border-slate-200/50 dark:border-slate-800/80 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.02)] dark:shadow-none flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="hidden lg:block">
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-2">
                <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Priority Support Tickets
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Submit support requests directly to our technical support team.</p>
        </div>
        
        <button wire:click="$set('showCreateModal', true)" class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl shadow-lg shadow-emerald-500/10 active:scale-95 transition-all text-xs uppercase tracking-wider flex items-center gap-2 cursor-pointer">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            Create New Ticket
        </button>
    </div>

    <!-- Alert Messaging -->
    @if (session()->has('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center gap-3 text-sm font-bold shadow-inner">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Main Tickets Listing Table Card -->
    <div class="bg-white dark:bg-[#111111] rounded-3xl border border-slate-200/50 dark:border-slate-800/80 shadow-[0_10px_30px_-5px_rgba(0,0,0,0.02)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/60 dark:bg-[#151515]/50 border-b border-slate-100 dark:border-slate-850 text-xs font-black uppercase tracking-widest text-slate-400">
                        <th class="px-6 py-4.5">Ticket ID</th>
                        <th class="px-6 py-4.5">Issue Title</th>
                        <th class="px-6 py-4.5">Status</th>
                        <th class="px-6 py-4.5">Priority</th>
                        <th class="px-6 py-4.5">Date Created</th>
                        <th class="px-6 py-4.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/60 dark:divide-[#202020]/60">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-[#151515]/30 transition-all duration-150 group">
                            <td class="px-6 py-5 text-sm font-mono font-bold text-slate-400 dark:text-slate-500">
                                #{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="px-6 py-5">
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-200 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors leading-tight">{{ $ticket->title }}</p>
                            </td>
                            <td class="px-6 py-5">
                                @if($ticket->status === 'OPEN')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-amber-500/10 text-amber-500 border border-amber-500/20">Open</span>
                                @elseif($ticket->status === 'RESOLVED')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">Resolved</span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-indigo-500/10 text-indigo-500 border border-indigo-500/20">{{ $ticket->status }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-5">
                                <span class="px-2.5 py-1 bg-slate-100 dark:bg-[#1e1e1e] rounded-xl text-[10px] font-black uppercase tracking-wider border border-slate-200/50 dark:border-slate-800/80 text-slate-500 dark:text-slate-400">{{ $ticket->priority }}</span>
                            </td>
                            <td class="px-6 py-5 text-xs font-semibold text-slate-400">
                                {{ $ticket->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-5 text-right">
                                @if($ticket->status === 'OPEN')
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button wire:click="editTicket({{ $ticket->id }})" class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-950/30 rounded-xl transition-all border border-transparent hover:border-blue-100 dark:hover:border-blue-900/40 cursor-pointer" title="Edit Ticket">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        <button wire:click="cancelTicket({{ $ticket->id }})" class="p-2 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/30 rounded-xl transition-all border border-transparent hover:border-amber-100 dark:hover:border-amber-900/40 cursor-pointer" title="Cancel Ticket" onclick="confirm('Are you sure you want to cancel this ticket?') || event.stopImmediatePropagation()">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                        <button wire:click="deleteTicket({{ $ticket->id }})" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-xl transition-all border border-transparent hover:border-red-100 dark:hover:border-red-900/40 cursor-pointer" title="Delete Ticket" onclick="confirm('Are you sure you want to permanently delete this ticket?') || event.stopImmediatePropagation()">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                @else
                                    <button wire:click="deleteTicket({{ $ticket->id }})" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-xl transition-all border border-transparent hover:border-red-100 dark:hover:border-red-900/40 cursor-pointer" title="Delete Ticket" onclick="confirm('Are you sure you want to permanently delete this ticket?') || event.stopImmediatePropagation()">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-24 text-center">
                                <div class="flex flex-col items-center max-w-xs mx-auto">
                                    <div class="w-14 h-14 bg-slate-50 dark:bg-[#1c1c1c] border border-slate-200/40 dark:border-slate-800 rounded-3xl flex items-center justify-center text-slate-300 dark:text-slate-600 mb-4 animate-pulse">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    </div>
                                    <p class="text-sm font-bold text-slate-600 dark:text-slate-300">No Support Tickets Found</p>
                                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">If you are encountering any hardware or system issues, click "Create New Ticket" to report it.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Ticket Modal -->
    @if($showCreateModal)
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showCreateModal', false)"></div>
        <div class="relative bg-white dark:bg-[#111111] rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-200/50 dark:border-slate-800/80 transform transition-all duration-300 scale-100">
            <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800/80 flex justify-between items-center bg-slate-50/50 dark:bg-[#161616]/50">
                <h3 class="text-lg font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    Create Support Ticket
                </h3>
                <button type="button" wire:click="$set('showCreateModal', false)" class="p-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-400 dark:text-slate-300 rounded-full transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form wire:submit.prevent="createTicket" class="p-6 space-y-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Subject / Brief Description</label>
                    <input type="text" wire:model="title" class="w-full rounded-2xl border border-slate-200/60 dark:border-slate-800/80 px-4 py-3 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 outline-none transition-all dark:bg-[#171717] dark:text-white placeholder-slate-400 text-sm font-semibold" placeholder="E.g. Thermal printer is not printing receipts" required>
                    @error('title') <span class="text-red-500 text-xs mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Severity & Priority</label>
                    <select wire:model="priority" class="w-full rounded-2xl border border-slate-200/60 dark:border-slate-800/80 px-4 py-3 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 outline-none transition-all dark:bg-[#171717] dark:text-white text-sm font-semibold">
                        <option value="LOW">Low - General Question / Feature Idea</option>
                        <option value="MEDIUM">Medium - Performance/Glitch (Workaround exists)</option>
                        <option value="HIGH">High - Operations Impacted (No clear workaround)</option>
                        <option value="URGENT">Urgent - System Down (POS cannot take orders)</option>
                    </select>
                    @error('priority') <span class="text-red-500 text-xs mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Detailed Description</label>
                    <textarea wire:model="description" rows="4" class="w-full rounded-2xl border border-slate-200/60 dark:border-slate-800/80 px-4 py-3 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 outline-none transition-all dark:bg-[#171717] dark:text-white resize-none text-sm font-semibold" placeholder="Please explain the issue step-by-step. Mention any error messages displayed..." required></textarea>
                    @error('description') <span class="text-red-500 text-xs mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                </div>

                <div class="pt-1.5">
                    <label class="flex items-start gap-3.5 p-4 rounded-2xl border border-emerald-500/25 bg-emerald-500/5 dark:border-emerald-900/30 dark:bg-emerald-950/10 cursor-pointer hover:bg-emerald-500/10 transition-colors">
                        <div class="flex items-center h-5 mt-0.5">
                            <input type="checkbox" wire:model="grantAccess" class="w-4 h-4 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500/20 dark:bg-[#171717] dark:border-slate-800">
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-black text-emerald-900 dark:text-emerald-400 flex items-center gap-1.5 uppercase tracking-wide">
                                <svg class="w-4 h-4 text-emerald-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 7a2 2 0 012 2m-2 4a5 5 0 01-7-7m7 7v4a3 3 0 01-3 3H9a3 3 0 01-3-3V9a3 3 0 013-3h4"></path></svg>
                                Grant Diagnostics Remote Access
                            </span>
                            <span class="text-xs text-emerald-700/80 dark:text-emerald-500/80 mt-1 leading-relaxed">Allow support.drestro technicians to temporarily check your local configurations and diagnostics log files to troubleshoot the issue immediately.</span>
                        </div>
                    </label>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80 flex justify-end gap-3">
                    <button type="button" wire:click="$set('showCreateModal', false)" class="px-5 py-3 text-slate-500 dark:text-slate-400 font-bold hover:bg-slate-50 dark:hover:bg-[#1a1a1a] rounded-2xl transition-colors text-xs uppercase tracking-wider cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl shadow-lg shadow-emerald-500/10 transition-all text-xs uppercase tracking-wider flex items-center gap-2 cursor-pointer">
                        <span wire:loading.remove wire:target="createTicket">Submit Support Ticket</span>
                        <span wire:loading wire:target="createTicket" class="flex items-center gap-1.5">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Submitting...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Edit Ticket Modal -->
    @if($showEditModal)
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showEditModal', false)"></div>
        <div class="relative bg-white dark:bg-[#111111] rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-200/50 dark:border-slate-800/80 transform transition-all duration-300 scale-100">
            <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800/80 flex justify-between items-center bg-slate-50/50 dark:bg-[#161616]/50">
                <h3 class="text-lg font-black text-slate-800 dark:text-white tracking-tight">Edit Support Ticket</h3>
                <button type="button" wire:click="$set('showEditModal', false)" class="p-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-400 dark:text-slate-300 rounded-full transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form wire:submit.prevent="updateTicket" class="p-6 space-y-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Subject / Brief Description</label>
                    <input type="text" wire:model="title" class="w-full rounded-2xl border border-slate-200/60 dark:border-slate-800/80 px-4 py-3 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 outline-none transition-all dark:bg-[#171717] dark:text-white text-sm font-semibold" required>
                    @error('title') <span class="text-red-500 text-xs mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Severity & Priority</label>
                    <select wire:model="priority" class="w-full rounded-2xl border border-slate-200/60 dark:border-slate-800/80 px-4 py-3 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 outline-none transition-all dark:bg-[#171717] dark:text-white text-sm font-semibold">
                        <option value="LOW">Low - General Question / Feature Idea</option>
                        <option value="MEDIUM">Medium - Performance/Glitch (Workaround exists)</option>
                        <option value="HIGH">High - Operations Impacted (No clear workaround)</option>
                        <option value="URGENT">Urgent - System Down (POS cannot take orders)</option>
                    </select>
                    @error('priority') <span class="text-red-500 text-xs mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Detailed Description</label>
                    <textarea wire:model="description" rows="4" class="w-full rounded-2xl border border-slate-200/60 dark:border-slate-800/80 px-4 py-3 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 outline-none transition-all dark:bg-[#171717] dark:text-white resize-none text-sm font-semibold" required></textarea>
                    @error('description') <span class="text-red-500 text-xs mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80 flex justify-end gap-3">
                    <button type="button" wire:click="$set('showEditModal', false)" class="px-5 py-3 text-slate-500 dark:text-slate-400 font-bold hover:bg-slate-50 dark:hover:bg-[#1a1a1a] rounded-2xl transition-colors text-xs uppercase tracking-wider cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-500/10 transition-all text-xs uppercase tracking-wider flex items-center gap-2 cursor-pointer">
                        <span wire:loading.remove wire:target="updateTicket">Save Changes</span>
                        <span wire:loading wire:target="updateTicket" class="flex items-center gap-1.5">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
