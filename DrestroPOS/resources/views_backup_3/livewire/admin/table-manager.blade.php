<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Table Management & QR Codes</h2>
            <p class="text-slate-500 text-sm mt-1">Add tables and generate unique QR codes for dine-in ordering.</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="toggleAddModal" class="px-4 py-2 bg-emerald-600 text-white rounded-lg font-medium hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add New Table
            </button>
        </div>
    </div>

    <!-- Add Table Modal -->
    @if($showAddModal)
    <div class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl border border-slate-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-slate-800">Add New Table</h3>
                <button wire:click="toggleAddModal" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form wire:submit.prevent="saveTable" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Table Name/Number</label>
                    <input wire:model="newTableName" type="text" class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors" placeholder="e.g. Table 1, Window Seat" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Seating Capacity</label>
                    <input wire:model="newTableCapacity" type="number" min="1" class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors" required>
                </div>
                
                <div class="pt-4 flex gap-3">
                    <button type="button" wire:click="toggleAddModal" class="flex-1 px-4 py-2 bg-slate-100 text-slate-700 rounded-lg font-medium hover:bg-slate-200 transition-colors">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-emerald-600 text-white rounded-lg font-medium hover:bg-emerald-700 transition-colors shadow-sm">Save Table</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Tables Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        
        @foreach($tables as $table)
        <!-- Table Card -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col items-center text-center relative group">
            
            <button wire:click="deleteTable({{ $table->id }})" class="absolute top-3 right-3 text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-opacity">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>

            <h3 class="text-xl font-bold text-slate-800">{{ $table->name }}</h3>
            <p class="text-sm text-slate-500 mb-4">Capacity: {{ $table->capacity }} Persons</p>
            
            <!-- Dynamic QR Code -->
            <div class="w-32 h-32 bg-slate-100 rounded-lg p-2 flex items-center justify-center border-2 border-dashed border-slate-300 mb-4">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(url('/menu?table=' . $table->id)) }}" alt="QR Code" class="w-full h-full object-contain mix-blend-multiply opacity-80" />
            </div>
            
            <div class="flex gap-2 w-full">
                <a href="https://api.qrserver.com/v1/create-qr-code/?size=500x500&data={{ urlencode(url('/menu?table=' . $table->id)) }}" target="_blank" class="flex-1 px-3 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors flex justify-center items-center">
                    Print QR
                </a>
            </div>
        </div>
        @endforeach

        <!-- Add Table Card -->
        <div wire:click="toggleAddModal" class="bg-slate-50 p-6 rounded-2xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center text-center cursor-pointer hover:bg-slate-100 transition-colors group min-h-[300px]">
            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-4 shadow-sm group-hover:scale-105 transition-transform">
                <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </div>
            <h3 class="text-lg font-medium text-slate-700">Add Table</h3>
            <p class="text-sm text-slate-500 mt-1">Generate a new QR code</p>
        </div>

    </div>
</div>
