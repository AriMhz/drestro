<div class="h-full flex flex-col bg-slate-50 dark:bg-slate-900" wire:poll.5s="loadTables">
    @php
        $userForAuth = auth()->user();
        $hasCashierAccess = $userForAuth->role === 'super_admin' || 
            (empty($userForAuth->allowed_pages) ? true : in_array('cashier_panel', $userForAuth->allowed_pages));
    @endphp
    <x-slot:title>Table Status Dashboard</x-slot>

    <!-- Audio Chimes -->
    <audio id="qr-alert-sound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>
    <audio id="ready-alert-sound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>

    <!-- Header Area -->
    <div class="px-6 py-6 bg-white border-b border-slate-200 dark:bg-slate-900 dark:border-slate-700">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-xl font-black text-slate-800 dark:text-white">Floor & Waiter Panel</span>
                @if(count($pendingQrOrders) > 0)
                    <span class="px-3 py-1 bg-amber-500 text-white text-xs font-black rounded-full uppercase tracking-wider animate-pulse flex items-center gap-1.5 shadow-md shadow-amber-500/30">
                        <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
                        {{ count($pendingQrOrders) }} New QR Order(s)
                    </span>
                @endif
            </div>
            
            <div class="flex items-center gap-4 bg-slate-50 p-3 rounded-2xl border border-slate-100 dark:border-slate-800 dark:bg-slate-900 text-xs">
                <div class="flex items-center gap-2">
                    <div class="w-3.5 h-3.5 rounded-full bg-emerald-500 shadow-sm"></div>
                    <span class="font-bold text-slate-700 dark:text-slate-300">Khali (Empty)</span>
                </div>
                <div class="flex items-center gap-2 border-l border-slate-200 pl-4 dark:border-slate-700">
                    <div class="w-3.5 h-3.5 rounded-full bg-amber-500 shadow-sm animate-pulse"></div>
                    <span class="font-bold text-amber-700 dark:text-amber-400">QR Pending</span>
                </div>
                <div class="flex items-center gap-2 border-l border-slate-200 pl-4 dark:border-slate-700">
                    <div class="w-3.5 h-3.5 rounded-full bg-red-500 shadow-sm"></div>
                    <span class="font-bold text-slate-700 dark:text-slate-300">Booked</span>
                </div>
            </div>
        </div>
        
        @if(session()->has('success'))
            <div class="max-w-7xl mx-auto mt-4">
                <div class="p-3.5 bg-emerald-50 text-emerald-700 rounded-2xl font-bold border border-emerald-100 flex items-center gap-2.5 shadow-sm text-sm dark:bg-emerald-900/20 dark:border-emerald-800/50 dark:text-emerald-400">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            </div>
        @endif
    </div>

    <!-- PENDING QR ORDERS BANNER SECTION -->
    @if(count($pendingQrOrders) > 0)
    <div class="bg-amber-500/10 border-b border-amber-500/20 px-6 py-6 animate-fade-in">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2 text-amber-800 dark:text-amber-400">
                    <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg font-bold shadow-md shadow-amber-500/30 animate-bounce">
                        🔔
                    </div>
                    <div>
                        <h2 class="text-base font-black uppercase tracking-wider">QR Orders Awaiting Waiter Confirmation ({{ count($pendingQrOrders) }})</h2>
                        <p class="text-xs text-amber-700 dark:text-amber-400/80 font-medium">Verify with table customer and click "Approve" to send KOT ticket to kitchen.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($pendingQrOrders as $qrOrder)
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 border-2 border-amber-400 shadow-xl shadow-amber-500/10 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-start justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white font-black text-lg flex items-center justify-center shadow-md">
                                    {{ $qrOrder->table ? $qrOrder->table->identifier : 'QR' }}
                                </div>
                                <div>
                                    <h3 class="font-black text-slate-800 dark:text-white text-base">
                                        {{ $qrOrder->table ? 'Table ' . $qrOrder->table->name : 'Dine-in' }}
                                    </h3>
                                    <span class="text-[10px] font-bold text-slate-400">
                                        {{ $qrOrder->order_number }} • {{ $qrOrder->created_at->format('h:i A') }}
                                    </span>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-700 border border-amber-200 animate-pulse dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900">
                                Pending
                            </span>
                        </div>

                        <!-- Items List -->
                        <div class="space-y-1.5 max-h-40 overflow-y-auto pr-1">
                            @foreach($qrOrder->items as $item)
                            <div class="flex items-center justify-between text-xs py-1 border-b border-slate-50 dark:border-slate-800/50">
                                <span class="font-medium text-slate-700 dark:text-slate-300">
                                    <strong class="font-black text-amber-600 dark:text-amber-400">{{ $item->quantity }}x</strong> 
                                    {{ $item->menuItem->name ?? 'Dish' }}
                                    @if($item->variation_name)
                                        <span class="text-[10px] text-slate-400">({{ $item->variation_name }})</span>
                                    @endif
                                </span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">Rs. {{ number_format($item->subtotal, 0) }}</span>
                            </div>
                            @endforeach
                        </div>

                        @if($qrOrder->special_instructions)
                        <div class="mt-2.5 p-2 bg-slate-50 dark:bg-slate-800/60 rounded-xl text-[11px] text-slate-600 dark:text-slate-300">
                            <strong>Note:</strong> {{ $qrOrder->special_instructions }}
                        </div>
                        @endif

                        <div class="flex items-center justify-between mt-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <span class="text-xs font-bold text-slate-500">Order Total:</span>
                            <span class="text-base font-black text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($qrOrder->total_amount, 0) }}</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="grid grid-cols-2 gap-2 pt-2">
                        <button wire:click="rejectQrOrder({{ $qrOrder->id }})" wire:confirm="Are you sure you want to reject this QR order?" class="py-2.5 px-3 bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 rounded-xl font-bold text-xs transition-colors dark:bg-slate-800 dark:hover:bg-rose-950/40 dark:text-slate-300 dark:hover:text-rose-400 flex items-center justify-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Reject
                        </button>
                        <button wire:click="approveQrOrder({{ $qrOrder->id }})" class="py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-black text-xs transition-all shadow-md shadow-emerald-600/20 active:scale-95 flex items-center justify-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Approve (KOT)
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Main Dashboard Grid -->
    <div class="p-6 overflow-y-auto flex-1">
        <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($tables as $table)
                @php
                    $isOccupied = $table->status === 'occupied';
                    $hasPendingQr = $table->status === 'pending_qr';
                    $isReady = $isOccupied && $table->active_order && (
                        $table->active_order->status === 'ready' || 
                        $table->active_order->items()->where('status', 'ready')->exists()
                    );
                @endphp
                
                <div class="relative group rounded-[2.5rem] p-8 transition-all duration-500 
                    {{ $hasPendingQr ? 'bg-amber-50/70 border-2 border-amber-500 shadow-xl shadow-amber-500/10' : ($isOccupied ? ($isReady ? 'bg-emerald-50 border-2 border-emerald-500 shadow-xl' : 'bg-white border-2 border-red-500 shadow-xl') : 'bg-white border border-slate-100 hover:border-emerald-300 shadow-[0_10px_30px_-10px_rgba(0,0,0,0.04)] hover:shadow-xl') }} dark:border-slate-800 dark:bg-slate-900">
                    
                    <!-- Table ID Badge -->
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-16 h-16 rounded-3xl flex items-center justify-center text-2xl font-black {{ $hasPendingQr ? 'bg-amber-500 text-white shadow-lg animate-bounce' : ($isOccupied ? ($isReady ? 'bg-emerald-600 text-white shadow-lg animate-bounce' : 'bg-red-500 text-white shadow-lg rotate-3') : 'bg-emerald-50 text-emerald-600 border border-emerald-100') }} dark:bg-emerald-900/20 dark:border-emerald-800/50 dark:text-emerald-400">
                            {{ $table->identifier }}
                        </div>
                        
                        <div class="flex flex-col items-end gap-2">
                            @if($hasPendingQr)
                                <span class="px-3.5 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-amber-500 text-white animate-pulse">
                                    🔔 QR ORDER
                                </span>
                            @elseif($isReady)
                                <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-600 text-white animate-pulse">
                                    TAIYAAR (READY)
                                </span>
                            @else
                                <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border {{ $isOccupied ? 'bg-red-50 text-red-600 border-red-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100' }} dark:bg-emerald-900/20 dark:border-red-800/50 dark:bg-red-900/20 dark:text-red-400 dark:text-emerald-400 dark:border-emerald-800/50">
                                    {{ $isOccupied ? 'BOOKED' : 'KHALI' }}
                                </span>
                            @endif
                            @if($isOccupied && $table->active_order)
                                <span class="text-[10px] font-bold text-slate-400 bg-slate-50 px-2 py-1 rounded-md dark:bg-slate-900">
                                    Since {{ $table->active_order->created_at->format('h:i A') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Table Name & Capacity -->
                    <div class="mb-8">
                        <h3 class="text-xl font-black text-slate-800 leading-tight dark:text-slate-200">{{ $table->name }}</h3>
                        <div class="flex items-center gap-2 mt-2 text-slate-400 font-bold text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            {{ $table->capacity }} Persons
                        </div>
                    </div>
                    
                    <!-- Primary Action -->
                    <div class="space-y-3">
                        @if($hasPendingQr && $table->pending_qr_order)
                            <div class="bg-amber-50 rounded-2xl p-4 border border-amber-200 mb-4 dark:border-amber-900/50 dark:bg-amber-950/30">
                                <div class="flex items-center justify-between text-xs font-bold text-amber-700 uppercase dark:text-amber-400">
                                    <span>QR Order Received</span>
                                    <span>Rs. {{ number_format($table->pending_qr_order->total_amount, 0) }}</span>
                                </div>
                                <div class="mt-1 text-xs text-slate-600 dark:text-slate-300 font-medium truncate">
                                    {{ $table->pending_qr_order->items->count() }} item(s) • {{ $table->pending_qr_order->created_at->format('h:i A') }}
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-2">
                                <button wire:click="rejectQrOrder({{ $table->pending_qr_order->id }})" wire:confirm="Are you sure you want to reject this QR order?" class="py-3 bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 rounded-[1.25rem] font-bold text-xs transition-colors dark:bg-slate-800 dark:hover:bg-rose-950/40 dark:text-slate-300 dark:hover:text-rose-400 flex items-center justify-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    Reject
                                </button>
                                <button wire:click="approveQrOrder({{ $table->pending_qr_order->id }})" class="py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-[1.25rem] font-black text-xs transition-all shadow-md shadow-emerald-600/20 active:scale-95 flex items-center justify-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Approve (KOT)
                                </button>
                            </div>
                        @elseif($isOccupied)
                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 mb-4 {{ $isReady ? 'border-emerald-200 bg-emerald-50/50' : '' }} dark:border-slate-800 dark:bg-slate-900 dark:bg-emerald-900/20 dark:border-emerald-800">
                                <div class="flex items-center justify-between text-xs font-bold text-slate-500 uppercase dark:text-slate-400">
                                    <span>Session Info</span>
                                    <span class="{{ $isReady ? 'text-emerald-600' : ($table->active_order && $table->active_order->status === 'served' ? 'text-blue-600' : 'text-red-500') }} dark:text-blue-400 dark:text-emerald-400">
                                        @if($isReady)
                                             Ready to Serve
                                        @elseif($table->active_order && $table->active_order->status === 'served')
                                            Served
                                        @else
                                            Cooking
                                        @endif
                                    </span>
                                </div>
                                <div class="mt-1 text-sm font-black text-slate-700 dark:text-slate-300">
                                    {{ $table->active_order ? 'Order #' . $table->active_order->id : 'Manual Booking' }}
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 gap-3">
                                @if($isReady)
                                    <button wire:click="clearTable({{ $table->id }})" class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-[1.25rem] font-black text-sm text-center transition-all shadow-lg flex items-center justify-center gap-2">
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
                                            <div class="w-full py-3 bg-amber-50 border border-amber-200 text-amber-800 text-xs font-black uppercase text-center rounded-[1.25rem] tracking-wider flex items-center justify-center gap-1.5 shadow-sm dark:border-amber-800 dark:text-amber-300 dark:bg-amber-900/20">
                                                <span class="w-2 h-2 bg-amber-500 rounded-full animate-ping"></span>
                                                Waiting for Cashier 💳
                                            </div>
                                            <button wire:click="completeTable({{ $table->id }})" onclick="confirm('Clear this table without cashier billing? (Order will be marked as completed with cash payment)') || event.stopImmediatePropagation()" class="w-full py-3 bg-white border-2 border-slate-300 text-slate-500 hover:border-red-400 hover:text-red-600 hover:bg-red-50 rounded-[1.25rem] font-bold text-xs text-center transition-all flex items-center justify-center gap-2 dark:bg-slate-900 dark:border-slate-600 dark:text-slate-400">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Clear Table
                                            </button>
                                        @else
                                            <button wire:click="completeTable({{ $table->id }})" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-[1.25rem] font-black text-sm text-center transition-all shadow-lg flex items-center justify-center gap-2">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                                Complete & Settle
                                            </button>
                                        @endif
                                    @else
                                        <button wire:click="clearTable({{ $table->id }})" class="w-full py-3.5 bg-white border-2 border-emerald-500 text-emerald-600 hover:bg-emerald-50 rounded-[1.25rem] font-bold text-sm transition-all flex items-center justify-center gap-2 dark:bg-slate-900 dark:text-emerald-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Mark as Served
                                        </button>
                                        <button wire:click="cancelOrder({{ $table->id }})" onclick="confirm('Are you sure you want to cancel this order?') || event.stopImmediatePropagation()" class="w-full mt-3 py-3 bg-white border-2 border-red-500 text-red-600 hover:bg-red-50 rounded-[1.25rem] font-bold text-sm transition-all flex items-center justify-center gap-2 dark:bg-slate-900 dark:text-red-400 dark:border-red-800">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            Cancel Order
                                        </button>
                                    @endif
                                @endif
                            </div>
                        @else
                            <a href="{{ route('staff.take-order', ['table' => $table->id]) }}" class="block w-full py-4 bg-emerald-500 hover:bg-emerald-600 text-white rounded-[1.25rem] font-black text-sm text-center transition-all shadow-lg flex items-center justify-center gap-2 group-hover:scale-105">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                START BOOKING
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 bg-white rounded-[3rem] border-2 border-dashed border-slate-200 flex flex-col items-center justify-center text-center dark:bg-slate-900 dark:border-slate-700">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4 text-slate-300 dark:bg-slate-900">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-slate-200">No tables available</h3>
                    <p class="text-slate-500 mt-2 dark:text-slate-400">Go to Admin > Tables to add your restaurant tables.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Scripts for Audio & Thermal Printing -->
    <script>
        document.addEventListener('livewire:initialized', function() {
            Livewire.on('play-qr-alert', function() {
                let sound = document.getElementById('qr-alert-sound');
                if (sound) {
                    sound.currentTime = 0;
                    sound.play().catch(e => console.log('Audio autoplay prevented:', e));
                }
            });

            Livewire.on('play-ready-sound', function() {
                let sound = document.getElementById('ready-alert-sound');
                if (sound) {
                    sound.currentTime = 0;
                    sound.play().catch(e => console.log('Audio autoplay prevented:', e));
                }
            });

            Livewire.on('trigger-browser-print', function(event) {
                let url = typeof event === 'string' ? event : (event.url || (Array.isArray(event) ? event[0]?.url : ''));
                if (url) {
                    let iframe = document.createElement('iframe');
                    iframe.style.position = 'fixed';
                    iframe.style.right = '0';
                    iframe.style.bottom = '0';
                    iframe.style.width = '0';
                    iframe.style.height = '0';
                    iframe.style.border = '0';
                    iframe.src = url;
                    document.body.appendChild(iframe);
                    setTimeout(function() {
                        try { document.body.removeChild(iframe); } catch(e){}
                    }, 15000);
                }
            });
        });
    </script>
</div>
