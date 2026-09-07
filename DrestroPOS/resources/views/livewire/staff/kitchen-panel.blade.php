<div class="min-h-[calc(100vh-4rem)] flex flex-col bg-slate-50 dark:bg-slate-950 -mx-4 sm:-mx-6 lg:-mx-8 -mt-8 p-3 sm:p-4 md:p-6 lg:p-8" wire:poll.5s>
    <!-- Header Stats Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 sm:mb-6 gap-3">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>🍳 Kitchen & Bar Orders</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Real-time live ticket queue</p>
        </div>
        <div class="flex gap-3 sm:gap-4">
            <div class="bg-white dark:bg-slate-900 px-5 py-2.5 rounded-2xl flex flex-col items-center justify-center border border-slate-200 dark:border-slate-800 shadow-sm">
                <span class="text-2xl font-black text-orange-500">{{ $this->pendingItems->where('status', 'pending')->count() }}</span>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pending</span>
            </div>
            <div class="bg-white dark:bg-slate-900 px-5 py-2.5 rounded-2xl flex flex-col items-center justify-center border border-slate-200 dark:border-slate-800 shadow-sm">
                <span class="text-2xl font-black text-emerald-500">{{ $this->pendingItems->where('status', 'preparing')->count() }}</span>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Preparing</span>
            </div>
        </div>
    </div>

    @if($this->pendingItems->isEmpty())
        <div class="flex-1 flex flex-col items-center justify-center bg-white dark:bg-slate-900 rounded-3xl border-2 border-dashed border-slate-200 dark:border-slate-800 p-12 text-center my-auto">
            <div class="w-20 h-20 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4 text-3xl shadow-inner">
                ✨
            </div>
            <h3 class="text-xl font-black text-slate-700 dark:text-slate-200">Kitchen Queue is Clear!</h3>
            <p class="text-slate-400 text-sm mt-1">Waiting for incoming customer or waiter orders...</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6 overflow-y-auto pb-12 pr-1 custom-scrollbar">
            @php
                $groupedItems = $this->pendingItems->groupBy('order_id');
            @endphp

            @foreach($groupedItems as $orderId => $items)
                @php 
                    $order = $items->first()->order;
                    $timeAgo = $order->created_at->diffForHumans();
                    $isOld = $order->created_at->diffInMinutes(now()) > 15;
                @endphp
                <div class="bg-white dark:bg-slate-900 rounded-3xl border-2 {{ $isOld ? 'border-red-400 shadow-lg shadow-red-500/10' : 'border-slate-200 dark:border-slate-800 shadow-md hover:shadow-lg' }} overflow-hidden flex flex-col transition-all">
                    
                    <!-- Ticket Header -->
                    <div class="{{ $isOld ? 'bg-red-50 dark:bg-red-950/30 border-b border-red-100 dark:border-red-900/50' : 'bg-slate-50 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800' }} p-4 sm:p-5 flex justify-between items-start">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xl font-black text-slate-900 dark:text-white">
                                    @if($order->table)
                                        {{ $order->table->type === 'room' ? 'Room ' : 'Table ' }}{{ $order->table->name }}
                                        @if($order->guest_info) <span class="text-slate-500 text-xs font-bold">({{ $order->guest_info }})</span> @endif
                                    @else
                                        Takeaway
                                        @if($order->guest_info) <span class="text-slate-500 text-xs font-bold">({{ $order->guest_info }})</span> @endif
                                    @endif
                                </span>
                                <span class="px-2.5 py-0.5 rounded-lg text-xs font-black bg-slate-900 text-white dark:bg-slate-700">
                                    {{ $order->token_number ?? '#' . substr($order->order_number, -4) }}
                                </span>
                            </div>
                            <div class="text-xs font-bold {{ $isOld ? 'text-red-500' : 'text-slate-500 dark:text-slate-400' }} flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $timeAgo }}
                            </div>
                        </div>

                        @if($order->special_instructions)
                            <div class="bg-amber-100/80 dark:bg-amber-950/50 border border-amber-300 dark:border-amber-900 text-amber-900 dark:text-amber-300 p-2 rounded-xl text-xs max-w-[50%] flex items-start gap-1 shadow-sm">
                                <span class="font-bold shrink-0">📝</span>
                                <span class="font-bold line-clamp-3 text-[11px] leading-tight" title="{{ $order->special_instructions }}">{{ $order->special_instructions }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Ticket Items List -->
                    <div class="p-3 sm:p-4 space-y-2 flex-1 overflow-y-auto">
                        @foreach($items as $item)
                            <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 flex justify-between items-center hover:bg-slate-100/80 dark:hover:bg-slate-800 transition-colors">
                                <div class="flex items-center gap-3 pr-2">
                                    <div class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800 flex items-center justify-center font-black text-slate-800 dark:text-white text-base border border-slate-200 dark:border-slate-700 shadow-sm shrink-0">
                                        {{ $item->quantity }}<span class="text-[10px] text-slate-400 font-medium ml-0.5">x</span>
                                    </div>
                                    <div>
                                        <h4 class="font-black text-slate-800 dark:text-slate-100 text-base leading-tight">
                                            {{ $item->menuItem->name }}
                                            @if($item->variation_name)
                                                <span class="text-xs font-semibold text-slate-500">({{ $item->variation_name }})</span>
                                            @endif
                                        </h4>
                                        @if($item->special_instructions)
                                            <p class="text-[11px] font-black text-amber-700 dark:text-amber-400 mt-0.5 flex items-center gap-1 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-md border border-amber-200 dark:border-amber-900/50 w-fit">
                                                <span>Note:</span> {{ strtoupper($item->special_instructions) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <div class="shrink-0 ml-2">
                                    @if($item->status == 'pending')
                                        <button wire:click="markPreparing({{ $item->id }})" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md shadow-orange-500/20 active:scale-95 transition-all">
                                            START
                                        </button>
                                    @elseif($item->status == 'preparing')
                                        <button wire:click="markReady({{ $item->id }})" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md shadow-emerald-600/20 active:scale-95 transition-all flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                            READY
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.05); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0, 0, 0, 0.2); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(0, 0, 0, 0.4); }
    </style>
</div>
