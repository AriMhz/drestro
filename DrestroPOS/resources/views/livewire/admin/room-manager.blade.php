<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="hidden lg:block">
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight dark:text-slate-200">Hotel Room Manager</h2>
            <p class="text-slate-500 text-sm mt-1 dark:text-slate-400">Manage your hotel rooms and availability separately from restaurant tables.</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="toggleAddModal" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-all shadow flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add New Room
            </button>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session()->has('error'))
    <div class="p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center text-red-700 font-bold text-sm shadow-sm dark:border-red-800 dark:text-red-400 dark:bg-red-900/20">
        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    @if(session()->has('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center text-emerald-700 font-bold text-sm shadow-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400">
        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- Add Room Modal -->
    @if($showAddModal)
    <div class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-[2rem] p-8 w-full max-w-md shadow-2xl border border-slate-100 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-black text-slate-800 dark:text-slate-200">{{ $editingRoomId ? 'Edit Room' : 'Add New Room' }}</h3>
                <button wire:click="toggleAddModal" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form wire:submit.prevent="saveRoom" class="space-y-5">
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2 dark:text-slate-400">Room Number / Name</label>
                    <input wire:model="newRoomName" type="text" class="w-full px-5 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-indigo-500 focus:bg-slate-900 focus:text-white hover:bg-slate-900 hover:text-white focus:dark:bg-[#0a0a0a] hover:dark:bg-[#0a0a0a] outline-none transition-all font-bold text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 focus:dark:text-white hover:dark:text-white" placeholder="e.g. Room 101, Executive Suite" required>
                    @error('newRoomName')
                        <span class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="pt-4 flex gap-4">
                    <button type="button" wire:click="toggleAddModal" class="flex-1 px-6 py-3.5 bg-slate-100 text-slate-600 rounded-2xl font-bold hover:bg-slate-200 transition-all dark:text-slate-400 dark:bg-slate-800">Cancel</button>
                    <button type="submit" class="flex-1 px-6 py-3.5 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 transition-all shadow">Save Room</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Rooms Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        
        @foreach($rooms as $room)
        <!-- Room Card -->
        <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex flex-col items-center text-center relative group hover:shadow-xl hover:-translate-y-1 transition-all duration-300 dark:border-slate-800 dark:bg-slate-900">
            
            <div class="absolute top-4 right-4 flex gap-2 transition-all">
                <button wire:click="editRoom({{ $room->id }})" class="text-slate-300 hover:text-indigo-500 transition-colors p-1" title="Edit">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </button>
                <button wire:click="deleteRoom({{ $room->id }})" wire:confirm="Are you sure you want to delete this room?" class="text-slate-300 hover:text-red-500 transition-colors p-1" title="Delete">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>

            <div class="w-20 h-20 bg-indigo-50 rounded-3xl flex items-center justify-center mb-4 group-hover:bg-indigo-600 transition-colors duration-300 dark:bg-indigo-900/20">
                <svg class="w-10 h-10 text-indigo-500 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            </div>

            <h3 class="text-2xl font-black text-slate-800 dark:text-slate-200">{{ $room->name }}</h3>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Hotel Room</p>
            
            <div class="mt-6 w-full pt-6 border-t border-slate-50">
                <span class="px-4 py-2 bg-emerald-50 text-emerald-600 rounded-full text-xs font-black uppercase tracking-widest dark:bg-emerald-900/20 dark:text-emerald-400">Available</span>
            </div>
        </div>
        @endforeach

        <!-- Add Room Card -->
        <div wire:click="toggleAddModal" class="bg-indigo-50/30 p-6 rounded-[2rem] border-2 border-dashed border-indigo-100 flex flex-col items-center justify-center text-center cursor-pointer hover:bg-indigo-50 hover:border-indigo-300 transition-all group min-h-[280px] dark:border-slate-800">
            <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mb-4 shadow-sm group-hover:scale-110 transition-transform dark:bg-slate-900">
                <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </div>
            <h3 class="text-lg font-black text-indigo-900 dark:text-indigo-300">Add Room</h3>
            <p class="text-xs font-bold text-indigo-400 dark:text-indigo-400/80 mt-1 uppercase tracking-wider">Setup a new guest room</p>
        </div>

    </div>
</div>
