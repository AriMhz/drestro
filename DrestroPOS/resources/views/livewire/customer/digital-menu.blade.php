<div class="min-h-screen bg-slate-50 flex flex-col md:flex-row relative dark:bg-slate-900" x-data="{ addingToCart: null }">
    
    @if($currentOrder)
    <!-- Order Tracking View (Live Polling for Waiter Confirmation) -->
    <div class="w-full min-h-screen flex items-center justify-center bg-slate-50 p-4 md:p-8 animate-fade-in z-50 dark:bg-slate-900" wire:poll.3s="checkCurrentOrder">
        <div class="max-w-lg w-full bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-100 dark:border-slate-800 dark:bg-slate-900">
            
            @if($currentOrder->status === 'pending_approval')
                <!-- 1. WAITING FOR WAITER CONFIRMATION -->
                <div class="bg-gradient-to-br from-amber-500 to-orange-600 p-8 text-center relative overflow-hidden text-white">
                    <div class="w-20 h-20 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center mx-auto mb-4 border border-white/30 shadow-lg animate-pulse">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-black uppercase tracking-wider mb-2">Awaiting Waiter Confirmation</span>
                    <h2 class="text-2xl font-black mb-1">Order Sent to Waiter!</h2>
                    <p class="text-amber-100 text-xs font-medium max-w-sm mx-auto">
                        Our staff is reviewing your order for <strong>{{ $table ? 'Table ' . $table->name : 'your table' }}</strong>. It will be sent to the kitchen once confirmed.
                    </p>
                </div>
            @elseif($currentOrder->status === 'cancelled')
                <!-- 2. CANCELLED / REJECTED -->
                <div class="bg-gradient-to-br from-rose-600 to-red-700 p-8 text-center relative overflow-hidden text-white">
                    <div class="w-20 h-20 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center mx-auto mb-4 border border-white/30 shadow-lg">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </div>
                    <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-black uppercase tracking-wider mb-2">Order Not Accepted</span>
                    <h2 class="text-2xl font-black mb-1">Order Cancelled</h2>
                    <p class="text-rose-100 text-xs font-medium max-w-sm mx-auto">
                        This order was not approved. Please call a waiter for assistance.
                    </p>
                </div>
            @else
                <!-- 3. APPROVED & IN KITCHEN -->
                <div class="bg-gradient-to-br from-emerald-600 to-teal-700 p-8 text-center relative overflow-hidden text-white">
                    <div class="w-20 h-20 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center mx-auto mb-4 border border-white/30 shadow-lg">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-black uppercase tracking-wider mb-2">Confirmed by Waiter</span>
                    <h2 class="text-2xl font-black mb-1">Cooking in Kitchen! 👨‍🍳</h2>
                    <p class="text-emerald-100 text-xs font-medium tracking-wide">ORDER #{{ $currentOrder->order_number }}</p>
                </div>
            @endif
            
            <div class="p-6 sm:p-8">
                <!-- Status Stepper -->
                @if($currentOrder->status !== 'cancelled')
                <div class="relative mb-8">
                    <div class="absolute top-1/2 left-0 w-full h-1 bg-slate-100 -translate-y-1/2 rounded-full dark:bg-slate-800"></div>
                    <div class="absolute top-1/2 left-0 h-1 bg-emerald-500 -translate-y-1/2 rounded-full transition-all duration-700" style="width: {{ $currentOrder->status == 'pending_approval' ? '15%' : (in_array($currentOrder->status, ['in_kitchen', 'preparing', 'confirmed']) ? '50%' : '100%') }}"></div>
                    
                    <div class="relative flex justify-between">
                        <!-- Step 1: Waiter -->
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center border-4 border-white shadow-sm {{ $currentOrder->status === 'pending_approval' ? 'bg-amber-500 text-white animate-bounce' : 'bg-emerald-500 text-white' }} dark:border-slate-900">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <span class="text-[11px] font-bold text-slate-700 mt-2 dark:text-slate-300">{{ $currentOrder->status === 'pending_approval' ? 'Verifying' : 'Confirmed' }}</span>
                        </div>
                        <!-- Step 2: Kitchen -->
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center border-4 border-white shadow-sm {{ in_array($currentOrder->status, ['in_kitchen', 'preparing', 'ready', 'served', 'completed']) ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-400' }} dark:border-slate-900 dark:bg-slate-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                            </div>
                            <span class="text-[11px] font-bold {{ in_array($currentOrder->status, ['in_kitchen', 'preparing', 'ready', 'served', 'completed']) ? 'text-slate-700 dark:text-slate-300' : 'text-slate-400' }} mt-2">Kitchen</span>
                        </div>
                        <!-- Step 3: Served -->
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center border-4 border-white shadow-sm {{ in_array($currentOrder->status, ['ready', 'served', 'completed']) ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-400' }} dark:border-slate-900 dark:bg-slate-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <span class="text-[11px] font-bold {{ in_array($currentOrder->status, ['ready', 'served', 'completed']) ? 'text-slate-700 dark:text-slate-300' : 'text-slate-400' }} mt-2">Ready</span>
                        </div>
                    </div>
                </div>
                @endif

                <div class="bg-slate-50 p-4 rounded-2xl mb-6 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-slate-800 border-b border-slate-200 pb-2 mb-3 dark:border-slate-700 dark:text-slate-200 flex justify-between items-center text-sm">
                        <span>Ordered Items</span>
                        <span class="text-xs font-normal text-slate-500">{{ $table ? 'Table ' . $table->name : 'Dine-in' }}</span>
                    </h3>
                    <div class="space-y-2">
                        @foreach($currentOrder->items as $orderItem)
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600 dark:text-slate-400"><span class="font-bold text-slate-800 dark:text-slate-200">{{ $orderItem->quantity }}x</span> {{ $orderItem->menuItem->name ?? 'Dish' }}</span>
                                <span class="text-slate-800 font-bold dark:text-slate-200">Rs. {{ number_format($orderItem->subtotal, 0) }}</span>
                            </div>
                        @endforeach
                    </div>
                    @if($currentOrder->special_instructions)
                    <div class="mt-3 pt-3 border-t border-slate-200/60 dark:border-slate-700/60 text-xs text-slate-500">
                        <strong>Note:</strong> {{ $currentOrder->special_instructions }}
                    </div>
                    @endif
                    <div class="flex justify-between items-center mt-4 pt-3 border-t border-slate-200 dark:border-slate-700">
                        <span class="font-bold text-slate-800 dark:text-slate-200">Estimated Total</span>
                        <span class="font-black text-xl text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($currentOrder->total_amount, 0) }}</span>
                    </div>
                </div>

                <button wire:click="startNewOrder" class="w-full py-3.5 bg-slate-900 hover:bg-slate-800 text-white rounded-2xl font-bold text-sm shadow-lg shadow-slate-900/10 active:scale-95 transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Order More / New Items
                </button>
            </div>
        </div>
    </div>
    @else

    <!-- Main Menu Area -->
    <div class="flex-1 md:pr-[400px] transition-all pb-28 md:pb-0">
        <!-- Premium Hero / Header Section -->
        <div class="relative h-64 md:h-80 bg-slate-900 md:rounded-br-[60px] rounded-b-[40px] overflow-hidden shadow-xl">
            <!-- Glassmorphism Background Elements -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-emerald-500/30 blur-[80px]"></div>
                <div class="absolute top-10 -right-10 w-72 h-72 rounded-full bg-amber-500/20 blur-[60px]"></div>
            </div>
            
            <div class="relative z-10 p-6 md:p-10 flex flex-col h-full justify-between pb-8">
                <!-- Top Nav Bar -->
                <div class="flex items-center justify-between w-full max-w-5xl mx-auto">
                    <div class="px-4 py-1.5 bg-white/10 backdrop-blur-md rounded-full border border-white/10 flex items-center">
                        <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse mr-2"></div>
                        <span class="text-xs font-semibold text-white uppercase tracking-wider">
                            @if($table) Table {{ $table->name }} @else Takeaway @endif
                        </span>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-white/10 backdrop-blur-md border border-white/10 flex items-center justify-center text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </div>
                </div>
                
                <div class="max-w-5xl mx-auto w-full mt-auto">
                    <h1 class="text-4xl md:text-6xl font-[Outfit] font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white to-slate-300 leading-tight">
                        {{ $restaurant ? $restaurant->name : 'Drestro' }}
                    </h1>
                    <p class="text-slate-300 text-base md:text-lg mt-2 font-medium">Explore our delicious offerings.</p>
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto w-full">
            <!-- Premium Category Scroller -->
            <div class="px-2 mt-8 sticky top-0 z-20 bg-slate-50/80 backdrop-blur-xl py-4 border-b border-slate-200/50">
                <div class="flex overflow-x-auto hide-scrollbar gap-3 px-4 snap-x">
                    <button wire:click="filterByCategory(null)" class="snap-start flex-shrink-0 px-6 py-3 {{ $activeCategoryId == null ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/20' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }} rounded-full font-bold text-sm shadow-sm border transition-all active:scale-95 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-400">
                        All Items
                    </button>
                    @foreach($categories as $category)
                    <button wire:click="filterByCategory({{ $category->id }})" class="snap-start flex-shrink-0 px-6 py-3 {{ $activeCategoryId == $category->id ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/20' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }} rounded-full font-bold text-sm shadow-sm border transition-all active:scale-95 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-400">
                        {{ $category->name }}
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Menu Items Grid -->
            <div class="px-6 mt-6 space-y-6 md:grid md:grid-cols-2 lg:grid-cols-3 md:gap-6 md:space-y-0">
                @if($items->isEmpty())
                    <div class="col-span-full text-center py-20">
                        <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 dark:bg-slate-800">
                            <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-slate-200">Menu Coming Soon</h3>
                        <p class="text-slate-500 mt-2 dark:text-slate-400">We are currently updating our delicious offerings.</p>
                    </div>
                @else
                    @foreach($items as $item)
                        @if($item->menu_category_id == $activeCategoryId || $activeCategoryId == null)
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-100/60 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group dark:bg-slate-900">
                            <!-- Image Area -->
                            <div class="relative h-48 w-full bg-slate-100 overflow-hidden dark:bg-slate-800">
                                @if($item->image)
                                    <img src="{{ Storage::url($item->image) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" alt="{{ $item->name }}">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-300 bg-slate-100 dark:bg-slate-800">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                @endif
                                
                                <!-- Veg/NonVeg Indicator -->
                                <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-2 py-1 rounded-md shadow-sm border border-white/20 flex items-center dark:bg-slate-900/90">
                                    <span class="w-3 h-3 rounded-sm border {{ $item->is_veg ? 'border-green-600' : 'border-red-600' }} flex items-center justify-center p-[2px]"><span class="w-full h-full {{ $item->is_veg ? 'bg-green-600' : 'bg-red-600' }} rounded-full"></span></span>
                                </div>
                            </div>
                            
                            <!-- Content Area -->
                            <div class="p-5">
                                <h3 class="font-bold text-lg text-slate-800 leading-tight mb-2 dark:text-slate-200">{{ $item->name }}</h3>
                                <p class="text-sm text-slate-500 line-clamp-2 min-h-[40px] dark:text-slate-400">{{ $item->description }}</p>
                                
                                <div class="flex items-center justify-between mt-5 pt-4 border-t border-slate-100 dark:border-slate-800">
                                    <span class="font-black text-xl text-slate-900 dark:text-slate-100">Rs. {{ number_format($item->price, 0) }}</span>
                                    
                                    <button 
                                        wire:click="addToCart({{ $item->id }})" 
                                        @click="addingToCart = {{ $item->id }}; setTimeout(() => addingToCart = null, 300)"
                                        class="relative overflow-hidden w-10 h-10 rounded-full bg-slate-900 text-white flex items-center justify-center hover:bg-emerald-600 active:scale-90 transition-all shadow-md group-hover:shadow-xl"
                                    >
                                        <svg x-show="addingToCart !== {{ $item->id }}" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                        
                                        <!-- Check animation when clicked -->
                                        <svg x-cloak x-show="addingToCart === {{ $item->id }}" class="w-5 h-5 animate-scale-in" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <!-- Mobile Floating Cart Button (Glassmorphism) -->
    @if($this->cartCount > 0 && !$showCart)
    <div class="md:hidden fixed bottom-6 left-0 right-0 px-6 z-20 animate-fade-up">
        <button wire:click="toggleCart" class="w-full py-4 bg-slate-900/90 backdrop-blur-xl border border-white/10 text-white rounded-2xl font-bold shadow-2xl flex items-center justify-between px-6 transition-transform active:scale-95">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center mr-3 text-sm font-black shadow-inner border border-emerald-400">{{ $this->cartCount }}</div>
                <span>View Order</span>
            </div>
            <span class="text-emerald-300">Rs. {{ number_format($this->cartTotal, 0) }}</span>
        </button>
    </div>
    @endif

    <!-- Cart Sidebar / Drawer -->
    <div class="fixed md:right-0 inset-y-0 right-0 z-30 w-full md:w-[400px] bg-white/95 backdrop-blur-2xl shadow-2xl transform transition-all duration-500 ease-out {{ $showCart ? 'translate-x-0' : 'translate-x-full' }} md:translate-x-0 md:static {{ $this->cartCount == 0 ? 'hidden md:flex' : 'flex' }} flex-col border-l border-slate-200/50">
        
        <!-- Cart Header -->
        <div class="px-6 py-6 border-b border-slate-100 flex items-center justify-between bg-transparent z-10 dark:border-slate-800">
            <h2 class="text-2xl font-[Outfit] font-bold text-slate-800 flex items-center dark:text-slate-200">
                Your Order
                <span class="ml-3 px-2.5 py-0.5 bg-slate-100 text-slate-600 rounded-full text-xs font-bold dark:text-slate-400 dark:bg-slate-800">{{ $this->cartCount }}</span>
            </h2>
            <button wire:click="toggleCart" class="md:hidden p-2 text-slate-400 hover:text-slate-800 rounded-full hover:bg-slate-100 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Cart Items -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            @if(empty($cart))
                <div class="h-full flex flex-col items-center justify-center text-slate-400">
                    <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-4 border-2 border-dashed border-slate-200 dark:bg-slate-900 dark:border-slate-700">
                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <p class="font-medium text-slate-600 dark:text-slate-400">Your tray is empty.</p>
                    <p class="text-sm mt-1">Let's add some delicious food!</p>
                </div>
            @else
                <div class="space-y-5">
                    @foreach($cart as $index => $item)
                    <div class="flex items-start justify-between bg-white p-4 rounded-2xl border border-slate-100 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex-1 pr-4">
                            <div class="flex items-center">
                                <span class="w-3 h-3 rounded-sm border {{ $item['is_veg'] ? 'border-green-600' : 'border-red-600' }} flex items-center justify-center p-[1px] mr-2"><span class="w-full h-full {{ $item['is_veg'] ? 'bg-green-600' : 'bg-red-600' }} rounded-full"></span></span>
                                <h4 class="font-bold text-slate-800 dark:text-slate-200">{{ $item['name'] }}</h4>
                            </div>
                            <div class="text-slate-500 font-medium text-sm mt-1 dark:text-slate-400">Rs. {{ number_format($item['price'], 0) }}</div>
                        </div>
                        <div class="flex flex-col items-end">
                            <div class="text-slate-900 font-bold mb-3 dark:text-slate-100">Rs. {{ number_format($item['price'] * $item['quantity'], 0) }}</div>
                            <div class="flex items-center bg-slate-100 rounded-full p-1 border border-slate-200 dark:border-slate-700 dark:bg-slate-800">
                                <button wire:click="decrementQuantity({{ $index }})" class="w-8 h-8 flex items-center justify-center text-slate-600 hover:bg-white rounded-full shadow-sm transition-all active:scale-90 dark:text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"></path></svg>
                                </button>
                                <span class="w-8 text-center font-bold text-sm text-slate-800 dark:text-slate-200">{{ $item['quantity'] }}</span>
                                <button wire:click="incrementQuantity({{ $index }})" class="w-8 h-8 flex items-center justify-center text-slate-600 hover:bg-white rounded-full shadow-sm transition-all active:scale-90 dark:text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <!-- Remarks & Special Instructions -->
                <div class="pt-5 mt-5 border-t border-slate-200 dark:border-slate-800" x-data="{ addRemark(text) { $wire.set('orderNotes', ($wire.orderNotes ? $wire.orderNotes + ', ' : '') + text); } }">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-black text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                            <span class="text-base">📝</span>
                            <span>Order Remarks / Special Notes</span>
                        </label>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Optional</span>
                    </div>

                    <!-- Quick Suggestion Tags -->
                    <div class="flex flex-wrap gap-1.5 mb-2.5">
                        <button type="button" @click="addRemark('Less Spicy')" class="px-2.5 py-1 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 dark:bg-slate-800 dark:text-slate-300 text-[11px] font-bold rounded-lg border border-slate-200 dark:border-slate-700 transition-colors">
                            🌶️ Less Spicy
                        </button>
                        <button type="button" @click="addRemark('Extra Spicy')" class="px-2.5 py-1 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 dark:bg-slate-800 dark:text-slate-300 text-[11px] font-bold rounded-lg border border-slate-200 dark:border-slate-700 transition-colors">
                            🔥 Extra Spicy
                        </button>
                        <button type="button" @click="addRemark('No Onion/Garlic')" class="px-2.5 py-1 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 dark:bg-slate-800 dark:text-slate-300 text-[11px] font-bold rounded-lg border border-slate-200 dark:border-slate-700 transition-colors">
                            🧅 No Onion
                        </button>
                        <button type="button" @click="addRemark('Extra Cheese')" class="px-2.5 py-1 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 dark:bg-slate-800 dark:text-slate-300 text-[11px] font-bold rounded-lg border border-slate-200 dark:border-slate-700 transition-colors">
                            🧀 Extra Cheese
                        </button>
                    </div>

                    <textarea wire:model.live="orderNotes" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all text-sm resize-none dark:bg-slate-900 dark:border-slate-700 text-slate-800 dark:text-slate-200 placeholder-slate-400 font-medium" rows="2.5" placeholder="Special instructions for chef (e.g. less oil, extra spicy, no ice...)"></textarea>
                </div>
            @endif
        </div>

        <!-- Cart Footer -->
        @if(!empty($cart))
        <div class="p-6 bg-white border-t border-slate-100 shadow-[0_-10px_40px_rgba(0,0,0,0.05)] dark:border-slate-800 dark:bg-slate-900">
            <div class="flex justify-between text-slate-500 text-sm mb-2 font-medium dark:text-slate-400">
                <span>Subtotal</span>
                <span>Rs. {{ number_format($this->cartTotal, 0) }}</span>
            </div>
            <div class="flex justify-between text-slate-500 text-sm mb-4 font-medium dark:text-slate-400">
                <span>Taxes & Fees</span>
                <span>Calculated at counter</span>
            </div>
            <div class="flex justify-between items-end mb-6">
                <span class="text-slate-800 font-bold text-lg dark:text-slate-200">Total</span>
                <span class="text-3xl font-[Outfit] font-black text-slate-900 dark:text-slate-100">Rs. {{ number_format($this->cartTotal, 0) }}</span>
            </div>
            <button wire:click="placeOrder" class="w-full py-4 bg-emerald-600 text-white rounded-2xl font-bold shadow-md hover:bg-emerald-700 active:scale-[0.98] transition-all flex justify-between items-center px-8 relative overflow-hidden group">
                <div class="absolute inset-0 bg-white/20 translate-x-[-100%] group-hover:animate-[shimmer_1.5s_infinite]"></div>
                <span class="text-lg relative z-10">Place Order</span>
                <svg class="w-6 h-6 relative z-10 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </div>
        @endif
    </div>

    @endif

    <!-- Custom Alpine/CSS Animations -->
    <style>
        [x-cloak] { display: none !important; }
        
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        @keyframes scale-in {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); opacity: 1; }
        }
        .animate-scale-in { animation: scale-in 0.3s ease-out forwards; }
        
        @keyframes fade-up {
            0% { transform: translateY(20px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        .animate-fade-up { animation: fade-up 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        
        @keyframes fade-in {
            0% { opacity: 0; transform: scale(0.98); }
            100% { opacity: 1; transform: scale(1); }
        }
        .animate-fade-in { animation: fade-in 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        
        @keyframes shimmer {
            100% { transform: translateX(100%); }
        }
    </style>
</div>
