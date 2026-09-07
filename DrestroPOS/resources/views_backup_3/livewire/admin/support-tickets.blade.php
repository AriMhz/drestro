<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Support Tickets</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Need help? Open a ticket and our team will assist you.</p>
        </div>
        <button wire:click="$set('showCreateModal', true)" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            New Ticket
        </button>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-[#1a1a1a] rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 text-sm font-semibold text-slate-600 dark:text-slate-400">
                        <th class="px-6 py-4">Ticket ID</th>
                        <th class="px-6 py-4">Title</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Priority</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">
                                #{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $ticket->title }}</p>
                            </td>
                            <td class="px-6 py-4">
                                @if($ticket->status === 'OPEN')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">Open</span>
                                @elseif($ticket->status === 'RESOLVED')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">Resolved</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">{{ $ticket->status }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">{{ $ticket->priority }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">
                                {{ $ticket->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($ticket->status === 'OPEN')
                                    <div class="flex items-center justify-end gap-2">
                                        <button wire:click="editTicket({{ $ticket->id }})" class="p-1.5 text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30 rounded-lg transition-colors" title="Edit Ticket">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        <button wire:click="cancelTicket({{ $ticket->id }})" class="p-1.5 text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/30 rounded-lg transition-colors" title="Cancel Ticket" onclick="confirm('Are you sure you want to cancel this ticket?') || event.stopImmediatePropagation()">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                        <button wire:click="deleteTicket({{ $ticket->id }})" class="p-1.5 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="Delete Ticket" onclick="confirm('Are you sure you want to permanently delete this ticket?') || event.stopImmediatePropagation()">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                @else
                                    <button wire:click="deleteTicket({{ $ticket->id }})" class="p-1.5 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="Delete Ticket" onclick="confirm('Are you sure you want to permanently delete this ticket?') || event.stopImmediatePropagation()">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    <p>No support tickets found.</p>
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
    <div class="fixed inset-0 z-[100] flex items-center justify-center">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="$set('showCreateModal', false)"></div>
        <div class="relative bg-white dark:bg-[#1a1a1a] rounded-2xl shadow-2xl w-full max-w-lg mx-4 border border-slate-200 dark:border-slate-800">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white">Create Support Ticket</h3>
                <button type="button" wire:click="$set('showCreateModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form wire:submit.prevent="createTicket" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Subject</label>
                    <input type="text" wire:model="title" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-[#242424] dark:text-white" placeholder="E.g. Printer is not connecting" required>
                    @error('title') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Priority</label>
                    <select wire:model="priority" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-[#242424] dark:text-white">
                        <option value="LOW">Low - General Inquiry</option>
                        <option value="MEDIUM">Medium - Non-critical Issue</option>
                        <option value="HIGH">High - System Impacted</option>
                        <option value="URGENT">Urgent - System Down</option>
                    </select>
                    @error('priority') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Description</label>
                    <textarea wire:model="description" rows="4" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-[#242424] dark:text-white resize-none" placeholder="Please describe the issue in detail..." required></textarea>
                    @error('description') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="pt-2">
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-emerald-200 bg-emerald-50/50 dark:border-emerald-900/50 dark:bg-emerald-900/10 cursor-pointer hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors">
                        <div class="flex items-center h-5 mt-0.5">
                            <input type="checkbox" wire:model="grantAccess" class="w-4 h-4 text-emerald-600 border-emerald-300 rounded focus:ring-emerald-500 dark:bg-[#242424] dark:border-emerald-800">
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-emerald-900 dark:text-emerald-400">Grant Remote Support Access</span>
                            <span class="text-xs text-emerald-700 dark:text-emerald-500/80">Allow support.drestro technicians to temporarily access your POS system to diagnose and fix this issue faster.</span>
                        </div>
                    </label>
                </div>

                <div class="pt-4 flex justify-end gap-3">
                    <button type="button" wire:click="$set('showCreateModal', false)" class="px-5 py-2.5 text-slate-600 dark:text-slate-300 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl shadow-sm transition-colors flex items-center gap-2">
                        <span wire:loading.remove wire:target="createTicket">Submit Ticket</span>
                        <span wire:loading wire:target="createTicket">Submitting...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Edit Ticket Modal -->
    @if($showEditModal)
    <div class="fixed inset-0 z-[100] flex items-center justify-center">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="$set('showEditModal', false)"></div>
        <div class="relative bg-white dark:bg-[#1a1a1a] rounded-2xl shadow-2xl w-full max-w-lg mx-4 border border-slate-200 dark:border-slate-800">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white">Edit Support Ticket</h3>
                <button type="button" wire:click="$set('showEditModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form wire:submit.prevent="updateTicket" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Subject</label>
                    <input type="text" wire:model="title" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-[#242424] dark:text-white" required>
                    @error('title') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Priority</label>
                    <select wire:model="priority" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-[#242424] dark:text-white">
                        <option value="LOW">Low - General Inquiry</option>
                        <option value="MEDIUM">Medium - Non-critical Issue</option>
                        <option value="HIGH">High - System Impacted</option>
                        <option value="URGENT">Urgent - System Down</option>
                    </select>
                    @error('priority') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Description</label>
                    <textarea wire:model="description" rows="4" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-[#242424] dark:text-white resize-none" required></textarea>
                    @error('description') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="pt-4 flex justify-end gap-3">
                    <button type="button" wire:click="$set('showEditModal', false)" class="px-5 py-2.5 text-slate-600 dark:text-slate-300 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-sm transition-colors flex items-center gap-2">
                        <span wire:loading.remove wire:target="updateTicket">Save Changes</span>
                        <span wire:loading wire:target="updateTicket">Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
