<div>
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-black text-slate-800 dark:text-white tracking-tight">Ordered Goods</h1>
        <p class="text-sm text-slate-500 mt-1 dark:text-slate-400">View all the hardware and POS equipment you have purchased.</p>
    </div>

    @if($isLoading)
        <div class="flex flex-col items-center justify-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-slate-900 dark:border-white mb-4"></div>
            <p class="text-slate-500 dark:text-slate-400 font-medium">Loading your orders...</p>
        </div>
    @elseif($errorMessage)
        <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-2xl p-6 text-center">
            <div class="w-12 h-12 bg-red-100 dark:bg-red-500/20 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-red-800 dark:text-red-400 mb-2">Could not load orders</h3>
            <p class="text-red-600 dark:text-red-300 text-sm mb-4">{{ $errorMessage }}</p>
            <button wire:click="fetchOrders" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-sm transition-colors text-sm">
                Try Again
            </button>
        </div>
    @elseif(empty($orders))
        <div class="bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] rounded-2xl p-12 text-center shadow-sm">
            <div class="w-20 h-20 bg-slate-50 dark:bg-[#222] rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">No Orders Found</h3>
            <p class="text-slate-500 dark:text-slate-400 text-sm max-w-md mx-auto mb-6">
                It looks like you haven't placed any hardware or equipment orders associated with your restaurant's contact number.
            </p>
            <a href="https://drestro.com/products" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-slate-900 font-bold rounded-xl transition-all shadow-sm">
                Shop Hardware
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
        </div>
    @else
        <div class="space-y-6">
            @foreach($orders as $order)
                <div class="bg-white dark:bg-[#111111] border border-slate-200 dark:border-[#333333] rounded-2xl overflow-hidden shadow-sm">
                    <!-- Order Header -->
                    <div class="bg-slate-50 dark:bg-[#1A1A1A] border-b border-slate-200 dark:border-[#333333] p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Order Placed</h3>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold 
                                    @if($order['status'] === 'COMPLETED') bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400
                                    @elseif($order['status'] === 'PENDING') bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400
                                    @elseif($order['status'] === 'CANCELLED') bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400
                                    @else bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400 @endif">
                                    {{ $order['status'] }}
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-mono">
                                {{ \Carbon\Carbon::parse($order['createdAt'])->format('M j, Y h:i A') }}
                            </p>
                        </div>
                        <div class="sm:text-right">
                            <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider font-bold mb-1">Total Amount</p>
                            <p class="text-lg font-black text-slate-800 dark:text-white">Rs. {{ number_format($order['totalPrice']) }}</p>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="p-4 sm:p-6">
                        <div class="space-y-4">
                            @foreach($order['items'] as $item)
                                <div class="flex items-center gap-4 py-2 border-b border-slate-100 dark:border-[#222] last:border-0 last:pb-0">
                                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-slate-50 dark:bg-[#222] rounded-xl border border-slate-200 dark:border-[#333] flex items-center justify-center overflow-hidden shrink-0">
                                        @if(isset($item['image']) && $item['image'])
                                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-contain p-2">
                                        @else
                                            <svg class="w-6 h-6 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm sm:text-base font-bold text-slate-800 dark:text-slate-200 truncate">{{ $item['name'] }}</h4>
                                        <div class="flex items-center gap-3 mt-1">
                                            <span class="text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded-lg">Qty: {{ $item['quantity'] }}</span>
                                            <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">Rs. {{ number_format($item['price']) }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right hidden sm:block">
                                        <p class="text-sm font-bold text-slate-800 dark:text-white">Rs. {{ number_format($item['price'] * $item['quantity']) }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Delivery Details -->
                    <div class="bg-slate-50/50 dark:bg-[#151515] p-4 sm:px-6 sm:py-4 border-t border-slate-100 dark:border-[#222] text-sm text-slate-600 dark:text-slate-400">
                        <span class="font-bold text-slate-800 dark:text-slate-300">Shipped To:</span> {{ $order['fullName'] }}, {{ $order['phone'] }} - {{ $order['address'] }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
