<div class="h-[calc(100vh-4rem)] flex flex-col bg-slate-900 -mx-4 sm:-mx-6 lg:-mx-8 -mt-8 p-3 sm:p-4 md:p-6 lg:p-8" wire:poll.5s>
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 sm:mb-6 gap-3">
        <div>
            <h1 class="text-xl sm:text-3xl font-bold text-white tracking-tight">{{ str_replace('App\Livewire\Staff\\', '', get_class($this)) == 'KitchenPanel' ? 'Kitchen' : 'Bar' }} Display <span class="{{ str_replace('App\Livewire\Staff\\', '', get_class($this)) == 'KitchenPanel' ? 'text-emerald-500' : 'text-blue-500' }}">System</span></h1>
            <p class="text-slate-400 mt-1 text-xs sm:text-base">Live {{ str_replace('App\Livewire\Staff\\', '', get_class($this)) == 'KitchenPanel' ? 'food' : 'beverage' }} orders. Auto-refreshes every 5 seconds.</p>
        </div>
        <div class="flex gap-3 sm:gap-4">
            <div class="bg-slate-800 px-4 py-2 rounded-lg flex flex-col items-center justify-center border border-slate-700">
                <span class="text-xl font-bold text-orange-400">{{ $this->pendingItems->where('status', 'pending')->count() }}</span>
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Pending</span>
            </div>
            <div class="bg-slate-800 px-4 py-2 rounded-lg flex flex-col items-center justify-center border border-slate-700">
                <span class="text-xl font-bold text-emerald-400">{{ $this->pendingItems->where('status', 'preparing')->count() }}</span>
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Preparing</span>
            </div>
        </div>
    </div>

    @if($this->pendingItems->isEmpty())
        <div class="flex-1 flex flex-col items-center justify-center bg-slate-800/50 rounded-2xl border border-slate-700 border-dashed">
            <svg class="w-20 h-20 text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            <h2 class="text-2xl font-semibold text-slate-400">Kitchen is clear</h2>
            <p class="text-slate-500 mt-2">Waiting for new food orders...</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6 overflow-y-auto pb-8 pr-1 sm:pr-2 custom-scrollbar">
            @php
                // Group items by Order ID to show tickets
                $groupedItems = $this->pendingItems->groupBy('order_id');
            @endphp

            @foreach($groupedItems as $orderId => $items)
                @php 
                    $order = $items->first()->order;
                    $timeAgo = $order->created_at->diffForHumans();
                    $isOld = $order->created_at->diffInMinutes(now()) > 15;
                @endphp
                <div class="bg-slate-800 rounded-2xl border {{ $isOld ? 'border-red-500/50 shadow-[0_0_15px_rgba(239,68,68,0.1)]' : 'border-slate-700' }} overflow-hidden flex flex-col h-fit">
                    <!-- Ticket Header -->
                    <div class="{{ $isOld ? 'bg-red-500/10' : 'bg-slate-700/50' }} p-4 border-b border-slate-700 flex justify-between items-start">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-lg font-bold text-white">
                                    @if($order->table)
                                        {{ $order->table->type === 'room' ? 'Room ' : 'Table ' }}{{ $order->table->name }}
                                        @if($order->guest_info) <span class="text-slate-400 text-sm font-medium">({{ $order->guest_info }})</span> @endif
                                    @else
                                        Takeaway
                                        @if($order->guest_info) <span class="text-slate-400 text-sm font-medium">({{ $order->guest_info }})</span> @endif
                                    @endif
                                </span>
                                <span class="px-2 py-0.5 rounded text-xs font-bold bg-slate-600 text-slate-300">{{ $order->token_number ?? '#' . substr($order->order_number, -4) }}</span>
                            </div>
                            <div class="text-sm font-medium {{ $isOld ? 'text-red-400' : 'text-slate-400' }} flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $timeAgo }}
                            </div>
                        </div>
                        @if($order->special_instructions)
                            <div class="bg-amber-500/10 border border-amber-500/20 text-amber-400 p-2 rounded-lg text-xs max-w-[50%] flex items-start gap-1.5">
                                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <span class="font-medium line-clamp-3" title="{{ $order->special_instructions }}">{{ $order->special_instructions }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Ticket Items -->
                    <div class="flex-1 overflow-y-auto px-1 py-1">
                        <div class="bg-slate-900/40 rounded-xl border border-slate-700/30 overflow-hidden">
                            @foreach($items as $item)
                                <div class="p-3 {{ !$loop->last ? 'border-b border-slate-700/30' : '' }} hover:bg-slate-700/20 transition-colors group">
                                    <div class="flex justify-between items-start">
                                        <div class="flex items-start gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center font-bold text-white shrink-0 border border-slate-700 shadow-sm">
                                                {{ $item->quantity }}<span class="text-[10px] text-slate-500 ml-0.5 font-medium">x</span>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-slate-100 text-lg leading-tight group-hover:text-emerald-400 transition-colors">{{ $item->menuItem->name }}</h4>
                                                @if($item->special_instructions)
                                                    <p class="text-[11px] font-bold text-amber-500 mt-1 flex items-center tracking-tight bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20 w-fit">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                        {{ strtoupper($item->special_instructions) }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="shrink-0 ml-2">
                                            @if($item->status == 'pending')
                                                <button wire:click="markPreparing({{ $item->id }})" class="px-3 py-1.5 bg-orange-600 hover:bg-orange-500 text-white rounded-lg text-xs font-black uppercase tracking-wider shadow-lg shadow-orange-900/20 active:scale-[0.95] transition-all">
                                                    Start
                                                </button>
                                            @elseif($item->status == 'preparing')
                                                <button wire:click="markReady({{ $item->id }})" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-black uppercase tracking-wider shadow-lg shadow-emerald-900/20 active:scale-[0.95] transition-all flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                    Ready
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(30, 41, 59, 0.5); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(71, 85, 105, 0.8); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(100, 116, 139, 1); }
    </style>
</div>
