<div class="h-full flex flex-col bg-slate-50" wire:poll.10s="loadTables">
    @php
        $userForAuth = auth()->user();
        $hasCashierAccess = $userForAuth->role === 'super_admin' || 
            (empty($userForAuth->allowed_pages) ? true : in_array('cashier_panel', $userForAuth->allowed_pages));
    @endphp
    <x-slot:title>Table Status Dashboard</x-slot>

    <!-- Header Area -->
    <div class="px-6 py-8 bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Table Dashboard</h1>
                <p class="text-slate-500 font-medium mt-1">Real-time occupancy and status management</p>
            </div>
            
            <div class="flex items-center gap-6 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/50 animate-pulse"></div>
                    <span class="text-sm font-bold text-slate-700">GREEN: Khali (Empty)</span>
                </div>
                <div class="flex items-center gap-2 border-l border-slate-200 pl-6">
                    <div class="w-4 h-4 rounded-full bg-red-500 shadow-sm shadow-red-500/50"></div>
                    <span class="text-sm font-bold text-slate-700">RED: Booked (1 Hr Limit)</span>
                </div>
            </div>
        </div>
        
        @if(session()->has('success'))
            <div class="max-w-7xl mx-auto mt-6">
                <div class="p-4 bg-emerald-50 text-emerald-700 rounded-2xl font-bold border border-emerald-100 flex items-center gap-3 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            </div>
        @endif
    </div>

    <!-- Main Dashboard Grid -->
    <div class="p-6 overflow-y-auto flex-1">
        <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($tables as $table)
                @php
                    $isOccupied = $table->status === 'occupied';
                    $isReady = $isOccupied && $table->active_order && (
                        $table->active_order->status === 'ready' || 
                        $table->active_order->items()->where('status', 'ready')->exists()
                    );
                @endphp
                
                <div class="relative group rounded-[2.5rem] p-8 transition-all duration-500 
                    {{ $isOccupied ? ($isReady ? 'bg-emerald-50 border-2 border-emerald-500 shadow-[0_20px_50px_-12px_rgba(16,185,129,0.3)]' : 'bg-white border-2 border-red-500 shadow-[0_20px_50px_-12px_rgba(239,68,68,0.25)]') : 'bg-white border border-slate-100 hover:border-emerald-300 shadow-[0_10px_30px_-10px_rgba(0,0,0,0.04)] hover:shadow-xl' }}">
                    
                    <!-- Table ID Badge -->
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-16 h-16 rounded-3xl flex items-center justify-center text-2xl font-black {{ $isOccupied ? ($isReady ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/40 animate-bounce' : 'bg-red-500 text-white shadow-lg shadow-red-500/40 rotate-3') : 'bg-emerald-50 text-emerald-600 border border-emerald-100' }}">
                            {{ $table->identifier }}
                        </div>
                        
                        <div class="flex flex-col items-end gap-2">
                            @if($isReady)
                                <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-600 text-white animate-pulse">
                                    TAIYAAR (READY)
                                </span>
                            @else
                                <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border {{ $isOccupied ? 'bg-red-50 text-red-600 border-red-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100' }}">
                                    {{ $isOccupied ? 'BOOKED' : 'KHALI' }}
                                </span>
                            @endif
                            @if($isOccupied && $table->active_order)
                                <span class="text-[10px] font-bold text-slate-400 bg-slate-50 px-2 py-1 rounded-md">
                                    Since {{ $table->active_order->created_at->format('h:i A') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Table Name & Capacity -->
                    <div class="mb-8">
                        <h3 class="text-xl font-black text-slate-800 leading-tight">{{ $table->name }}</h3>
                        <div class="flex items-center gap-2 mt-2 text-slate-400 font-bold text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            {{ $table->capacity }} Persons
                        </div>
                    </div>
                    
                    <!-- Primary Action -->
                    <div class="space-y-3">
                        @if($isOccupied)
                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 mb-4 {{ $isReady ? 'border-emerald-200 bg-emerald-50/50' : '' }}">
                                <div class="flex items-center justify-between text-xs font-bold text-slate-500 uppercase">
                                    <span>Session Info</span>
                                    <span class="{{ $isReady ? 'text-emerald-600' : ($table->active_order && $table->active_order->status === 'served' ? 'text-blue-600' : 'text-red-500') }}">
                                        @if($isReady)
                                            Ready to Serve
                                        @elseif($table->active_order && $table->active_order->status === 'served')
                                            Served
                                        @else
                                            Cooking
                                        @endif
                                    </span>
                                </div>
                                <div class="mt-1 text-sm font-black text-slate-700">
                                    {{ $table->active_order ? 'Order #' . $table->active_order->id : 'Manual Booking' }}
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 gap-3">
                                @if($isReady)
                                    <button wire:click="clearTable({{ $table->id }})" class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-[1.25rem] font-black text-sm text-center transition-all shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        ✓ SERVE ORDER
                                    </button>
                                @else
                                    <a href="{{ route('staff.take-order', ['table' => $table->id]) }}" class="w-full py-3.5 bg-slate-900 hover:bg-black text-white rounded-[1.25rem] font-bold text-sm text-center transition-all shadow-lg shadow-slate-900/20 flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                        Add Items
                                    </a>
                                    
                                    @if($table->active_order && $table->active_order->status === 'served')
                                        @if($hasCashierAccess)
                                            <div class="w-full py-3 bg-amber-50 border border-amber-200 text-amber-800 text-xs font-black uppercase text-center rounded-[1.25rem] tracking-wider flex items-center justify-center gap-1.5 shadow-sm">
                                                <span class="w-2 h-2 bg-amber-500 rounded-full animate-ping"></span>
                                                Waiting for Cashier 💳
                                            </div>
                                            <button wire:click="completeTable({{ $table->id }})" onclick="confirm('Clear this table without cashier billing? (Order will be marked as completed with cash payment)') || event.stopImmediatePropagation()" class="w-full py-3 bg-white border-2 border-slate-300 text-slate-500 hover:border-red-400 hover:text-red-600 hover:bg-red-50 rounded-[1.25rem] font-bold text-xs text-center transition-all flex items-center justify-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Clear Table
                                            </button>
                                        @else
                                            <button wire:click="completeTable({{ $table->id }})" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-[1.25rem] font-black text-sm text-center transition-all shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                                Complete & Settle
                                            </button>
                                        @endif
                                    @else
                                        <button wire:click="clearTable({{ $table->id }})" class="w-full py-3.5 bg-white border-2 border-emerald-500 text-emerald-600 hover:bg-emerald-50 rounded-[1.25rem] font-bold text-sm transition-all flex items-center justify-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Mark as Served
                                        </button>
                                    @endif
                                @endif
                            </div>
                        @else
                            <a href="{{ route('staff.take-order', ['table' => $table->id]) }}" class="block w-full py-4 bg-emerald-500 hover:bg-emerald-600 text-white rounded-[1.25rem] font-black text-sm text-center transition-all shadow-lg shadow-emerald-500/30 flex items-center justify-center gap-2 group-hover:scale-105">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                START BOOKING
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 bg-white rounded-[3rem] border-2 border-dashed border-slate-200 flex flex-col items-center justify-center text-center">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4 text-slate-300">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">No tables available</h3>
                    <p class="text-slate-500 mt-2">Go to Admin > Tables to add your restaurant tables.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Ready Sound -->
    <audio id="readySound" preload="auto">
        <source src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" type="audio/mpeg">
    </audio>

    <script>
        window.addEventListener('play-ready-sound', event => {
            const sound = document.getElementById('readySound');
            if (sound) {
                sound.currentTime = 0;
                sound.play().catch(error => {
                    console.log("Audio playback failed:", error);
                });
            }
        });
    </script>
</div>
