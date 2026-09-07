<div x-data="{ showCart: false }" class="h-full flex flex-col overflow-hidden relative bg-[#f1f5f9]/30 dark:bg-slate-950">

    @if(session()->has('success'))
        <div class="fixed top-20 right-8 z-[100] animate-in fade-in slide-in-from-right duration-500">
            <div class="bg-slate-900 text-white px-8 py-4 rounded-2xl shadow-2xl flex items-center gap-4 border border-slate-700">
                <div class="w-8 h-8 bg-emerald-500 rounded-full flex items-center justify-center text-white shadow-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <span class="text-xs font-bold tracking-wide">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if($currentOrder)
    <!-- Order Success Screen -->
    <div class="absolute inset-0 z-[60] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-8 dark:bg-slate-900/80">
        <div class="max-w-md w-full bg-white rounded-[2.5rem] shadow-2xl border border-white overflow-hidden text-center animate-in zoom-in duration-300 dark:bg-slate-900">
            <div class="bg-indigo-600 p-12 text-white">
                <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-6 border border-white/30">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h2 class="text-3xl font-bold tracking-tight">Order Placed</h2>
                <p class="text-indigo-100 text-[10px] font-bold uppercase tracking-widest mt-2">Room Service Initialized</p>
            </div>
            <div class="p-10">
                <div class="bg-slate-50 rounded-2xl p-6 mb-8 border border-slate-100 text-center dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] mb-1">Receipt ID</p>
                    <p class="text-slate-900 font-black text-2xl tracking-tight dark:text-slate-100">{{ $currentOrder->order_number }}</p>
                </div>
                <button wire:click="startNewOrder" class="w-full py-4 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-all uppercase tracking-widest text-xs">
                    Return to Dashboard
                </button>
            </div>
        </div>
    </div>

    @elseif($tableSelectionMode)
    <!-- Premium Room Selection Dashboard -->
    <div class="flex-1 flex flex-col overflow-y-auto custom-scrollbar">
        <!-- Header Section -->
        <div class="pt-8 md:pt-16 pb-6 md:pb-12 px-4 md:px-12 max-w-[1600px] mx-auto w-full flex flex-col md:flex-row items-start md:items-end justify-between gap-6 md:gap-10">
            <div>
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-indigo-600 text-white rounded-full text-[9px] font-black uppercase tracking-[0.3em] mb-4">
                    Property Navigator
                </div>
                <h2 class="text-4xl md:text-6xl font-black text-slate-900 tracking-tighter leading-tight dark:text-slate-100">Hotel Booking</h2>
                <p class="text-slate-400 font-bold uppercase tracking-[0.4em] text-[9px] md:text-[10px] mt-2 flex items-center gap-3">Stay Management & Guest Services</p>
            </div>
            
            <div class="flex gap-4">
                <div class="flex items-center gap-4 px-6 md:px-8 py-3 md:py-4 bg-white rounded-xl md:rounded-2xl shadow-sm border border-slate-200 dark:bg-slate-900 dark:border-slate-700">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                        <span class="text-[9px] font-bold text-slate-600 uppercase tracking-widest dark:text-slate-400">Available</span>
                    </div>
                    <div class="w-px h-4 bg-slate-100 dark:bg-slate-800"></div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-indigo-600 rounded-full"></span>
                        <span class="text-[9px] font-bold text-slate-600 uppercase tracking-widest dark:text-slate-400">Occupied</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Room Grid -->
        <div class="max-w-[1600px] mx-auto w-full px-4 md:px-12 pb-20 grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-8">
            @foreach($this->filteredTables as $t)
            @php $isOccupied = $t->room_status === 'occupied'; @endphp
            <button wire:click="selectTable('{{ $t->id }}')" 
                class="group relative aspect-[4/5] rounded-[1.5rem] md:rounded-[3rem] transition-all duration-500 active:scale-95 shadow-sm hover:shadow-2xl flex flex-col items-center justify-between p-4 md:p-10 border-2 {{ $isOccupied ? 'bg-slate-900 border-slate-800' : 'bg-white border-transparent hover:border-indigo-100 shadow-slate-200/50' }} dark:bg-slate-900">
                
                <div class="absolute top-4 md:top-8 left-4 md:left-8 flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full {{ $isOccupied ? 'bg-indigo-500 shadow-sm' : 'bg-emerald-500 shadow-sm' }}"></div>
                    <span class="text-[8px] font-bold uppercase tracking-[0.2em] {{ $isOccupied ? 'text-indigo-400' : 'text-slate-400' }} hidden sm:block">
                        {{ $isOccupied ? 'Active Stay' : 'Ready' }}
                    </span>
                </div>

                <div class="mt-6 md:mt-8 w-16 h-16 md:w-24 md:h-24 rounded-[1.25rem] md:rounded-[2.5rem] flex items-center justify-center transition-all duration-500 {{ $isOccupied ? 'bg-white/5 text-white border border-white/5' : 'bg-slate-50 text-slate-300 group-hover:bg-indigo-600 group-hover:text-white' }} dark:bg-slate-900">
                    <svg class="w-8 h-8 md:w-10 md:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                </div>
                
                <div class="text-center w-full">
                    <span class="block text-3xl md:text-5xl font-black tracking-tighter mb-2 md:mb-4 {{ $isOccupied ? 'text-white' : 'text-slate-900 group-hover:text-indigo-600' }} transition-colors duration-500 dark:text-slate-100">{{ $t->name }}</span>
                    @if($isOccupied)
                        <div class="px-3 md:px-4 py-1.5 bg-white/10 rounded-xl border border-white/5">
                            <span class="text-[8px] md:text-[9px] font-bold text-indigo-300 uppercase tracking-widest block truncate">{{ $t->guest_name }}</span>
                        </div>
                    @else
                        <span class="text-[8px] md:text-[9px] font-bold text-emerald-500 uppercase tracking-[0.4em] block group-hover:translate-x-1 transition-transform">Book Now</span>
                    @endif
                </div>
            </button>
            @endforeach
        </div>
    </div>

    @elseif($bookingMode)
    <!-- Bulletproof Check-in Form (Final Elite Stability) -->
    <div class="flex-1 flex flex-col items-center justify-start bg-slate-50 p-4 md:p-12 pt-20 md:pt-32 overflow-y-auto custom-scrollbar dark:bg-slate-900">
        <div class="max-w-2xl w-full bg-white rounded-[2rem] md:rounded-[2.5rem] shadow-2xl border border-slate-200 flex flex-col mb-12 dark:bg-slate-900 dark:border-slate-700">
            <!-- Header: Rigid Stability & Perfect Rounding -->
            <div class="bg-slate-900 rounded-t-[2rem] md:rounded-t-[2.5rem] px-6 md:px-20 py-8 md:py-12 text-white flex justify-between items-center relative z-[10] overflow-hidden">
                <div class="flex-1">
                    <h2 class="text-2xl md:text-3xl font-black tracking-tight leading-none mb-2 md:mb-3">Guest Registration</h2>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
                        <p class="text-indigo-400 text-[9px] md:text-[10px] font-black uppercase tracking-[0.2em] md:tracking-[0.3em]">Room {{ \App\Models\Table::find($selectedTableId)?->name }} Stay Initiation</p>
                    </div>
                </div>
                <button wire:click="cancelBookingMode" class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-white/10 hover:bg-white/20 rounded-full transition-all shrink-0 ml-4">
                    <svg class="w-4 h-4 md:w-5 md:h-5 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="p-6 md:p-16 space-y-8 md:space-y-12">
                <!-- Guest Name Input -->
                <div class="space-y-3 md:space-y-4">
                    <label class="text-[9px] md:text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] md:tracking-[0.3em] ml-1">Guest Identification</label>
                    <div class="flex items-center bg-transparent border border-slate-200 rounded-2xl focus-within:border-indigo-600 transition-all group overflow-hidden dark:border-slate-700">
                        <div class="px-5 text-slate-300 group-focus-within:text-indigo-500 transition-colors flex items-center justify-center bg-transparent">
                            <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <input wire:model="guestName" type="text" placeholder="Full name of the primary guest" class="flex-1 bg-transparent border-none outline-none px-4 py-4 md:px-6 md:py-6 text-sm md:text-base text-slate-800 font-bold placeholder:text-slate-300 dark:text-slate-200">
                    </div>
                    @error('guestName') <span class="text-red-500 text-[10px] font-bold mt-1 block ml-1">{{ $message }}</span> @enderror
                </div>

                <!-- ID Number Input -->
                <div class="space-y-3 md:space-y-4">
                    <label class="text-[9px] md:text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] md:tracking-[0.3em] ml-1">Citizenship / ID Number (Optional)</label>
                    <div class="flex items-center bg-transparent border border-slate-200 rounded-2xl focus-within:border-indigo-600 transition-all group overflow-hidden dark:border-slate-700">
                        <div class="px-5 text-slate-300 group-focus-within:text-indigo-500 transition-colors flex items-center justify-center bg-transparent">
                            <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                        </div>
                        <input wire:model="guestIdNumber" type="text" placeholder="ID or Citizenship Number" class="flex-1 bg-transparent border-none outline-none px-4 py-4 md:px-6 md:py-6 text-sm md:text-base text-slate-800 font-bold placeholder:text-slate-300 dark:text-slate-200">
                    </div>
                    @error('guestIdNumber') <span class="text-red-500 text-[10px] font-bold mt-1 block ml-1">{{ $message }}</span> @enderror
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-10">
                    <!-- Phone Input -->
                    <div class="space-y-3 md:space-y-4">
                        <label class="text-[9px] md:text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] md:tracking-[0.3em] ml-1">Phone Reference</label>
                        <div class="flex items-center bg-transparent border border-slate-200 rounded-2xl focus-within:border-indigo-600 transition-all group overflow-hidden dark:border-slate-700">
                            <div class="px-5 text-slate-300 group-focus-within:text-indigo-500 transition-colors flex items-center justify-center bg-transparent">
                                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h2.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            </div>
                            <input wire:model.live="guestPhone" type="tel" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" placeholder="98XXXXXXXX" class="flex-1 bg-transparent border-none outline-none px-4 py-4 md:px-6 md:py-6 text-sm md:text-base text-slate-800 font-bold placeholder:text-slate-300 dark:text-slate-200">
                        </div>
                    </div>
                    <!-- Tariff Input -->
                    <div class="space-y-3 md:space-y-4">
                        <label class="text-[9px] md:text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] md:tracking-[0.3em] ml-1">Daily Rate (Rs.)</label>
                        <div class="flex items-center bg-transparent border border-slate-200 rounded-2xl focus-within:border-indigo-600 transition-all group overflow-hidden dark:border-slate-700">
                            <div class="px-5 text-slate-400 font-black group-focus-within:text-indigo-600 transition-colors text-sm flex items-center justify-center bg-transparent">
                                Rs.
                            </div>
                            <input wire:model="roomRate" type="number" placeholder="0.00" class="flex-1 bg-transparent border-none outline-none px-4 py-4 md:px-6 md:py-6 text-indigo-600 font-black text-xl md:text-2xl placeholder:text-slate-300">
                        </div>
                    </div>
                </div>

                <div class="pt-4 md:pt-8 pb-2 md:pb-4">
                    <button wire:click="bookRoom" class="w-full py-5 md:py-7 bg-indigo-600 text-white rounded-[1.5rem] md:rounded-[2rem] font-bold hover:bg-indigo-700 active:scale-95 transition-all uppercase tracking-[0.2em] md:tracking-[0.3em] text-[10px] md:text-[11px]">
                        Finalize Check-in & Open Stay
                    </button>
                    <button wire:click="cancelBookingMode" class="w-full mt-4 md:mt-6 py-3 md:py-4 bg-red-50 text-red-500 border border-red-100 rounded-xl md:rounded-2xl font-bold text-[9px] md:text-[10px] uppercase tracking-widest hover:bg-red-100 hover:text-red-600 transition-colors dark:bg-red-900/20 dark:text-red-400 dark:border-red-800/30 dark:hover:bg-red-900/40">
                        Cancel Registration
                    </button>
                </div>
            </div>
        </div>
    </div>

    @else
    <!-- Premium Service & Order Dashboard -->
    <div class="flex flex-col lg:flex-row flex-1 overflow-hidden bg-white dark:bg-slate-900">
        <!-- Left Column: Menu Selector -->
        <div class="flex-1 flex flex-col bg-slate-50 overflow-hidden border-r border-slate-100 dark:border-slate-800 dark:bg-slate-900">
            <!-- Menu Header -->
            <div class="bg-white px-8 py-6 border-b border-slate-200 flex justify-between items-center shadow-sm z-10 dark:bg-slate-900 dark:border-slate-700">
                <div class="flex items-center gap-6">
                    <button wire:click="startNewOrder" class="p-3 bg-slate-50 text-slate-400 rounded-xl hover:bg-indigo-600 hover:text-white transition-all dark:bg-slate-900">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </button>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 leading-none dark:text-slate-100">Room {{ \App\Models\Table::find($selectedTableId)?->name }}</h2>
                        <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest mt-1">{{ \App\Models\Table::find($selectedTableId)?->guest_name ?? 'Active Stay' }}</p>
                    </div>
                </div>
                <!-- Checkout Option Removed -->
            </div>

            <!-- Category Filter Bar -->
            <div class="px-8 py-4 bg-white border-b border-slate-100 flex gap-3 overflow-x-auto hide-scrollbar scroll-smooth dark:border-slate-800 dark:bg-slate-900">
                <button wire:click="filterByCategory(null)" class="flex-shrink-0 px-6 py-2 {{ $activeCategoryId == null ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }} rounded-xl text-[10px] font-bold uppercase tracking-wider transition-all dark:text-slate-400 dark:bg-slate-800">All Culinary</button>
                @foreach($categories as $category)
                <button wire:click="filterByCategory({{ $category->id }})" class="flex-shrink-0 px-6 py-2 {{ $activeCategoryId == $category->id ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }} rounded-xl text-[10px] font-bold uppercase tracking-wider transition-all dark:text-slate-400 dark:bg-slate-800">{{ $category->name }}</button>
                @endforeach
            </div>

            <!-- Product Grid -->
            <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                    @foreach($items as $item)
                    <button wire:click="addToCart({{ $item->id }})" wire:loading.attr="disabled" wire:loading.class="opacity-75 scale-95 pointer-events-none" wire:target="addToCart({{ $item->id }})" class="group bg-white rounded-2xl overflow-hidden border border-slate-200 hover:shadow-xl hover:border-indigo-300 transition-all active:scale-95 text-left relative dark:bg-slate-900 dark:border-slate-700">
                        <div class="h-32 bg-slate-50 relative dark:bg-slate-900">
                            @if($item->image)
                                <img src="{{ Storage::url($item->image) }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-200">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif
                            <div class="absolute top-2 right-2 px-2 py-0.5 {{ $item->is_veg ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-red-50 text-red-600 border-red-100' }} rounded text-[8px] font-bold border dark:bg-emerald-900/20 dark:border-red-800/50 dark:bg-red-900/20 dark:text-red-400 dark:text-emerald-400 dark:border-emerald-800/50">
                                {{ $item->is_veg ? 'VEG' : 'NON-VEG' }}
                            </div>
                            <!-- Loading Spinner Overlay -->
                            <div wire:loading.class.remove="hidden" wire:loading.class="flex" wire:target="addToCart({{ $item->id }})" class="hidden absolute inset-0 bg-white/50 backdrop-blur-sm items-center justify-center">
                                <svg class="animate-spin w-8 h-8 text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>
                        </div>
                        <div class="p-4">
                            <h4 class="text-[11px] font-bold text-slate-800 leading-tight h-8 line-clamp-2 mb-2 group-hover:text-indigo-600 transition-colors dark:text-slate-200">{{ $item->name }}</h4>
                            <p class="text-xs font-black text-indigo-600 tracking-tight">Rs. {{ number_format($item->price, 0) }}</p>
                        </div>
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Column: Order Summary (Cart) -->
        <div class="w-full lg:w-[400px] h-[40vh] lg:h-auto flex-shrink-0 bg-white flex flex-col shadow-[-20px_0_40px_rgba(0,0,0,0.02)] z-10 dark:bg-slate-900">
            <div class="p-8 border-b border-slate-100 flex items-center justify-between dark:border-slate-800">
                <div>
                    <h3 class="text-xl font-bold text-slate-900 tracking-tight dark:text-slate-100">Stay Summary</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ count($cart) }} Selections</p>
                </div>
                <div class="w-12 h-12 bg-slate-900 text-white rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-8 space-y-5 custom-scrollbar bg-slate-50/30 dark:bg-slate-900/30">
                @forelse($cart as $index => $item)
                <div class="p-5 bg-white rounded-2xl border border-slate-200 shadow-sm group hover:border-indigo-300 transition-all dark:bg-slate-900 dark:border-slate-700">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1 pr-4">
                            <h4 class="text-xs font-bold text-slate-800 leading-tight dark:text-slate-200">{{ $item['name'] }}</h4>
                            <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest">Rs. {{ number_format($item['price'], 0) }}</p>
                        </div>
                        <button wire:click="removeFromCart({{ $index }})" class="text-slate-300 hover:text-red-500 transition-colors p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center bg-slate-100 rounded-lg p-1 dark:bg-slate-800">
                            <button wire:click="decrementQuantity({{ $index }})" wire:loading.attr="disabled" class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-white hover:text-indigo-600 rounded-md transition-all disabled:opacity-50 dark:text-slate-400">-</button>
                            <span class="w-10 text-center text-xs font-bold text-slate-700 dark:text-slate-300">{{ $item['quantity'] }}</span>
                            <button wire:click="incrementQuantity({{ $index }})" wire:loading.attr="disabled" class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-white hover:text-indigo-600 rounded-md transition-all disabled:opacity-50 dark:text-slate-400">+</button>
                        </div>
                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100">Rs. {{ number_format($item['price'] * $item['quantity'], 0) }}</span>
                    </div>
                </div>
                @empty
                <div class="h-full flex flex-col items-center justify-center py-24 text-slate-300 opacity-60">
                    <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-6 border border-slate-200 dark:border-slate-700 dark:bg-slate-800">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-slate-400">Cart is empty</p>
                </div>
                @endforelse
            </div>

            <!-- Footer Billing -->
            @if(!empty($cart))
            <div class="p-8 bg-white border-t border-slate-200 shadow-[0_-20px_40px_rgba(0,0,0,0.02)] dark:bg-slate-900 dark:border-slate-700">
                @if($orderError)
                <div class="flex items-center gap-2 p-3 bg-red-50 border border-red-200 rounded-xl mb-4 dark:border-red-800 dark:bg-red-900/20">
                    <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-xs font-semibold text-red-700 dark:text-red-400">{{ $orderError }}</span>
                </div>
                @endif
                <div class="flex justify-between items-center mb-8">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Order Total</span>
                    <span class="text-3xl font-black text-slate-900 tracking-tight dark:text-slate-100">Rs. {{ number_format(collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']), 0) }}</span>
                </div>
                <button wire:click="placeOrder" wire:loading.attr="disabled" class="w-full py-5 bg-indigo-600 text-white rounded-2xl font-bold shadow-xl shadow-indigo-100 hover:bg-indigo-700 active:scale-95 transition-all uppercase tracking-widest text-xs flex items-center justify-center gap-2 disabled:opacity-75 disabled:cursor-not-allowed disabled:active:scale-100">
                    <span wire:loading.remove wire:target="placeOrder">Confirm Service Order</span>
                    <span wire:loading wire:target="placeOrder" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Processing...
                    </span>
                </button>
            </div>
            @endif
        </div>
    </div>
    @endif

    <style>
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    </style>
</div>
