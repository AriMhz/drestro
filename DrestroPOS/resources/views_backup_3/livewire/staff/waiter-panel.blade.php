<div class="space-y-4 sm:space-y-6" wire:poll.5s>
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Waiter Dashboard</h2>
            <p class="text-slate-500 text-xs sm:text-sm mt-1">Items ready to be served to tables.</p>
        </div>
        <div class="bg-emerald-100 text-emerald-700 px-3 sm:px-4 py-2 rounded-lg font-bold flex items-center text-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            {{ $this->readyItems->flatten()->count() }} Items Ready
        </div>
    </div>

    @if($this->readyItems->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 sm:p-12 flex flex-col items-center justify-center text-center">
            <div class="w-16 sm:w-20 h-16 sm:h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 sm:w-10 h-8 sm:h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg>
            </div>
            <h3 class="text-lg font-medium text-slate-900">All caught up!</h3>
            <p class="mt-1 text-sm text-slate-500">No items are currently waiting to be served.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            @foreach($this->readyItems as $tableKey => $items)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-slate-50 px-5 py-4 border-b border-slate-200 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center font-bold text-lg">
                                {{ is_numeric($tableKey) ? $tableKey : 'T' }}
                            </div>
                            <h3 class="font-bold text-slate-800 text-lg flex flex-col">
                                <span>{{ $tableKey == 'Takeaway' ? 'Takeaway' : 'Table ' . $tableKey }}</span>
                                @if($items->first()->order->guest_info)
                                    <span class="text-xs text-slate-500 font-medium mt-0.5">Seat/Guest: {{ $items->first()->order->guest_info }}</span>
                                @endif
                            </h3>
                        </div>
                        <button wire:click="markTableServed('{{ $tableKey }}')" class="text-sm font-bold text-emerald-600 hover:text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg transition-colors">
                            Serve All
                        </button>
                    </div>
                    
                    <div class="p-2 space-y-2">
                        @foreach($items as $item)
                            <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-slate-100 hover:border-slate-200 hover:shadow-sm transition-all group">
                                <div class="flex items-center gap-3">
                                    <span class="font-bold text-slate-800">{{ $item->quantity }}x</span>
                                    <div>
                                        <span class="font-medium text-slate-700">{{ $item->menuItem->name }}</span>
                                        @if($item->menuItem->category->department == 'bar')
                                            <span class="ml-2 text-[10px] font-bold uppercase px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded">Bar</span>
                                        @else
                                            <span class="ml-2 text-[10px] font-bold uppercase px-1.5 py-0.5 bg-orange-100 text-orange-700 rounded">Kitchen</span>
                                        @endif
                                    </div>
                                </div>
                                <button wire:click="markServed({{ $item->id }})" class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center hover:bg-emerald-500 hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Audio Notification -->
    <audio id="dingSound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('play-ding-sound', () => {
                let audio = document.getElementById('dingSound');
                if (audio) {
                    audio.play().catch(e => console.log("Audio play prevented by browser interaction rule."));
                }
            });
        });
    </script>
</div>
