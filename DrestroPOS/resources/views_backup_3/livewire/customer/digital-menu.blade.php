<div class="min-h-screen bg-slate-50 flex flex-col md:flex-row relative" x-data="{ addingToCart: null }">
    
    @if($currentOrder)
    <!-- Order Tracking View -->
    <div class="w-full min-h-screen flex items-center justify-center bg-slate-50 p-4 md:p-8 animate-fade-in z-50">
        <div class="max-w-lg w-full bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-100">
            <div class="bg-slate-900 p-8 text-center relative overflow-hidden">
                <!-- Abstract blobs -->
                <div class="absolute -top-12 -left-12 w-32 h-32 bg-emerald-500 rounded-full blur-2xl opacity-20"></div>
                <div class="absolute -bottom-12 -right-12 w-32 h-32 bg-emerald-500 rounded-full blur-2xl opacity-20"></div>
                
                <div class="w-20 h-20 bg-emerald-500/20 rounded-full flex items-center justify-center mx-auto mb-4 border border-emerald-500/30">
                    <svg class="w-10 h-10 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h2 class="text-3xl font-bold text-white mb-1">Order Received</h2>
                <p class="text-emerald-300 text-sm font-medium tracking-wide">ORDER #{{ $currentOrder->order_number }}</p>
            </div>
            
            <div class="p-8">
                <!-- Status Tracker -->
                <div class="relative mb-10">
                    <div class="absolute top-1/2 left-0 w-full h-1 bg-slate-100 -translate-y-1/2 rounded-full"></div>
                    <!-- Progress Bar (33%, 66%, 100%) -->
                    <div class="absolute top-1/2 left-0 h-1 bg-emerald-500 -translate-y-1/2 rounded-full transition-all duration-1000" style="width: {{ $currentOrder->status == 'pending' ? '15%' : ($currentOrder->status == 'preparing' ? '50%' : '100%') }}"></div>
                    
                    <div class="relative flex justify-between">
                        <!-- Pending -->
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center border-4 border-white shadow-sm {{ in_array($currentOrder->status, ['pending', 'preparing', 'ready', 'completed']) ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-400' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <span class="text-xs font-semibold text-slate-700 mt-2">Received</span>
                        </div>
                        <!-- Preparing -->
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center border-4 border-white shadow-sm {{ in_array($currentOrder->status, ['preparing', 'ready', 'completed']) ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-400' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <span class="text-xs font-semibold {{ in_array($currentOrder->status, ['preparing', 'ready', 'completed']) ? 'text-slate-700' : 'text-slate-400' }} mt-2">Preparing</span>
                        </div>
                        <!-- Ready -->
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center border-4 border-white shadow-sm {{ in_array($currentOrder->status, ['ready', 'completed']) ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-400' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                            </div>
                            <span class="text-xs font-semibold {{ in_array($currentOrder->status, ['ready', 'completed']) ? 'text-slate-700' : 'text-slate-400' }} mt-2">Ready</span>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 p-4 rounded-2xl mb-6">
                    <h3 class="font-bold text-slate-800 border-b border-slate-200 pb-2 mb-3">Order Details</h3>
                    <div class="space-y-2">
                        @foreach($currentOrder->items as $orderItem)
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600"><span class="font-medium text-slate-800">{{ $orderItem->quantity }}x</span> {{ $orderItem->menuItem->name }}</span>
                                <span class="text-slate-800 font-medium">Rs. {{ number_format($orderItem->subtotal, 0) }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-between items-center mt-4 pt-4 border-t border-slate-200">
                        <span class="font-bold text-slate-800">Total Paid</span>
                        <span class="font-bold text-xl text-emerald-600">Rs. {{ number_format($currentOrder->total_amount, 0) }}</span>
                    </div>
                </div>

                <button wire:click="startNewOrder" class="w-full py-4 bg-slate-900 text-white rounded-xl font-bold shadow-lg shadow-slate-900/20 hover:bg-slate-800 active:scale-95 transition-all">
                    Start New Order
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
                    <button wire:click="filterByCategory(null)" class="snap-start flex-shrink-0 px-6 py-3 {{ $activeCategoryId == null ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/20' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }} rounded-full font-bold text-sm shadow-sm border transition-all active:scale-95">
                        All Items
                    </button>
                    @foreach($categories as $category)
                    <button wire:click="filterByCategory({{ $category->id }})" class="snap-start flex-shrink-0 px-6 py-3 {{ $activeCategoryId == $category->id ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/20' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }} rounded-full font-bold text-sm shadow-sm border transition-all active:scale-95">
                        {{ $category->name }}
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Menu Items Grid -->
            <div class="px-6 mt-6 space-y-6 md:grid md:grid-cols-2 lg:grid-cols-3 md:gap-6 md:space-y-0">
                @if($items->isEmpty())
                    <div class="col-span-full text-center py-20">
                        <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800">Menu Coming Soon</h3>
                        <p class="text-slate-500 mt-2">We are currently updating our delicious offerings.</p>
                    </div>
                @else
                    @foreach($items as $item)
                        @if($item->menu_category_id == $activeCategoryId || $activeCategoryId == null)
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-100/60 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                            <!-- Image Area -->
                            <div class="relative h-48 w-full bg-slate-100 overflow-hidden">
                                @if($item->image)
                                    <img src="{{ Storage::url($item->image) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" alt="{{ $item->name }}">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-300 bg-slate-100">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                @endif
                                
                                <!-- Veg/NonVeg Indicator -->
                                <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-2 py-1 rounded-md shadow-sm border border-white/20 flex items-center">
                                    <span class="w-3 h-3 rounded-sm border {{ $item->is_veg ? 'border-green-600' : 'border-red-600' }} flex items-center justify-center p-[2px]"><span class="w-full h-full {{ $item->is_veg ? 'bg-green-600' : 'bg-red-600' }} rounded-full"></span></span>
                                </div>
                            </div>
                            
                            <!-- Content Area -->
                            <div class="p-5">
                                <h3 class="font-bold text-lg text-slate-800 leading-tight mb-2">{{ $item->name }}</h3>
                                <p class="text-sm text-slate-500 line-clamp-2 min-h-[40px]">{{ $item->description }}</p>
                                
                                <div class="flex items-center justify-between mt-5 pt-4 border-t border-slate-100">
                                    <span class="font-black text-xl text-slate-900">Rs. {{ number_format($item->price, 0) }}</span>
                                    
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
        <button wire:click="toggleCart" class="w-full py-4 bg-slate-900/90 backdrop-blur-xl border border-white/10 text-white rounded-2xl font-bold shadow-2xl shadow-emerald-900/20 flex items-center justify-between px-6 transition-transform active:scale-95">
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
        <div class="px-6 py-6 border-b border-slate-100 flex items-center justify-between bg-transparent z-10">
            <h2 class="text-2xl font-[Outfit] font-bold text-slate-800 flex items-center">
                Your Order
                <span class="ml-3 px-2.5 py-0.5 bg-slate-100 text-slate-600 rounded-full text-xs font-bold">{{ $this->cartCount }}</span>
            </h2>
            <button wire:click="toggleCart" class="md:hidden p-2 text-slate-400 hover:text-slate-800 rounded-full hover:bg-slate-100 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Cart Items -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            @if(empty($cart))
                <div class="h-full flex flex-col items-center justify-center text-slate-400">
                    <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-4 border-2 border-dashed border-slate-200">
                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <p class="font-medium text-slate-600">Your tray is empty.</p>
                    <p class="text-sm mt-1">Let's add some delicious food!</p>
                </div>
            @else
                <div class="space-y-5">
                    @foreach($cart as $index => $item)
                    <div class="flex items-start justify-between bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
                        <div class="flex-1 pr-4">
                            <div class="flex items-center">
                                <span class="w-3 h-3 rounded-sm border {{ $item['is_veg'] ? 'border-green-600' : 'border-red-600' }} flex items-center justify-center p-[1px] mr-2"><span class="w-full h-full {{ $item['is_veg'] ? 'bg-green-600' : 'bg-red-600' }} rounded-full"></span></span>
                                <h4 class="font-bold text-slate-800">{{ $item['name'] }}</h4>
                            </div>
                            <div class="text-slate-500 font-medium text-sm mt-1">Rs. {{ number_format($item['price'], 0) }}</div>
                        </div>
                        <div class="flex flex-col items-end">
                            <div class="text-slate-900 font-bold mb-3">Rs. {{ number_format($item['price'] * $item['quantity'], 0) }}</div>
                            <div class="flex items-center bg-slate-100 rounded-full p-1 border border-slate-200">
                                <button wire:click="decrementQuantity({{ $index }})" class="w-8 h-8 flex items-center justify-center text-slate-600 hover:bg-white rounded-full shadow-sm transition-all active:scale-90">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"></path></svg>
                                </button>
                                <span class="w-8 text-center font-bold text-sm text-slate-800">{{ $item['quantity'] }}</span>
                                <button wire:click="incrementQuantity({{ $index }})" class="w-8 h-8 flex items-center justify-center text-slate-600 hover:bg-white rounded-full shadow-sm transition-all active:scale-90">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <!-- Special Instructions -->
                <div class="pt-6 mt-6 border-t border-slate-100">
                    <label class="block text-sm font-bold text-slate-700 mb-2 flex items-center">
                        <svg class="w-4 h-4 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Cooking Instructions
                    </label>
                    <textarea wire:model="orderNotes" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all text-sm resize-none" rows="3" placeholder="E.g. Make it extra spicy, no onions..."></textarea>
                </div>
            @endif
        </div>

        <!-- Cart Footer -->
        @if(!empty($cart))
        <div class="p-6 bg-white border-t border-slate-100 shadow-[0_-10px_40px_rgba(0,0,0,0.05)]">
            <div class="flex justify-between text-slate-500 text-sm mb-2 font-medium">
                <span>Subtotal</span>
                <span>Rs. {{ number_format($this->cartTotal, 0) }}</span>
            </div>
            <div class="flex justify-between text-slate-500 text-sm mb-4 font-medium">
                <span>Taxes & Fees</span>
                <span>Calculated at counter</span>
            </div>
            <div class="flex justify-between items-end mb-6">
                <span class="text-slate-800 font-bold text-lg">Total</span>
                <span class="text-3xl font-[Outfit] font-black text-slate-900">Rs. {{ number_format($this->cartTotal, 0) }}</span>
            </div>
            <button wire:click="placeOrder" class="w-full py-4 bg-emerald-600 text-white rounded-2xl font-bold shadow-xl shadow-emerald-600/30 hover:bg-emerald-700 hover:shadow-emerald-600/40 active:scale-[0.98] transition-all flex justify-between items-center px-8 relative overflow-hidden group">
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
