<div x-data="{ showCart: false }" class="h-full flex flex-col md:flex-row -mx-4 sm:-mx-6 lg:-mx-8 -mt-8 overflow-hidden relative">

    @if($currentOrder)
    <!-- Order Success Screen -->
    <div class="w-full flex items-center justify-center bg-slate-50 p-8">
        <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="bg-slate-900 p-8 text-center relative overflow-hidden">
                <div class="absolute -top-12 -left-12 w-32 h-32 bg-emerald-500 rounded-full blur-2xl opacity-20"></div>
                <div class="absolute -bottom-12 -right-12 w-32 h-32 bg-emerald-500 rounded-full blur-2xl opacity-20"></div>
                <div class="w-16 h-16 bg-emerald-500/20 rounded-full flex items-center justify-center mx-auto mb-4 border border-emerald-500/30">
                    <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h2 class="text-2xl font-bold text-white">Order Sent to Kitchen</h2>
                <p class="text-emerald-300 text-sm font-bold mt-1 tracking-widest text-xl">Token {{ $currentOrder->token_number }}</p>
            </div>
            <div class="p-6">
                <div class="bg-slate-50 p-4 rounded-xl mb-4">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-slate-500">{{ $currentOrder->table && $currentOrder->table->type === 'room' ? 'Room' : 'Table' }}</span>
                        <span class="font-bold text-slate-800">
                            {{ $currentOrder->table ? ($currentOrder->table->type === 'room' ? 'Room ' : 'Table ') . $currentOrder->table->name : 'Takeaway' }}
                            @if($currentOrder->guest_info)
                                <span class="text-slate-500 font-medium">({{ $currentOrder->guest_info }})</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Total</span>
                        <span class="font-bold text-emerald-600">Rs. {{ number_format($currentOrder->total_amount, 0) }}</span>
                    </div>
                </div>

                <!-- Print Status Feedback -->
                @if($printStatus === 'success')
                <div class="flex items-center gap-2 p-3 bg-emerald-50 border border-emerald-200 rounded-xl mb-4">
                    <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm font-semibold text-emerald-700">Printed successfully</span>
                </div>
                @elseif($printStatus === 'partial')
                <div class="flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 rounded-xl mb-4">
                    <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span class="text-sm font-semibold text-amber-700">Partially printed — {{ $printError }}</span>
                </div>
                @elseif($printStatus === 'failed')
                <div class="flex items-center gap-2 p-3 bg-red-50 border border-red-200 rounded-xl mb-4">
                    <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm font-semibold text-red-700">Print failed — {{ $printError }}</span>
                </div>
                @elseif($printStatus === 'disabled')
                <div class="flex items-center gap-2 p-3 bg-slate-50 border border-slate-200 rounded-xl mb-4">
                    <svg class="w-5 h-5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm font-semibold text-slate-500">Auto-print not configured</span>
                </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <button wire:click="startNewOrder" class="w-full py-3.5 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700 active:scale-[0.98] transition-all shadow-lg">
                        Take Another
                    </button>
                    <a href="/staff/waiter" class="w-full py-3.5 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 active:scale-[0.98] transition-all shadow-lg text-center flex items-center justify-center">
                        Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
    @elseif($tableSelectionMode)
    <!-- Table Selection Screen -->
    <div class="w-full h-full flex flex-col bg-gradient-to-br from-slate-50 to-slate-100 p-8 overflow-y-auto">
        <div class="mb-10 text-center">
            <h2 class="text-4xl font-black bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent">Select Dining Table</h2>
            <p class="text-slate-500 mt-2 text-lg font-medium">Where is this order going?</p>
            
            <div class="mt-6">
                <a href="/staff/waiter" class="inline-flex items-center text-slate-500 hover:text-slate-800 font-bold text-sm transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to Dashboard
                </a>
            </div>
        </div>

        <div class="max-w-5xl mx-auto w-full grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <!-- Takeaway Option -->
            <button wire:click="selectTable('')" class="h-36 bg-white/80 backdrop-blur border border-blue-200 rounded-3xl flex flex-col items-center justify-center gap-3 hover:bg-white hover:border-blue-400 hover:-translate-y-1.5 hover:shadow-xl hover:shadow-blue-500/10 transition-all duration-300 active:scale-95 group shadow-lg">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-100 to-blue-200 text-blue-600 rounded-full flex items-center justify-center group-hover:scale-110 group-hover:shadow-md transition-all duration-300">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
                <span class="font-bold text-slate-800 text-lg group-hover:text-blue-700 transition-colors">Takeaway</span>
            </button>

            <!-- Selection Options -->
            @foreach($this->filteredTables as $t)
            @php 
                $isOccupied = in_array($t->id, $occupiedTableIds); 
                $colorClass = $t->type === 'room' ? 'indigo' : 'emerald';
            @endphp
            <button wire:click="selectTable('{{ $t->id }}')" class="h-36 backdrop-blur rounded-3xl flex flex-col items-center justify-center gap-3 transition-all duration-300 active:scale-95 group shadow-lg relative overflow-hidden {{ $isOccupied ? 'bg-red-50/80 border-2 border-red-300 opacity-75' : 'bg-white/80 border border-slate-200 hover:bg-white hover:border-'.$colorClass.'-400 hover:-translate-y-1.5 hover:shadow-xl hover:shadow-'.$colorClass.'-500/10' }}">
                @if($isOccupied)
                <span class="absolute top-2 right-2 px-2 py-0.5 bg-red-500 text-white text-[10px] font-black rounded-full uppercase tracking-wider">Occupied</span>
                @endif
                <div class="absolute inset-0 bg-gradient-to-br from-{{ $colorClass }}-500/0 to-{{ $colorClass }}-500/0 group-hover:from-{{ $colorClass }}-500/5 group-hover:to-transparent transition-colors"></div>
                <div class="w-14 h-14 {{ $isOccupied ? 'bg-red-100 text-red-600' : 'bg-slate-100 text-slate-600 group-hover:bg-gradient-to-br group-hover:from-'.$colorClass.'-100 group-hover:to-'.$colorClass.'-200 group-hover:text-'.$colorClass.'-700' }} rounded-full flex items-center justify-center group-hover:scale-110 group-hover:shadow-md transition-all duration-300 font-black text-2xl">
                    @if($t->type === 'room')
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    @else
                        {{ $t->name }}
                    @endif
                </div>
                <span class="font-bold {{ $isOccupied ? 'text-red-600' : 'text-slate-700 group-hover:text-'.$colorClass.'-800' }} text-lg transition-colors">{{ $t->type === 'room' ? 'Room ' : 'Table ' }}{{ $t->name }}</span>
            </button>
            @endforeach
        </div>
    </div>
    @else

    <!-- Left: Menu Browser -->
    <div class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50">
        <!-- Top Bar: Table Selector + Search -->
        <div class="bg-white/80 backdrop-blur border-b border-slate-200/60 p-3 md:p-4 flex flex-col sm:flex-row flex-wrap gap-3 sm:gap-4 items-stretch sm:items-center shadow-sm z-10 sticky top-0">
            <div class="flex gap-2 items-center">
                <!-- Table Selector -->
                <div class="flex items-center gap-2 flex-1 sm:flex-initial">
                    <label class="text-xs sm:text-sm font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">Table</label>
                    <div class="relative flex-1 sm:min-w-[140px]">
                        <select wire:model.live="selectedTableId" class="appearance-none w-full pl-4 pr-10 py-2.5 bg-slate-50/50 border border-slate-200 rounded-full text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 outline-none shadow-inner transition-all cursor-pointer">
                            <option value="">Takeaway</option>
                            @foreach($this->filteredTables as $t)
                                <option value="{{ $t->id }}">{{ $t->type === 'room' ? 'Room ' : 'Table ' }}{{ $t->name }}</option>
                            @endforeach
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                <!-- Seat/Guest Info -->
                <div class="flex items-center gap-2 flex-1 sm:flex-initial">
                    <label class="text-xs sm:text-sm font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap hidden sm:inline">Guest</label>
                    <input wire:model="guestInfo" type="text" placeholder="Name / Seat" class="pl-4 pr-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-full text-sm font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 outline-none shadow-inner transition-all w-full sm:w-32 placeholder-slate-400">
                </div>
            </div>

            <!-- Search -->
            <div class="flex-1 relative min-w-0 sm:min-w-[200px]">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input wire:model.live.debounce.300ms="searchQuery" type="text" placeholder="Search menu items..." class="w-full pl-11 pr-4 py-2.5 bg-slate-50/50 border border-slate-200 rounded-full text-sm font-medium focus:bg-white focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 outline-none shadow-inner transition-all placeholder-slate-400">
            </div>
        </div>

        <!-- Category Filter -->
        <div class="bg-white px-4 py-3.5 flex gap-2.5 overflow-x-auto hide-scrollbar border-b border-slate-200/50 shadow-sm z-0">
            <button wire:click="filterByCategory(null)" class="flex-shrink-0 px-5 py-2 {{ $activeCategoryId == null ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/30' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:border-emerald-300' }} rounded-full font-bold text-sm transition-all duration-300 active:scale-95">
                All Menu
            </button>
            @foreach($categories as $category)
            <button wire:click="filterByCategory({{ $category->id }})" class="flex-shrink-0 px-5 py-2 {{ $activeCategoryId == $category->id ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/30' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:border-emerald-300' }} rounded-full font-bold text-sm transition-all duration-300 active:scale-95 flex items-center gap-1.5">
                @if($category->image)
                    <img src="{{ Storage::url($category->image) }}" class="w-5 h-5 rounded-full object-cover">
                @else
                    <span class="text-xs opacity-80">{{ $category->department == 'bar' ? '🍷' : '🍳' }}</span>
                @endif
                {{ $category->name }}
            </button>
            @endforeach
        </div>

        <!-- Menu Items Grid -->
        <div class="flex-1 overflow-y-auto p-4 pb-28">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2 sm:gap-3">
                @foreach($this->filteredItems as $item)
                @php $q = $this->getItemQuantityInCart($item->id); @endphp
                <div class="relative bg-white rounded-2xl overflow-hidden text-left shadow-md border-2 {{ $q > 0 ? 'border-emerald-500' : 'border-slate-100' }} transition-all duration-300">
                    <!-- Image Area -->
                    <div class="relative h-24 sm:h-32 bg-slate-50 overflow-hidden">
                        @if($item->image)
                            <img src="{{ Storage::url($item->image) }}" class="w-full h-full object-cover" alt="{{ $item->name }}">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-200">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                        @endif
                        
                        <!-- Top Corner Badges -->
                        <div class="absolute top-2 left-2 z-30">
                            <!-- Veg Icon -->
                            <span class="w-4 h-4 rounded-sm border {{ $item->is_veg ? 'border-green-600 bg-white' : 'border-red-600 bg-white' }} flex items-center justify-center p-[2px] shadow-sm"><span class="w-full h-full {{ $item->is_veg ? 'bg-green-600' : 'bg-red-600' }} rounded-full"></span></span>
                        </div>

                        @if($q > 0)
                            <!-- BIG RED DELETE BUTTON -->
                            <button wire:click.stop="removeFromCartById({{ $item->id }})" class="absolute top-2 right-2 w-10 h-10 bg-red-600 text-white rounded-full flex items-center justify-center shadow-2xl z-50 hover:bg-red-700 active:scale-90 border-2 border-white transition-all">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>

                            <!-- Quantity Controls (Centered) -->
                            <div class="absolute inset-0 bg-emerald-500/10 flex items-center justify-center z-10">
                                <div class="bg-white rounded-full flex items-center p-1 shadow-xl border-2 border-emerald-500">
                                    <button wire:click.stop="decrementQuantity({{ array_search($item->id, array_column($cart, 'id')) }})" class="w-8 h-8 flex items-center justify-center text-emerald-600 hover:bg-emerald-50 rounded-full transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M20 12H4"></path></svg>
                                    </button>
                                    <span class="px-3 font-black text-slate-900 text-lg">{{ $q }}</span>
                                    <button wire:click.stop="addToCart({{ $item->id }})" class="w-8 h-8 flex items-center justify-center text-emerald-600 hover:bg-emerald-50 rounded-full transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    </button>
                                </div>
                            </div>
                        @else
                            <button wire:click="addToCart({{ $item->id }})" class="absolute bottom-2 right-2 w-10 h-10 bg-emerald-600 text-white rounded-full flex items-center justify-center shadow-lg hover:bg-emerald-700 active:scale-90 transition-all z-10">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            </button>
                        @endif
                    </div>
                    <!-- Info Area -->
                    <div class="p-3 bg-white">
                        <h4 class="font-bold text-slate-800 text-xs leading-tight line-clamp-2 uppercase">{{ $item->name }}</h4>
                        <p class="mt-1 font-black text-emerald-600 text-base">Rs. {{ number_format($item->price, 0) }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            @if($this->filteredItems->isEmpty())
                <div class="flex flex-col items-center justify-center h-40 text-slate-400">
                    <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <p class="font-medium">No items found</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Mobile: Floating Cart Button -->
    <button @click="showCart = true" class="md:hidden fixed bottom-6 right-6 z-50 w-16 h-16 bg-gradient-to-tr from-emerald-600 to-emerald-500 text-white rounded-full shadow-[0_8px_30px_rgba(16,185,129,0.5)] flex items-center justify-center active:scale-90 transition-transform {{ $this->cartCount > 0 ? 'animate-[bounce_2s_infinite]' : '' }}">
        <svg class="w-7 h-7 {{ $this->cartCount > 0 ? 'animate-pulse' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
        @if($this->cartCount > 0)
            <span class="absolute -top-1 -right-1 w-7 h-7 bg-red-500 text-white text-xs font-black rounded-full flex items-center justify-center shadow-md border-2 border-white">{{ $this->cartCount }}</span>
        @endif
    </button>

    <!-- Mobile: Cart Overlay -->
    <div x-show="showCart" x-cloak class="md:hidden fixed inset-0 bg-black/50 z-[60]" @click="showCart = false"></div>

    <!-- Right: Cart Panel -->
    <div :class="showCart ? 'translate-y-0' : 'translate-y-full md:translate-y-0'" class="fixed bottom-0 left-0 right-0 md:relative md:bottom-auto md:left-auto md:right-auto w-full md:w-[340px] max-h-[85vh] md:max-h-none bg-white border-l border-slate-200 flex flex-col md:h-full shadow-[-4px_0_24px_rgba(0,0,0,0.03)] z-[70] md:z-auto rounded-t-3xl md:rounded-none transition-transform duration-300 ease-out">
        <!-- Cart Header -->
        <div class="p-4 border-b border-slate-100 bg-white rounded-t-3xl md:rounded-none">
            <!-- Mobile drag handle -->
            <div class="w-10 h-1 bg-slate-300 rounded-full mx-auto mb-3 md:hidden"></div>
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-800">Current Order</h2>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 bg-slate-100 text-slate-600 rounded-full text-xs font-bold">{{ $this->cartCount }} items</span>
                    <button @click="showCart = false" class="md:hidden w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>
            @if($selectedTableId)
                @php $selectedTable = \App\Models\Table::find($selectedTableId); @endphp
                <div class="mt-2 flex items-center gap-2 px-3 py-2 bg-emerald-50 border border-emerald-100 rounded-lg">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                    <span class="text-sm font-semibold text-emerald-700">
                        {{ $selectedTable ? ($selectedTable->type === 'room' ? 'Room ' : 'Table ') . $selectedTable->name : '' }}
                        {{ $guestInfo ? '(' . $guestInfo . ')' : '' }}
                    </span>
                </div>
            @else
                <div class="mt-2 flex items-center gap-2 px-3 py-2 bg-blue-50 border border-blue-100 rounded-lg">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span class="text-sm font-semibold text-blue-700">Takeaway Order</span>
                </div>
            @endif
        </div>

        <!-- Cart Items -->
        <div class="flex-1 overflow-y-auto p-3 space-y-2">
            @if(empty($cart))
                <div class="h-full flex flex-col items-center justify-center text-slate-400 py-12">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3 border-2 border-dashed border-slate-200">
                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <p class="font-medium text-sm">Tap items to add</p>
                </div>
            @else
                @foreach($cart as $index => $item)
                <div class="group/item flex items-center justify-between p-3 bg-white rounded-xl border border-slate-100 hover:border-emerald-100 hover:bg-emerald-50/30 transition-all">
                    <div class="flex-1 min-w-0 pr-3">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-sm {{ $item['is_veg'] ? 'bg-green-500' : 'bg-red-500' }} flex-shrink-0"></span>
                            <h4 class="font-semibold text-slate-800 text-sm truncate">{{ $item['name'] }}</h4>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Rs. {{ number_format($item['price'], 0) }} each</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center bg-slate-100 rounded-lg p-0.5">
                            <button wire:click="decrementQuantity({{ $index }})" wire:loading.attr="disabled" class="w-7 h-7 flex items-center justify-center text-slate-600 hover:bg-white rounded-md transition-all active:scale-90 disabled:opacity-50">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"></path></svg>
                            </button>
                            <span class="w-7 text-center font-bold text-sm">{{ $item['quantity'] }}</span>
                            <button wire:click="incrementQuantity({{ $index }})" wire:loading.attr="disabled" class="w-7 h-7 flex items-center justify-center text-slate-600 hover:bg-white rounded-md transition-all active:scale-90 disabled:opacity-50">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            </button>
                        </div>
                        <button wire:click="removeFromCart({{ $index }})" class="text-slate-300 hover:text-red-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
                @endforeach

                <!-- Notes -->
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <textarea wire:model="orderNotes" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm resize-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" rows="2" placeholder="Special instructions... (e.g. extra spicy)"></textarea>
                </div>
            @endif
        </div>

        <!-- Cart Footer -->
        @if(!empty($cart))
        <div class="p-4 bg-white border-t border-slate-100">
            @if($orderError)
            <div class="flex items-center gap-2 p-3 bg-red-50 border border-red-200 rounded-xl mb-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-sm font-semibold text-red-700">{{ $orderError }}</span>
            </div>
            @endif
            <div class="flex justify-between items-center mb-4">
                <span class="font-bold text-slate-800">Total</span>
                <span class="text-2xl font-black text-slate-900">Rs. {{ number_format($this->cartTotal, 0) }}</span>
            </div>
            <button wire:click="placeOrder" wire:loading.attr="disabled" class="w-full py-3.5 bg-emerald-600 text-white rounded-xl font-bold shadow-lg shadow-emerald-600/20 hover:bg-emerald-700 active:scale-[0.98] transition-all flex items-center justify-center gap-2 text-base disabled:opacity-75 disabled:cursor-not-allowed disabled:active:scale-100">
                <span wire:loading.remove wire:target="placeOrder" class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Send to Kitchen
                </span>
                <span wire:loading wire:target="placeOrder" class="flex items-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Processing...
                </span>
            </button>
        </div>
        @endif
    </div>

    @endif

    <style>
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        [x-cloak] { display: none !important; }
    </style>
</div>
