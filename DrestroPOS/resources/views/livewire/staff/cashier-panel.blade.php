<div class="h-auto md:h-full flex flex-col -m-4 sm:-m-6 lg:-m-8">
    @php
        $calendarType = strtoupper(current_restaurant()->date_calendar_type ?? 'AD');
        $startFormatted = '';
        $endFormatted = '';
        
        if ($calendarType === 'BS') {
            $nc = new \App\Helpers\NepaliCalendar();
            // Start Date BS
            $startParts = explode('-', \Carbon\Carbon::parse($this->startDate)->format('Y-m-d'));
            $startBs = $nc->eng_to_nep((int)$startParts[0], (int)$startParts[1], (int)$startParts[2]);
            if ($startBs && isset($startBs['date'])) {
                $startFormatted = "{$startBs['nmonth']} {$startBs['date']}, {$startBs['year']}";
            } else {
                $startFormatted = \Carbon\Carbon::parse($this->startDate)->format('M d, Y');
            }
            
            // End Date BS
            $endParts = explode('-', \Carbon\Carbon::parse($this->endDate)->format('Y-m-d'));
            $endBs = $nc->eng_to_nep((int)$endParts[0], (int)$endParts[1], (int)$endParts[2]);
            if ($endBs && isset($endBs['date'])) {
                $endFormatted = "{$endBs['nmonth']} {$endBs['date']}, {$endBs['year']}";
            } else {
                $endFormatted = \Carbon\Carbon::parse($this->endDate)->format('M d, Y');
            }
        } else {
            $startFormatted = \Carbon\Carbon::parse($this->startDate)->format('M d, Y');
            $endFormatted = \Carbon\Carbon::parse($this->endDate)->format('M d, Y');
        }
    @endphp
    <style>
        @media print {
            @page { margin: 1.5cm; size: A4 portrait; }
            body * { visibility: hidden !important; }
            #pdf-content, #pdf-content * { visibility: visible !important; }
            #pdf-content { position: absolute !important; left: 0 !important; top: 0 !important; width: 100% !important; margin: 0 !important; padding: 0 !important; }
            #pdf-content * { background: transparent !important; color: black !important; box-shadow: none !important; }
            #pdf-content div { border: none !important; border-radius: 0 !important; }
            #pdf-content { font-family: Arial, sans-serif !important; font-size: 11pt !important; line-height: 1.5 !important; }
            #pdf-content p { margin: 0 0 10px 0 !important; }
            #pdf-content h1 { font-size: 20pt !important; font-weight: bold !important; border-bottom: 2px solid black !important; padding-bottom: 5px !important; margin: 0 0 15px 0 !important; display: block !important; }
            #pdf-content h3 { font-size: 13pt !important; font-weight: bold !important; margin: 20px 0 10px 0 !important; display: block !important; }
            #pdf-content table { width: 100% !important; border-collapse: collapse !important; margin: 15px 0 !important; page-break-inside: auto !important; }
            #pdf-content tr { page-break-inside: avoid !important; page-break-after: auto !important; }
            #pdf-content th, #pdf-content td { border: 1px solid #000 !important; padding: 8px !important; text-align: left !important; font-size: 10pt !important; }
            #pdf-content th { font-weight: bold !important; text-transform: uppercase !important; }
            .print-hidden { display: none !important; }
        }
    </style>
    
    <div class="flex-1 flex flex-col md:flex-row overflow-visible md:overflow-hidden bg-slate-50 dark:bg-slate-900">
        
        <!-- Left Side: Active Orders List -->
        <div class="{{ $selectedOrder ? 'hidden md:flex' : 'flex' }} w-full md:w-1/3 lg:w-1/4 bg-white border-b md:border-b-0 md:border-r border-slate-200 flex-col h-auto md:h-full shrink-0 z-10 shadow-[4px_0_24px_rgba(0,0,0,0.02)] dark:bg-slate-900 dark:border-slate-700" wire:poll.10s>
            <div class="p-4 border-b border-slate-100 bg-slate-50/80 backdrop-blur-md sticky top-0 z-20 dark:border-slate-800 dark:bg-slate-900/80">
                <div class="flex bg-slate-200/50 dark:bg-slate-800 p-1 rounded-xl mb-2">
                    <button wire:click="$set('activeTab', 'restaurant')" class="flex-1 py-2 text-[10px] font-black uppercase tracking-widest rounded-lg transition-all {{ $activeTab === 'restaurant' ? 'bg-white text-emerald-600 shadow-sm dark:bg-slate-900 dark:text-emerald-400' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400' }}">
                        Restaurant
                    </button>
                    <button wire:click="$set('activeTab', 'hotel')" class="flex-1 py-2 text-[10px] font-black uppercase tracking-widest rounded-lg transition-all {{ $activeTab === 'hotel' ? 'bg-white text-indigo-600 shadow-sm dark:bg-slate-900 dark:text-indigo-400' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400' }}">
                        Hotel
                    </button>
                </div>
                <!-- Status Filter Tabs -->
                <div class="flex bg-slate-100/80 dark:bg-slate-800 p-0.5 rounded-lg mb-2">
                    <button wire:click="$set('statusFilter', 'active')" class="flex-1 py-1.5 text-[9px] font-bold uppercase tracking-wider rounded-md transition-all {{ $statusFilter === 'active' ? 'bg-white text-amber-600 shadow-sm dark:bg-slate-900 dark:text-amber-400' : 'text-slate-400 hover:text-slate-600 dark:text-slate-400' }}">
                        Active
                    </button>
                    <button wire:click="$set('statusFilter', 'completed')" class="flex-1 py-1.5 text-[9px] font-bold uppercase tracking-wider rounded-md transition-all {{ $statusFilter === 'completed' ? 'bg-white text-emerald-600 shadow-sm dark:bg-slate-900 dark:text-emerald-400' : 'text-slate-400 hover:text-slate-600 dark:text-slate-400' }}">
                        Done
                    </button>
                    <button wire:click="$set('statusFilter', 'cancelled')" class="flex-1 py-1.5 text-[9px] font-bold uppercase tracking-wider rounded-md transition-all {{ $statusFilter === 'cancelled' ? 'bg-white text-red-600 shadow-sm dark:bg-slate-900 dark:text-red-400' : 'text-slate-400 hover:text-slate-600 dark:text-slate-400' }}">
                        Cancelled
                    </button>
                    <button wire:click="$set('statusFilter', 'all')" class="flex-1 py-1.5 text-[9px] font-bold uppercase tracking-wider rounded-md transition-all {{ $statusFilter === 'all' ? 'bg-white text-slate-700 shadow-sm dark:bg-slate-900 dark:text-slate-300' : 'text-slate-400 hover:text-slate-600 dark:text-slate-400' }}">
                        All
                    </button>
                </div>
                <div class="flex justify-between items-center mt-2">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest text-center flex-1">Select an order to view bill</p>
                </div>
            </div>
            
            <div class="flex-1 overflow-visible md:overflow-y-auto p-3 space-y-2">
                @forelse($this->activeOrders as $order)
                    <button wire:click="selectOrder({{ $order->id }})" class="w-full text-left p-4 rounded-xl border {{ $selectedOrder && $selectedOrder->id == $order->id ? 'border-emerald-500 bg-emerald-50 shadow-sm' : 'border-slate-100 hover:border-slate-300 hover:bg-slate-50' }} transition-all relative group dark:border-slate-800 dark:bg-emerald-900/20">
                        <div class="flex justify-between items-start mb-2">
                            <span class="font-bold {{ $selectedOrder && $selectedOrder->id == $order->id ? 'text-emerald-800' : 'text-slate-800' }} dark:text-emerald-300 dark:text-slate-200">
                                {{ $order->table ? ($order->table->type === 'room' ? 'Room ' : 'Table ') . $order->table->name : 'Takeaway' }}
                                <span class="text-[9px] text-slate-400 block font-semibold mt-0.5 uppercase">
                                    {{ \App\Helpers\DateHelper::format($order->created_at) }} | {{ $order->created_at->format('h:i A') }}
                                </span>
                            </span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full 
                                @if($order->status == 'ready') bg-emerald-100 text-emerald-700
                                @elseif($order->status == 'completed') bg-green-100 text-green-700
                                @elseif($order->status == 'cancelled') bg-red-100 text-red-700
                                @elseif($order->status == 'preparing') bg-blue-100 text-blue-700
                                @else bg-amber-100 text-amber-700
                                @endif dark:text-blue-400 dark:text-amber-400 dark:text-red-400 dark:text-emerald-400">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-end">
                            <span class="text-xs text-slate-500 font-medium dark:text-slate-400">#{{ substr($order->order_number, -4) }}</span>
                            <span class="font-bold {{ $selectedOrder && $selectedOrder->id == $order->id ? 'text-emerald-700' : 'text-slate-900' }} dark:text-emerald-400 dark:text-slate-100">
                                @php
                                    $oTax = $order->total_amount * ($restaurant->tax_percent ?? 0) / 100;
                                    $oSc = $order->total_amount * ($restaurant->service_charge_percent ?? 0) / 100;
                                    $oTotal = $order->invoice ? $order->invoice->grand_total : ($order->total_amount + $oTax + $oSc);
                                @endphp
                                Rs. {{ number_format(max(0, $oTotal), 0) }}
                            </span>
                        </div>
                    </button>
                @empty
                    <div class="py-10 text-center">
                        <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3 dark:bg-slate-900">
                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">No active orders</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right Side: Invoice View -->
        <div class="flex flex-1 bg-slate-50/50 flex-col h-auto md:h-full overflow-visible md:overflow-hidden relative dark:bg-slate-900/50">
            @if($selectedOrder)
                <!-- Invoice Header -->
                <div class="bg-white border-b border-slate-200 px-8 py-5 flex justify-between items-center z-10 shadow-sm dark:bg-slate-900 dark:border-slate-700">
                    <div class="flex items-start md:items-center gap-3">
                        <button wire:click="$set('selectedOrder', null)" class="md:hidden mt-1 p-2 -ml-2 bg-slate-100 rounded-lg text-slate-600 hover:bg-slate-200 dark:text-slate-400 dark:bg-slate-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </button>
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-200">
                                {{ $selectedOrder->table ? ($selectedOrder->table->type === 'room' ? 'Room ' : 'Table ') . $selectedOrder->table->name : 'Takeaway' }}
                            </h2>
                            <div class="text-sm text-slate-500 font-medium mt-1 flex items-center gap-3 dark:text-slate-400">
                                <span>Order #{{ $selectedOrder->order_number }}</span>
                                <span class="w-1 h-1 bg-slate-300 rounded-full dark:bg-slate-700"></span>
                                <span>{{ $selectedOrder->created_at->format('h:i A') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <!-- Direct ESC/POS Thermal Print -->
                        <button wire:click="printThermalReceiptDirect" wire:loading.attr="disabled" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-2 shadow-md active:scale-95 disabled:opacity-50" title="Print raw receipt directly to Thermal Network Printer">
                            <span wire:loading.remove wire:target="printThermalReceiptDirect" class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                Direct Print
                            </span>
                            <span wire:loading wire:target="printThermalReceiptDirect" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Printing...
                            </span>
                        </button>
                        
                        <!-- Web Browser Printer Fallback -->
                        <button onclick="printInvoice()" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-2 shadow-md shadow-slate-800/10 active:scale-95" title="Print using web browser window print dialog">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Browser Print
                        </button>
                    </div>
                </div>

                <!-- Session Alerts -->
                @if (session()->has('success'))
                    <div class="bg-emerald-50 border-b border-emerald-200 px-8 py-3 flex items-center gap-3 text-emerald-700 text-xs font-bold transition-all dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="bg-rose-50 border-b border-rose-200 px-8 py-3 flex items-center gap-3 text-rose-700 text-xs font-bold transition-all dark:text-rose-400 dark:bg-rose-900/20">
                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Invoice Content (Printable Area) -->
                <div class="flex-1 overflow-visible md:overflow-y-auto p-8 flex justify-center printable-area" id="pdf-content">
                    <div class="bg-white max-w-lg w-full rounded-2xl shadow-sm border border-slate-200 p-8 h-fit dark:bg-slate-900 dark:border-slate-700" id="invoice">
                        <!-- Restaurant Header -->
                        <div class="text-center mb-6 pb-6 border-b border-dashed border-slate-300 dark:border-slate-600">
                            <h1 class="text-2xl font-black text-slate-900 tracking-tight dark:text-slate-100">{{ $restaurant->name }}</h1>
                            @if($restaurant->address)
                                <p class="text-slate-500 text-xs mt-1 dark:text-slate-400">{{ $restaurant->address }}</p>
                            @endif
                            @if($restaurant->phone)
                                <p class="text-slate-500 text-xs dark:text-slate-400">Phone: {{ $restaurant->phone }}</p>
                            @endif
                            @if($restaurant->pan_number)
                                <p class="text-slate-500 text-xs font-semibold dark:text-slate-400">PAN: {{ $restaurant->pan_number }}</p>
                            @endif

                            <div class="flex items-center justify-center gap-2 my-3 print-hidden">
                                <button wire:click="printBrowserKOT" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold transition-all shadow-sm flex items-center gap-1">
                                    🍳 KOT (Kitchen)
                                </button>
                                <button wire:click="printBrowserBOT" class="px-3 py-1.5 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg text-xs font-bold transition-all shadow-sm flex items-center gap-1">
                                    🍹 BOT (Bar)
                                </button>
                                <button wire:click="printBrowserReceipt" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold transition-all shadow-sm flex items-center gap-1">
                                    🧾 Receipt
                                </button>
                            </div>

                            <p class="text-slate-400 text-xs mt-2">--- INVOICE ---</p>
                        </div>

                        <!-- Order Info -->
                        <div class="mb-4 pb-4 border-b border-dashed border-slate-200 text-xs text-slate-600 space-y-1 dark:text-slate-400 dark:border-slate-700">
                            <div class="flex justify-between">
                                <span>Order #</span>
                                <span class="font-bold">{{ $selectedOrder->order_number }}</span>
                            </div>
                            @if($selectedOrder->token_number)
                            <div class="flex justify-between">
                                <span>Token</span>
                                <span class="font-bold">{{ $selectedOrder->token_number }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <span>Table / Room</span>
                                <span class="font-bold">{{ $selectedOrder->table ? ($selectedOrder->table->type === 'room' ? 'Room ' : 'Table ') . $selectedOrder->table->name : 'Takeaway' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Date</span>
                                <span class="font-bold">{{ \App\Helpers\DateHelper::format($selectedOrder->created_at) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Time</span>
                                <span class="font-bold">{{ $selectedOrder->created_at->format('h:i A') }}</span>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <table class="w-full text-left mb-4">
                            <thead>
                                <tr class="text-xs text-slate-400 uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">
                                    <th class="pb-2 font-semibold">Item</th>
                                    <th class="pb-2 font-semibold text-center w-12">Qty</th>
                                    <th class="pb-2 font-semibold text-right w-16">Amt</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                @foreach($selectedOrder->items as $item)
                                    <tr class="border-b border-slate-50">
                                        <td class="py-2 text-slate-800 font-medium dark:text-slate-200">{{ $item->menuItem->name }}</td>
                                        <td class="py-2 font-bold text-slate-700 text-center dark:text-slate-300">{{ $item->quantity }}</td>
                                        <td class="py-2 text-right font-bold text-slate-700 dark:text-slate-300">{{ number_format($item->subtotal, 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Totals -->
                        @php
                            $grossItemsTotal = $selectedOrder->items()->sum('subtotal');
                            if ($grossItemsTotal <= 0) $grossItemsTotal = $selectedOrder->total_amount;
                            
                            $invoiceDiscount = $selectedOrder->invoice ? $selectedOrder->invoice->discount : (float)$discount;
                            $taxableAmount = max(0, $grossItemsTotal - $invoiceDiscount);

                            $scPercent = $restaurant->service_charge_percent ?? 0;
                            $scAmt = $taxableAmount * $scPercent / 100;
                            $taxableAmountWithSc = $taxableAmount + $scAmt;

                            $taxPercent = $restaurant->tax_percent ?? 0;
                            if ($taxPercent > 0) {
                                $vatAmt = round($taxableAmountWithSc * ($taxPercent / (100 + $taxPercent)), 2);
                                $netSubtotal = round($taxableAmountWithSc - $vatAmt, 2);
                            } else {
                                $vatAmt = 0;
                                $netSubtotal = $taxableAmountWithSc;
                            }

                            $grandTotal = $taxableAmountWithSc;
                        @endphp

                        <div class="border-t border-dashed border-slate-300 pt-3 space-y-1 text-sm dark:border-slate-600">
                            <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                <span>Subtotal</span>
                                <span>{{ $restaurant->currency }} {{ number_format($grossItemsTotal, 0) }}</span>
                            </div>

                            @if($invoiceDiscount > 0)
                            <div class="flex justify-between text-red-600 font-bold dark:text-red-400">
                                <span>Discount</span>
                                <span>- {{ $restaurant->currency }} {{ number_format($invoiceDiscount, 0) }}</span>
                            </div>
                            @endif

                            @if($scPercent > 0)
                            <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                <span>Service Charge ({{ $scPercent }}%)</span>
                                <span>{{ $restaurant->currency }} {{ number_format($scAmt, 0) }}</span>
                            </div>
                            @endif

                            @if($taxPercent > 0)
                            <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400 pt-0.5">
                                <span>Tax (13% Included)</span>
                                <span>{{ $restaurant->currency }} {{ number_format($vatAmt, 0) }}</span>
                            </div>
                            @endif

                            <div class="flex justify-between items-center pt-3 border-t border-slate-200 dark:border-slate-700">
                                <span class="text-lg font-bold text-slate-800 dark:text-slate-200">TOTAL</span>
                                <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $restaurant->currency }} {{ number_format($grandTotal > 0 ? $grandTotal : 0, 0) }}</span>
                            </div>
                        </div>

                        <!-- Payment Info -->
                        @if($selectedOrder->invoice && $selectedOrder->invoice->payment_status === 'paid')
                        <div class="mt-4 pt-3 border-t border-dashed border-slate-300 text-center text-xs text-slate-500 dark:border-slate-600 dark:text-slate-400">
                            <p class="font-bold">PAID via {{ ucfirst($selectedOrder->invoice->payment_provider ?: $selectedOrder->invoice->payment_method) }}</p>
                        </div>
                        @endif
                        
                        <div class="mt-4 pt-3 border-t border-dashed border-slate-300 text-center text-xs text-slate-500 font-medium dark:border-slate-600 dark:text-slate-400">
                            <p>Please collect orginal bill from the counter :) thank you</p>
                        </div>
                    </div>
                </div>

                <!-- Footer Checkout Area -->
                <div class="bg-white border-t border-slate-200 p-6 flex justify-end gap-4 shadow-[0_-4px_24px_rgba(0,0,0,0.02)] dark:bg-slate-900 dark:border-slate-700">
                    <div class="mr-auto">
                        @php
                            $uiGross = $selectedOrder->items()->sum('subtotal');
                            if ($uiGross <= 0) $uiGross = $selectedOrder->total_amount;
                            $uiDiscount = $selectedOrder->invoice ? $selectedOrder->invoice->discount : (float)$discount;
                            $uiTotal = max(0, $uiGross - $uiDiscount);
                            if (($restaurant->service_charge_percent ?? 0) > 0) {
                                $uiTotal += $uiTotal * $restaurant->service_charge_percent / 100;
                            }
                        @endphp
                        <div class="text-3xl font-black text-slate-900 dark:text-slate-100">Rs. {{ number_format($uiTotal > 0 ? $uiTotal : 0, 0) }}</div>
                    </div>
                    @if($selectedOrder->invoice && $selectedOrder->invoice->payment_status === 'paid')
                        <div class="flex items-center gap-3">
                            <span class="px-4 py-2 bg-emerald-100 text-emerald-800 font-bold rounded-lg border border-emerald-200 dark:text-emerald-300 dark:border-emerald-800">
                                Paid via {{ ucfirst($selectedOrder->invoice->payment_provider ?: $selectedOrder->invoice->payment_method) }}
                            </span>
                            @if(!in_array($selectedOrder->status, ['completed', 'cancelled']))
                            <button wire:click="markDelivered" class="px-8 py-3 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl shadow-lg active:scale-95 transition-all text-lg flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Mark as Served
                            </button>
                            @endif
                        </div>
                    @elseif($selectedOrder->status === 'completed')
                        <span class="px-5 py-2.5 bg-green-100 text-green-800 font-bold rounded-xl border border-green-200 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            Completed
                        </span>
                    @elseif($selectedOrder->status === 'cancelled')
                        <span class="px-5 py-2.5 bg-red-100 text-red-800 font-bold rounded-xl border border-red-200 flex items-center gap-2 dark:text-red-300 dark:border-red-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Cancelled
                        </span>
                    @else
                        <button wire:click="cancelOrder" onclick="confirm('Cancel this order?') || event.stopImmediatePropagation()" class="px-6 py-3 bg-red-50 hover:bg-red-100 text-red-600 font-bold rounded-xl border border-red-200 active:scale-95 transition-all text-lg flex items-center dark:border-red-800 dark:text-red-400 dark:bg-red-900/20">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Cancel
                        </button>
                        <button wire:click="openPaymentModal" class="px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md active:scale-95 transition-all text-lg flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Take Payment
                        </button>
                    @endif
                </div>
            @else
                <!-- Printable Sales Summary Report -->
                <div class="h-auto md:h-full flex flex-col overflow-visible md:overflow-hidden bg-slate-50 print:bg-white print:overflow-visible dark:bg-slate-900" id="pdf-content">
                    <!-- Top capsule header (print-hidden) -->
                    <div class="p-6 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4 bg-white shadow-sm print-hidden dark:border-slate-800 dark:bg-slate-900">
                        <div>
                            <h3 class="text-xl font-black text-slate-800 tracking-tight flex items-center gap-2 dark:text-slate-200">
                                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 00-2 2H5a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                Daily Sales Summary
                            </h3>
                            <p class="text-xs text-slate-500 font-medium dark:text-slate-400">Real-time overview of your cash counter sales.</p>
                        </div>
                        
                        <div class="flex flex-wrap items-stretch gap-4 bg-white rounded-2xl border border-slate-200 p-4 shadow-sm print-hidden w-full xl:w-auto mt-2 xl:mt-0 dark:bg-slate-900 dark:border-slate-700">
                            <!-- Section 2: Custom Date Range Picker -->
                            <div wire:ignore wire:key="calendar-picker-cashier" class="flex flex-col gap-1.5"
                                 x-ref="pickerWrap"
                                 x-data="{
                                    pickerOpen: false,
                                    pickingField: 'start', // 'start' or 'end'
                                    bsMap: @js((new \App\Helpers\NepaliCalendar())->_bs ?? []),
                                    nepMonthNames: ['Baisakh', 'Jestha', 'Ashadh', 'Shrawan', 'Bhadra', 'Ashwin', 'Kartik', 'Mangsir', 'Poush', 'Magh', 'Falgun', 'Chaitra'],
                                    engMonthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                                    startDate: '{{ $customStartDate ?? now()->format('Y-m-d') }}',
                                    endDate: '{{ $customEndDate ?? now()->format('Y-m-d') }}',
                                    viewYear: 2083,
                                    viewMonth: 2,
                                    calMode: '{{ strtoupper(current_restaurant()->date_calendar_type ?? 'AD') }}' === 'BS' ? 'bs' : 'ad',
                                    
                                    init() {
                                        this.syncFromSelected();
                                        this.$watch('startDate', (val) => {
                                            $wire.set('customStartDate', val);
                                            $wire.setPeriod('custom');
                                        });
                                        this.$watch('endDate', (val) => {
                                            $wire.set('customEndDate', val);
                                            $wire.setPeriod('custom');
                                        });
                                        
                                        this.$watch('$wire.customStartDate', (val) => {
                                            if (val && val !== this.startDate) {
                                                this.startDate = val;
                                                this.syncFromSelected();
                                            }
                                        });
                                        this.$watch('$wire.customEndDate', (val) => {
                                            if (val && val !== this.endDate) {
                                                this.endDate = val;
                                                this.syncFromSelected();
                                            }
                                        });
                                    },
                                    
                                    syncFromSelected() {
                                        let activeDate = this.pickingField === 'start' ? this.startDate : this.endDate;
                                        if (!activeDate) return;
                                        let parts = activeDate.split('-');
                                        let yy = parseInt(parts[0]);
                                        let mm = parseInt(parts[1]);
                                        let dd = parseInt(parts[2]);
                                        
                                        if (this.calMode === 'bs') {
                                            let nep = this.engToNep(yy, mm, dd);
                                            if (nep) {
                                                this.viewYear = nep.year;
                                                this.viewMonth = nep.month;
                                            }
                                        } else {
                                            this.viewYear = yy;
                                            this.viewMonth = mm;
                                        }
                                    },
                                    
                                    engToNep(yy, mm, dd) {
                                        if (yy < 1944 || yy > 2033) return null;
                                        let monthDays = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
                                        let leapMonthDays = [31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
                                        
                                        let total_eDays = 0;
                                        for (let i = 1944; i < yy; i++) {
                                            let isLeap = (i % 4 === 0 && i % 100 !== 0) || (i % 400 === 0);
                                            total_eDays += isLeap ? 366 : 365;
                                        }
                                        let isLeapActive = (yy % 4 === 0 && yy % 100 !== 0) || (yy % 400 === 0);
                                        let activeMonthDays = isLeapActive ? leapMonthDays : monthDays;
                                        for (let i = 0; i < mm - 1; i++) {
                                            total_eDays += activeMonthDays[i];
                                        }
                                        total_eDays += dd;
                                        
                                        let i = 0;
                                        let j = 9;
                                        let total_nDays = 16;
                                        let m = 9;
                                        let y = 2000;
                                        let day = 6 - 1;
                                        
                                        while (total_eDays > 0) {
                                            let a = this.bsMap[i][j];
                                            total_nDays++;
                                            day++;
                                            if (total_nDays > a) {
                                                m++;
                                                total_nDays = 1;
                                                j++;
                                            }
                                            if (day > 7) day = 1;
                                            if (m > 12) {
                                                y++;
                                                m = 1;
                                            }
                                            if (j > 12) {
                                                j = 1;
                                                i++;
                                            }
                                            total_eDays--;
                                        }
                                        
                                        return { year: y, month: m, day: total_nDays };
                                    },
                                    
                                    nepToEng(nyy, nmm, ndd) {
                                        let def_eyy = 1944;
                                        let def_nyy = 2000;
                                        let def_nmm = 9;
                                        let def_ndd = 17;
                                        
                                        let total_days = 0;
                                        
                                        if (nyy === def_nyy) {
                                            if (nmm === def_nmm) {
                                                total_days = ndd - def_ndd;
                                            } else if (nmm > def_nmm) {
                                                total_days = this.bsMap[0][def_nmm] - def_ndd;
                                                for (let m = def_nmm + 1; m < nmm; m++) {
                                                    total_days += this.bsMap[0][m];
                                                }
                                                total_days += ndd;
                                            }
                                        } else if (nyy > def_nyy) {
                                            total_days = this.bsMap[0][def_nmm] - def_ndd;
                                            for (let m = def_nmm + 1; m <= 12; m++) {
                                                total_days += this.bsMap[0][m];
                                            }
                                            for (let y = 2001; y < nyy; y++) {
                                                let idx = y - 2000;
                                                for (let m = 1; m <= 12; m++) {
                                                    total_days += this.bsMap[idx][m];
                                                }
                                            }
                                            let idx = nyy - 2000;
                                            for (let m = 1; m < nmm; m++) {
                                                total_days += this.bsMap[idx][m];
                                            }
                                            total_days += ndd;
                                        }
                                        
                                        let base = new Date(1944, 0, 1);
                                        base.setDate(base.getDate() + total_days);
                                        
                                        let pad = (n) => n.toString().padStart(2, '0');
                                        return `${base.getFullYear()}-${pad(base.getMonth() + 1)}-${pad(base.getDate())}`;
                                    },
                                    
                                    getGrid() {
                                        if (!this.viewYear || !this.viewMonth) return [];
                                        let grid = [];
                                        
                                        if (this.calMode === 'bs') {
                                            let firstEng = this.nepToEng(this.viewYear, this.viewMonth, 1);
                                            if (!firstEng) return [];
                                            let firstDate = new Date(firstEng.replace(/-/g, '/'));
                                            let startDayOfWeek = firstDate.getDay();
                                            
                                            let daysInMonth = this.bsMap[this.viewYear - 2000][this.viewMonth];
                                            
                                            for (let i = 0; i < startDayOfWeek; i++) {
                                                grid.push({ blank: true });
                                            }
                                            
                                            for (let d = 1; d <= daysInMonth; d++) {
                                                let cellEngDate = new Date(firstDate);
                                                cellEngDate.setDate(firstDate.getDate() + d - 1);
                                                
                                                let pad = (n) => n.toString().padStart(2, '0');
                                                let formattedEng = `${cellEngDate.getFullYear()}-${pad(cellEngDate.getMonth()+1)}-${pad(cellEngDate.getDate())}`;
                                                grid.push({
                                                    blank: false,
                                                    day: d,
                                                    engDay: cellEngDate.getDate(),
                                                    gregorian: formattedEng,
                                                    isToday: this.isToday(formattedEng),
                                                    isSelected: this.startDate === formattedEng || this.endDate === formattedEng,
                                                    isInRange: formattedEng > this.startDate && formattedEng < this.endDate
                                                });
                                            }
                                        } else {
                                            let firstDate = new Date(this.viewYear, this.viewMonth - 1, 1);
                                            let startDayOfWeek = firstDate.getDay();
                                            let daysInMonth = new Date(this.viewYear, this.viewMonth, 0).getDate();
                                            
                                            for (let i = 0; i < startDayOfWeek; i++) {
                                                grid.push({ blank: true });
                                            }
                                            
                                            for (let d = 1; d <= daysInMonth; d++) {
                                                let cellDate = new Date(this.viewYear, this.viewMonth - 1, d);
                                                let pad = (n) => n.toString().padStart(2, '0');
                                                let formattedEng = `${cellDate.getFullYear()}-${pad(cellDate.getMonth()+1)}-${pad(cellDate.getDate())}`;
                                                
                                                let nep = this.engToNep(cellDate.getFullYear(), cellDate.getMonth() + 1, cellDate.getDate());
                                                let cornerBsDay = nep ? nep.day : '';
                                                
                                                grid.push({
                                                    blank: false,
                                                    day: d,
                                                    engDay: cornerBsDay,
                                                    gregorian: formattedEng,
                                                    isToday: this.isToday(formattedEng),
                                                    isSelected: this.startDate === formattedEng || this.endDate === formattedEng,
                                                    isInRange: formattedEng > this.startDate && formattedEng < this.endDate
                                                });
                                            }
                                        }
                                        
                                        return grid;
                                    },
                                    
                                    isToday(gregStr) {
                                        let today = new Date();
                                        let pad = (n) => n.toString().padStart(2, '0');
                                        let todayStr = `${today.getFullYear()}-${pad(today.getMonth() + 1)}-${pad(today.getDate())}`;
                                        return gregStr === todayStr;
                                    },
                                    
                                    prevMonth() {
                                        if (this.viewMonth === 1) {
                                            this.viewMonth = 12;
                                            this.viewYear--;
                                        } else {
                                            this.viewMonth--;
                                        }
                                    },
                                    
                                    nextMonth() {
                                        if (this.viewMonth === 12) {
                                            this.viewMonth = 1;
                                            this.viewYear++;
                                        } else {
                                            this.viewMonth++;
                                        }
                                    },
                                    
                                    selectDay(item) {
                                        if (this.pickingField === 'start') {
                                            this.startDate = item.gregorian;
                                            if (this.startDate > this.endDate) {
                                                this.endDate = this.startDate;
                                            }
                                            this.pickingField = 'end';
                                            this.syncFromSelected();
                                        } else {
                                            this.endDate = item.gregorian;
                                            if (this.endDate < this.startDate) {
                                                this.startDate = this.endDate;
                                            }
                                            this.pickerOpen = false;
                                        }
                                    },
                                    
                                    getFormattedDate(dateVal) {
                                        if (!dateVal) return '';
                                        if (this.calMode === 'bs') {
                                            let parts = dateVal.split('-');
                                            let nep = this.engToNep(parseInt(parts[0]), parseInt(parts[1]), parseInt(parts[2]));
                                            if (nep) return this.nepMonthNames[nep.month - 1] + ' ' + nep.day + ', ' + nep.year;
                                            return dateVal;
                                        } else {
                                            return new Date(dateVal.replace(/-/g, '/')).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'});
                                        }
                                    },
                                    
                                    quickSelect(days) {
                                        let today = new Date();
                                        let start = new Date();
                                        start.setDate(today.getDate() - (days - 1));
                                        
                                        let pad = (n) => n.toString().padStart(2, '0');
                                        this.endDate = `${today.getFullYear()}-${pad(today.getMonth() + 1)}-${pad(today.getDate())}`;
                                        this.startDate = `${start.getFullYear()}-${pad(start.getMonth() + 1)}-${pad(start.getDate())}`;
                                        
                                        this.pickerOpen = false;
                                    }
                                 }" 
                                 @click.outside="pickerOpen = false">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Custom Date Range</span>
                                <div class="flex flex-col md:flex-row items-stretch md:items-center gap-2 bg-slate-100/60 p-1.5 border border-slate-200 rounded-xl dark:bg-slate-800/60 dark:border-slate-700 w-full flex-wrap lg:flex-nowrap">
                                    <div class="flex items-center gap-2 flex-1 min-w-[240px]">
                                        <!-- Date From Input -->
                                        <div @click.stop="pickingField = 'start'; pickerOpen = true; syncFromSelected()" 
                                             :class="pickingField === 'start' && pickerOpen ? 'bg-emerald-50 text-emerald-800 border-emerald-300 ring-2 ring-emerald-500/10' : 'bg-white hover:bg-slate-50 border-slate-200'"
                                             class="flex-1 flex items-center justify-between gap-1.5 px-3 py-1.5 border rounded-lg cursor-pointer transition-all shadow-sm dark:bg-slate-900 dark:border-slate-700">
                                            <span class="text-xs font-black text-slate-700 select-none whitespace-nowrap dark:text-slate-300" x-text="getFormattedDate(startDate)"></span>
                                            <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>

                                        <span class="text-[10px] font-black text-slate-400 text-center select-none">to</span>

                                        <!-- Date To Input -->
                                        <div @click.stop="pickingField = 'end'; pickerOpen = true; syncFromSelected()" 
                                             :class="pickingField === 'end' && pickerOpen ? 'bg-emerald-50 text-emerald-800 border-emerald-300 ring-2 ring-emerald-500/10' : 'bg-white hover:bg-slate-50 border-slate-200'"
                                             class="flex-1 flex items-center justify-between gap-1.5 px-3 py-1.5 border rounded-lg cursor-pointer transition-all shadow-sm dark:bg-slate-900 dark:border-slate-700">
                                            <span class="text-xs font-black text-slate-700 select-none whitespace-nowrap dark:text-slate-300" x-text="getFormattedDate(endDate)"></span>
                                            <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    </div>

                                    <div class="h-[1px] w-full md:h-5 md:w-[1px] bg-slate-200 mx-0.5 dark:bg-slate-800 hidden md:block"></div>

                                    <!-- Quick selectors -->
                                    <div class="flex items-center gap-1 justify-center flex-1 md:flex-none">
                                        <button type="button" @click="quickSelect(7)" class="flex-1 md:flex-none px-4 py-1.5 text-[10px] font-black text-emerald-600 bg-white hover:bg-emerald-50 border border-slate-200 rounded-lg transition-all shadow-sm uppercase tracking-wider flex items-center justify-center dark:bg-slate-900 dark:border-slate-700 dark:text-emerald-400">7d</button>
                                        <button type="button" @click="quickSelect(14)" class="flex-1 md:flex-none px-4 py-1.5 text-[10px] font-black text-emerald-600 bg-white hover:bg-emerald-50 border border-slate-200 rounded-lg transition-all shadow-sm uppercase tracking-wider flex items-center justify-center dark:bg-slate-900 dark:border-slate-700 dark:text-emerald-400">14d</button>
                                        <button type="button" @click="quickSelect(30)" class="flex-1 md:flex-none px-4 py-1.5 text-[10px] font-black text-emerald-600 bg-white hover:bg-emerald-50 border border-slate-200 rounded-lg transition-all shadow-sm uppercase tracking-wider flex items-center justify-center dark:bg-slate-900 dark:border-slate-700 dark:text-emerald-400">30d</button>
                                    </div>
                                </div>
                                
                                <!-- Floating AD/BS Visual Grid Picker -->
                                <div x-show="pickerOpen" x-cloak 
                                     style="position: fixed; z-index: 9999; width: 320px; max-height: calc(100vh - 20px); overflow-y: auto;"
                                     :style="pickerOpen ? {
                                         top: Math.max(10, Math.min(window.innerHeight - 440, (window.innerHeight - $refs.pickerWrap.getBoundingClientRect().bottom < 440 && $refs.pickerWrap.getBoundingClientRect().top > (window.innerHeight - $refs.pickerWrap.getBoundingClientRect().bottom)) ? ($refs.pickerWrap.getBoundingClientRect().top - 435) : ($refs.pickerWrap.getBoundingClientRect().bottom + 8))) + 'px',
                                         bottom: 'auto',
                                         left: Math.max(8, Math.min(window.innerWidth - 328, $refs.pickerWrap.getBoundingClientRect().left)) + 'px'
                                     } : {}"
                                     class="bg-white border border-slate-200 rounded-2xl shadow-2xl p-4 space-y-3 dark:bg-slate-900 dark:border-slate-700">
                                    
                                    <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-slate-800">
                                        <span class="text-xs font-black text-slate-800 uppercase tracking-wider dark:text-slate-200" x-text="calMode === 'bs' ? 'Nepali Calendar (BS)' : 'Gregorian Calendar (AD)'"></span>
                                        @php
                                            $currentCal = strtoupper(current_restaurant()->date_calendar_type ?? 'AD');
                                        @endphp
                                        @if(auth()->user()->role === 'super_admin')
                                            <div class="flex items-center bg-slate-100 border border-slate-200 p-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest select-none shrink-0 dark:border-slate-700 dark:bg-slate-800">
                                                <a href="{{ route('toggle-calendar', 'ad') }}" class="px-2 py-0.5 rounded transition-all {{ $currentCal === 'AD' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }} dark:text-slate-400">AD</a>
                                                <a href="{{ route('toggle-calendar', 'bs') }}" class="px-2 py-0.5 rounded transition-all {{ $currentCal === 'BS' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }} dark:text-slate-400">BS</a>
                                            </div>
                                        @else
                                            <div class="flex items-center bg-slate-50 border border-slate-200 px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest select-none shrink-0 text-slate-500 font-bold dark:bg-slate-900 dark:text-slate-400 dark:border-slate-700">
                                                Calendar: {{ $currentCal }}
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Interactive Visual Calendar Grid -->
                                    <div class="space-y-2">
                                        <!-- Month Selector Header -->
                                        <div class="flex items-center justify-between pb-1">
                                            <button type="button" @click.stop="prevMonth()" class="p-1 hover:bg-slate-100 rounded-lg text-slate-500 hover:text-slate-700 transition-colors dark:text-slate-400">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                            </button>
                                            <div class="text-sm font-black text-slate-800 flex items-center gap-1 select-none dark:text-slate-200">
                                                <span x-text="calMode === 'bs' ? nepMonthNames[viewMonth - 1] : engMonthNames[viewMonth - 1]"></span>
                                                <span x-text="viewYear" class="text-xs text-slate-400 font-black"></span>
                                            </div>
                                            <button type="button" @click.stop="nextMonth()" class="p-1 hover:bg-slate-100 rounded-lg text-slate-500 hover:text-slate-700 transition-colors dark:text-slate-400">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            </button>
                                        </div>
                                        
                                        <!-- Day of Week Headers -->
                                        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; text-align: center;" class="text-[10px] font-black text-slate-400 uppercase">
                                            <div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div>
                                        </div>
                                        
                                        <!-- Date Grid -->
                                        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px;">
                                            <template x-for="item in getGrid()">
                                                <div style="width: 36px; height: 36px;">
                                                    <template x-if="item.blank">
                                                        <div style="width: 36px; height: 36px;"></div>
                                                    </template>
                                                    <template x-if="!item.blank">
                                                        <button type="button" 
                                                            @click.stop="selectDay(item)"
                                                            :class="item.isSelected ? 'bg-emerald-600 text-white shadow-md' : (item.isInRange ? 'bg-emerald-50 text-emerald-800' : (item.isToday ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'hover:bg-slate-100 text-slate-700'))"
                                                            style="width:36px;height:36px;border-radius:8px;position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;transition:all 0.15s;">
                                                            
                                                            <!-- Main Date Number (BS day in BS mode, AD day in AD mode) -->
                                                            <span style="font-size:13px;font-weight:900;line-height:1;" x-text="item.day"></span>
                                                            
                                                            <!-- Small corner AD day number — ONLY shown in BS mode so user can see corresponding AD date -->
                                                            <template x-if="calMode === 'bs' && item.engDay">
                                                                <span style="font-size:7px;line-height:1;font-weight:700;position:absolute;bottom:2px;right:3px;opacity:0.6;" 
                                                                      x-text="item.engDay"></span>
                                                            </template>
                                                        </button>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    
                                    <!-- Selected Info Row -->
                                    <div class="pt-2 border-t border-slate-100 flex flex-col gap-1 text-[10px] select-none dark:border-slate-800">
                                        <div class="flex justify-between items-center text-[9px] text-slate-400 font-bold uppercase">
                                            <span>Picking Field</span>
                                            <span class="text-emerald-600 font-black uppercase tracking-wider dark:text-emerald-400" x-text="pickingField === 'start' ? 'Selecting From' : 'Selecting To'"></span>
                                        </div>
                                        <div class="flex justify-between items-center font-bold text-slate-700 dark:text-slate-300">
                                            <span class="text-slate-500 dark:text-slate-400">Range:</span>
                                            <span class="text-slate-800 dark:text-slate-200" x-text="getFormattedDate(startDate) + ' → ' + getFormattedDate(endDate)"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="w-[1px] bg-slate-200 hidden sm:block self-stretch my-1 dark:bg-slate-800"></div>
                            
                            <!-- Section 3: Actions -->
                            <div class="flex flex-col gap-1.5 justify-between">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Actions</span>
                                <div class="flex flex-wrap items-center gap-2 shrink-0">
                                    <button onclick="printDailySummary()" class="px-4 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-all flex items-center gap-2 border border-slate-200 shadow-sm dark:text-slate-300 dark:border-slate-700 dark:bg-slate-800" title="Print Daily Summary">
                                        <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        Print
                                    </button>
                                    <button onclick="printDailySummary()" class="px-4 py-1.5 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 hover:bg-indigo-100 transition-all flex items-center gap-2 shadow-sm dark:border-indigo-800/50 dark:text-indigo-400 dark:bg-indigo-900/20" title="Save Daily Summary as PDF">
                                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        Save PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scrollable Report View -->
                    <div class="flex-1 overflow-visible md:overflow-y-auto p-6 sm:p-8 space-y-8 print:overflow-visible print:p-0" id="daily-sales-report-content">
                        <!-- Printable Header -->
                        <div class="hidden print:block text-center mb-8">
                            <h1 class="text-3xl font-black uppercase text-slate-900 tracking-tight dark:text-slate-100">{{ $restaurant->name ?? 'DrestroPOS' }}</h1>
                            <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mt-1 dark:text-slate-400">Sales Summary & Record Report</p>
                            <div class="text-xs font-bold text-slate-400 mt-2">
                                Period: {{ strtoupper($period) }} | Date: {{ $startFormatted }} to {{ $endFormatted }}
                            </div>
                        </div>

                        <!-- Grid Cards for Stats -->
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Card 1: Total Revenue -->
                            <div class="bg-emerald-600 border border-emerald-500 p-5 rounded-[1.5rem] shadow-sm flex flex-col justify-between">
                                <span class="text-[10px] font-black text-emerald-100 uppercase tracking-wider">Total Sales</span>
                                <div class="mt-2">
                                    <span class="text-2xl font-black text-white">Rs. {{ number_format($this->dailySummaryData['grand_total'], 0) }}</span>
                                    <p class="text-[9px] text-emerald-200 font-bold uppercase tracking-wider mt-1">{{ $this->dailySummaryData['count'] }} Orders Completed</p>
                                </div>
                            </div>
                            
                            <!-- Card 2: Cash Collection -->
                            <div class="bg-slate-700 border border-slate-600 p-5 rounded-[1.5rem] shadow-sm flex flex-col justify-between">
                                <span class="text-[10px] font-black text-slate-300 uppercase tracking-wider">Paid via Cash</span>
                                <div class="mt-2">
                                    <span class="text-2xl font-black text-white">Rs. {{ number_format($this->dailySummaryData['cash_total'], 0) }}</span>
                                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-1">Cash in Drawer</p>
                                </div>
                            </div>

                            <!-- Card 3: Card Collection -->
                            <div class="bg-indigo-600 border border-indigo-500 p-5 rounded-[1.5rem] shadow-sm flex flex-col justify-between">
                                <span class="text-[10px] font-black text-indigo-200 uppercase tracking-wider">Paid via Card</span>
                                <div class="mt-2">
                                    <span class="text-2xl font-black text-white">Rs. {{ number_format($this->dailySummaryData['card_total'], 0) }}</span>
                                    <p class="text-[9px] text-indigo-300 font-bold uppercase tracking-wider mt-1">Bank Settlements</p>
                                </div>
                            </div>

                            <!-- Card 4: Online Collection -->
                            <div class="bg-rose-600 border border-rose-500 p-5 rounded-[1.5rem] shadow-sm flex flex-col justify-between">
                                <span class="text-[10px] font-black text-rose-200 uppercase tracking-wider">Online / Wallet</span>
                                <div class="mt-2">
                                    <span class="text-2xl font-black text-white">Rs. {{ number_format($this->dailySummaryData['online_total'], 0) }}</span>
                                    <p class="text-[9px] text-rose-300 font-bold uppercase tracking-wider mt-1">Fonepay & Online Wallets</p>
                                </div>
                            </div>
                        </div>

                        <!-- Financial breakdown table -->
                        <div class="bg-white border border-slate-100 rounded-[1.5rem] p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                            <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider mb-4 dark:text-slate-200">Financial Breakdown</h4>
                            <div class="space-y-3.5">
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500 font-medium dark:text-slate-400">Subtotal Revenue</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200">Rs. {{ number_format($this->dailySummaryData['subtotal'], 0) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500 font-medium dark:text-slate-400">VAT & Taxes</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200">Rs. {{ number_format($this->dailySummaryData['tax'], 0) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500 font-medium dark:text-slate-400">Total Discounts Given</span>
                                    <span class="font-bold text-red-600 dark:text-red-400">- Rs. {{ number_format($this->dailySummaryData['discount'], 0) }}</span>
                                </div>
                                <div class="h-px bg-slate-100 dark:bg-slate-800"></div>
                                <div class="flex justify-between text-base font-black">
                                    <span class="text-slate-900 dark:text-slate-100">Net Grand Total</span>
                                    <span class="text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($this->dailySummaryData['grand_total'], 0) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Detailed order list of the day -->
                        <div class="bg-white border border-slate-100 rounded-[1.5rem] overflow-hidden shadow-sm dark:border-slate-800 dark:bg-slate-900">
                            <div class="px-6 py-5 border-b border-slate-50">
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider dark:text-slate-200">Completed Orders Archive</h4>
                            </div>
                            
                            @if($this->dailySummaryData['orders']->isEmpty())
                                <div class="p-8 text-center text-slate-400">
                                    <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                    <span class="text-xs font-bold">No orders completed in this period.</span>
                                </div>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-wider dark:border-slate-800 dark:bg-slate-900">
                                                <th class="px-6 py-4">Order #</th>
                                                <th class="px-6 py-4">Table/Room</th>
                                                <th class="px-6 py-4">Payment</th>
                                                <th class="px-6 py-4 text-right">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-50 text-xs">
                                            @foreach($this->dailySummaryData['orders'] as $order)
                                                <tr class="hover:bg-slate-50/50 transition-colors">
                                                    <td class="px-6 py-4 font-bold text-slate-900 uppercase dark:text-slate-100">#{{ substr($order->id, -6) }}</td>
                                                    <td class="px-6 py-4 text-slate-600 font-medium font-sans dark:text-slate-400">
                                                        {{ $order->table ? ($order->table->type === 'room' ? 'Room ' : 'Table ') . $order->table->name : 'Takeaway' }}
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <span class="px-2 py-1 bg-slate-100 text-slate-700 font-bold rounded-lg uppercase text-[9px] tracking-wider dark:text-slate-300 dark:bg-slate-800">
                                                            {{ $order->invoice ? $order->invoice->payment_method : 'Cash' }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 text-right font-black text-slate-900 dark:text-slate-100">
                                                        Rs. {{ number_format($order->invoice ? $order->invoice->grand_total : $order->total_amount, 0) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Mobile view overlay -->
    @if($selectedOrder)
    <div class="md:hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex flex-col justify-end">
        <div class="bg-white rounded-t-3xl overflow-hidden flex flex-col max-h-[90vh] dark:bg-slate-900">
            <div class="p-4 border-b border-slate-100 flex justify-between items-center sticky top-0 bg-white dark:border-slate-800 dark:bg-slate-900">
                <h3 class="font-bold text-lg">{{ $selectedOrder->table ? ($selectedOrder->table->type === 'room' ? 'Room ' : 'Table ') . $selectedOrder->table->name : 'Takeaway' }}</h3>
                <button wire:click="closeOrderModal" class="p-2 bg-slate-100 rounded-full text-slate-500 dark:text-slate-400 dark:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="p-6 pb-24 overflow-y-auto">
                <div class="text-3xl font-black text-center text-slate-900 mb-6 dark:text-slate-100">
                    Rs. {{ number_format($selectedOrder->total_amount, 0) }}
                </div>
                
                <div class="space-y-3 mb-6">
                    @foreach($selectedOrder->items as $item)
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600 font-medium dark:text-slate-400">{{ $item->quantity }}x {{ $item->menuItem->name }}</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">Rs. {{ number_format($item->subtotal, 0) }}</span>
                        </div>
                    @endforeach
                </div>
                
                @if($selectedOrder->invoice && $selectedOrder->invoice->payment_status === 'paid')
                    <button wire:click="markDelivered" class="w-full py-4 bg-slate-800 text-white font-bold rounded-xl shadow-lg">
                        Mark as Served
                    </button>
                @else
                    <button wire:click="openPaymentModal" class="w-full py-4 bg-emerald-600 text-white font-bold rounded-xl shadow-md">
                        Take Payment
                    </button>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Payment Modal -->
    @if($showPaymentModal)
    <div class="fixed inset-0 z-[100] bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl overflow-hidden w-full max-w-lg shadow-2xl flex flex-col max-h-[85vh] sm:max-h-[90vh] dark:bg-slate-900">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 dark:border-slate-800 dark:bg-slate-900/50">
                <h3 class="font-bold text-xl text-slate-800 dark:text-slate-200">Complete Payment</h3>
                <button wire:click="closePaymentModal" class="p-2 hover:bg-slate-200 rounded-full text-slate-500 transition-colors dark:text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="p-8 overflow-y-auto flex-1 custom-scrollbar">
                <div class="text-center mb-8">
                    <div class="text-sm text-slate-500 font-bold uppercase tracking-wider mb-2 dark:text-slate-400">Grand Total</div>
                    @php
                        $mTax = $selectedOrder->total_amount * ($restaurant->tax_percent ?? 0) / 100;
                        $mSc = $selectedOrder->total_amount * ($restaurant->service_charge_percent ?? 0) / 100;
                        $mTotal = ($selectedOrder->total_amount + $mTax + $mSc) - (float)$discount;
                    @endphp
                    <div class="text-5xl font-black text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($mTotal > 0 ? $mTotal : 0, 0) }}</div>
                </div>

                <div class="mb-8 p-6 bg-slate-50 rounded-2xl border border-slate-200 dark:bg-slate-900 dark:border-slate-700">
                    <div class="flex items-center justify-between mb-3">
                        <label class="text-sm font-black text-slate-700 uppercase tracking-widest dark:text-slate-300">Apply Discount</label>
                        <div class="flex items-center bg-slate-200 dark:bg-slate-800 p-1 rounded-xl">
                            <button type="button" wire:click="$set('discountType', 'percent')" class="px-3 py-1 text-xs font-black rounded-lg transition-all {{ $discountType === 'percent' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400' }}">
                                % Percent
                            </button>
                            <button type="button" wire:click="$set('discountType', 'amount')" class="px-3 py-1 text-xs font-black rounded-lg transition-all {{ $discountType === 'amount' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400' }}">
                                {{ $restaurant->currency }} Flat
                            </button>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-slate-400 font-black text-base">{{ $discountType === 'percent' ? '%' : $restaurant->currency }}</span>
                        </div>
                        <input type="number" step="any" min="0" max="{{ $discountType === 'percent' ? '100' : '999999' }}" wire:model.live="discountValue" wire:keyup="recalculateDiscount" wire:change="recalculateDiscount" class="w-full pl-12 pr-4 py-4 bg-white border-2 border-emerald-100 rounded-xl text-xl font-black text-emerald-600 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all dark:bg-slate-900 dark:border-emerald-800/50 dark:text-emerald-400" placeholder="{{ $discountType === 'percent' ? 'e.g. 10 (%)' : 'e.g. 150 (' . $restaurant->currency . ')' }}">
                    </div>

                    @if((float)$discount > 0)
                        <div class="mt-2.5 p-2.5 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/40 rounded-xl flex items-center justify-between text-xs font-bold text-emerald-700 dark:text-emerald-400">
                            <span>Applied Discount ({{ $discountType === 'percent' ? $discountValue . '%' : 'Flat' }}):</span>
                            <span>- {{ $restaurant->currency }} {{ number_format($discount, 2) }}</span>
                        </div>
                    @else
                        <p class="text-[10px] text-slate-400 mt-2 font-bold uppercase tracking-wider text-center">Discount will be subtracted from the grand total</p>
                    @endif
                </div>                <div class="space-y-4" x-data="{ method: @entangle('paymentMethod') }">
                    <h4 class="font-bold text-slate-700 mb-3 dark:text-slate-300">Select Payment Method</h4>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" x-model="method" value="cash" class="sr-only">
                            <div :class="method === 'cash' ? 'border-emerald-500 bg-emerald-50 shadow-sm' : 'border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/50'" class="p-4 rounded-xl border-2 transition-all flex items-center gap-3">
                                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm dark:bg-slate-900">💵</div>
                                <span :class="method === 'cash' ? 'text-slate-900 dark:text-slate-900' : 'text-slate-700 dark:text-slate-300'" class="font-bold">Cash</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" x-model="method" value="card" class="sr-only">
                            <div :class="method === 'card' ? 'border-emerald-500 bg-emerald-50 shadow-sm' : 'border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/50'" class="p-4 rounded-xl border-2 transition-all flex items-center gap-3">
                                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm dark:bg-slate-900">💳</div>
                                <span :class="method === 'card' ? 'text-slate-900 dark:text-slate-900' : 'text-slate-700 dark:text-slate-300'" class="font-bold">Card</span>
                            </div>
                        </label>
                        <label class="cursor-pointer col-span-2">
                            <input type="radio" x-model="method" value="online" class="sr-only">
                            <div :class="method === 'online' ? 'border-emerald-500 bg-emerald-50 shadow-sm' : 'border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/50'" class="p-4 rounded-xl border-2 transition-all flex items-center gap-3">
                                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm dark:bg-slate-900">📱</div>
                                <span :class="method === 'online' ? 'text-slate-900 dark:text-slate-900' : 'text-slate-700 dark:text-slate-300'" class="font-bold">Online Wallet / Bank Transfer</span>
                            </div>
                        </label>
                        <label class="cursor-pointer col-span-2">
                            <input type="radio" x-model="method" value="split" class="sr-only">
                            <div :class="method === 'split' ? 'border-emerald-500 bg-emerald-50 shadow-sm' : 'border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/50'" class="p-4 rounded-xl border-2 transition-all flex items-center gap-3">
                                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm dark:bg-slate-900">⚖️</div>
                                <span :class="method === 'split' ? 'text-slate-900 dark:text-slate-900' : 'text-slate-700 dark:text-slate-300'" class="font-bold">Split Payment (Cash & Online)</span>
                            </div>
                        </label>
                    </div>

                    @if($paymentMethod === 'online')
                        <div class="mt-4 p-4 bg-slate-50 rounded-xl border border-slate-200 dark:bg-slate-900 dark:border-slate-700 space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2 dark:text-slate-300">Select Provider</label>
                                <select wire:model="paymentProvider" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 dark:border-slate-650 dark:bg-slate-900 dark:text-slate-100">
                                    <option value="">-- Choose Provider --</option>
                                    <option value="esewa">eSewa</option>
                                    <option value="khalti">Khalti</option>
                                    <option value="fonepay">Fonepay</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                            @php
                                $selectedQr = null;
                                if ($paymentProvider === 'esewa') {
                                    $selectedQr = current_restaurant()->esewa_qr ?? current_restaurant()->payment_qr_code;
                                } elseif ($paymentProvider === 'khalti') {
                                    $selectedQr = current_restaurant()->khalti_qr ?? current_restaurant()->payment_qr_code;
                                } elseif ($paymentProvider === 'fonepay') {
                                    $selectedQr = current_restaurant()->fonepay_qr ?? current_restaurant()->payment_qr_code;
                                } elseif ($paymentProvider === 'bank_transfer') {
                                    $selectedQr = current_restaurant()->bank_transfer_qr ?? current_restaurant()->payment_qr_code;
                                }
                            @endphp
                            @if($paymentProvider && current_restaurant() && $selectedQr)
                            <div class="flex flex-col items-center justify-center p-4 border border-dashed border-slate-200 bg-white rounded-xl dark:bg-slate-800 dark:border-slate-700">
                                <span class="text-xs font-bold text-slate-400 mb-2 uppercase tracking-wider">Scan QR to Pay ({{ strtoupper(str_replace('_', ' ', $paymentProvider)) }})</span>
                                <img src="{{ asset('storage/' . $selectedQr) }}" class="max-w-[200px] h-auto object-contain rounded-lg border shadow-sm" alt="Payment QR Code">
                            </div>
                            @endif
                        </div>
                    @endif

                    @if($paymentMethod === 'split')
                        <div class="mt-4 p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-4 dark:bg-slate-900 dark:border-slate-700">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2 dark:text-slate-300">Cash Portion Paid (Rs.)</label>
                                <input type="number" wire:model.live.debounce.500ms="splitCashAmount" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 font-bold dark:border-slate-650 dark:bg-slate-900 dark:text-slate-100" placeholder="Enter Cash Paid">
                            </div>
                            <div class="flex justify-between items-center text-sm font-bold p-3 bg-emerald-50 border border-emerald-100 rounded-lg text-emerald-800 dark:text-emerald-300 dark:bg-emerald-900/20 dark:border-emerald-800/50">
                                <span>Remaining Online Portion:</span>
                                <span>Rs. {{ number_format(max(0, $mTotal - (float)$splitCashAmount), 0) }}</span>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2 dark:text-slate-300">Select Online Provider</label>
                                <select wire:model="paymentProvider" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 dark:border-slate-650 dark:bg-slate-900 dark:text-slate-100">
                                    <option value="">-- Choose Provider --</option>
                                    <option value="esewa">eSewa</option>
                                    <option value="khalti">Khalti</option>
                                    <option value="fonepay">Fonepay</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                            @php
                                $selectedQr = null;
                                if ($paymentProvider === 'esewa') {
                                    $selectedQr = current_restaurant()->esewa_qr ?? current_restaurant()->payment_qr_code;
                                } elseif ($paymentProvider === 'khalti') {
                                    $selectedQr = current_restaurant()->khalti_qr ?? current_restaurant()->payment_qr_code;
                                } elseif ($paymentProvider === 'fonepay') {
                                    $selectedQr = current_restaurant()->fonepay_qr ?? current_restaurant()->payment_qr_code;
                                } elseif ($paymentProvider === 'bank_transfer') {
                                    $selectedQr = current_restaurant()->bank_transfer_qr ?? current_restaurant()->payment_qr_code;
                                }
                            @endphp
                            @if($paymentProvider && current_restaurant() && $selectedQr)
                            <div class="flex flex-col items-center justify-center p-4 border border-dashed border-slate-200 bg-white rounded-xl dark:bg-slate-800 dark:border-slate-700">
                                <span class="text-xs font-bold text-slate-400 mb-2 uppercase tracking-wider">Scan QR to Pay ({{ strtoupper(str_replace('_', ' ', $paymentProvider)) }})</span>
                                <img src="{{ asset('storage/' . $selectedQr) }}" class="max-w-[200px] h-auto object-contain rounded-lg border shadow-sm" alt="Payment QR Code">
                            </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="p-6 border-t border-slate-100 bg-slate-50 flex gap-3 justify-end dark:border-slate-800 dark:bg-slate-900">
                <button wire:click="closePaymentModal" class="px-6 py-3 font-bold text-slate-600 hover:bg-slate-200 rounded-xl transition-all dark:text-slate-400">
                    Cancel
                </button>
                <button wire:click="processPayment" wire:loading.attr="disabled" class="px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md active:scale-95 transition-all flex items-center gap-2 disabled:opacity-75 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="processPayment">Confirm Payment</span>
                    <span wire:loading wire:target="processPayment" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Processing...
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif

    <script>
        function printInvoice() {
            var printContents = document.getElementById('invoice').innerHTML;
            
            // Create a hidden iframe
            var iframe = document.createElement('iframe');
            iframe.style.position = 'fixed';
            iframe.style.right = '0';
            iframe.style.bottom = '0';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = '0';
            document.body.appendChild(iframe);
            
            // Write contents into the iframe
            var win = iframe.contentWindow;
            win.document.open();
            win.document.write(`
                <html>
                <head>
                    <title>Print Receipt</title>
                    <style>
                        * { margin: 0; padding: 0; box-sizing: border-box; }
                        @page { size: 80mm auto; margin: 0; }
                        body { 
                            width: 72mm; 
                            margin: 0; 
                            padding: 3mm 4mm; 
                            background: white; 
                            font-family: 'Courier New', Courier, monospace; 
                            color: #000;
                        }
                        h1 { font-size: 16px; font-weight: 900; margin-bottom: 2px; text-align: center; }
                        p, span, div { font-size: 11px; line-height: 1.3; color: #000; }
                        .text-center { text-align: center; }
                        .mb-6 { margin-bottom: 24px; }
                        .pb-6 { padding-bottom: 24px; }
                        .border-b { border-bottom: 1px dashed #000; }
                        .flex { display: flex; }
                        .justify-between { justify-content: space-between; }
                        .font-bold { font-weight: bold; }
                        .font-black { font-weight: 900; }
                        .text-xs { font-size: 10px; }
                        .mt-1 { margin-top: 4px; }
                        table { width: 100%; margin-bottom: 16px; border-collapse: collapse; text-align: left; }
                        th { padding: 4px 0; font-size: 10px; text-transform: uppercase; border-bottom: 1px dashed #000; }
                        td { padding: 4px 0; font-size: 11px; }
                        .text-right { text-align: right; }
                        .border-t { border-top: 1px dashed #000; }
                        .pt-3 { padding-top: 12px; }
                        .text-lg { font-size: 14px; }
                        .text-2xl { font-size: 16px; }
                        button { display: none; }
                    </style>
                </head>
                <body>
                    ${printContents}
                </body>
                </html>
            `);
            win.document.close();
            
            // Wait a moment for rendering, then print
            setTimeout(function() {
                win.focus();
                win.print();
                
                // Cleanup
                setTimeout(function() {
                    document.body.removeChild(iframe);
                }, 500);
            }, 250);
        }

        function downloadA4PDF() {
            alert('To save this receipt as a PDF:\n\n1. The Print Dialog will now open.\n2. Change your Destination to "Save as PDF".\n3. Click Save!');
            window.print();
        }

        function printDailySummary() {
            var restaurantName = "{{ $restaurant->name ?? 'DrestroPOS' }}";
            var periodText = "{{ strtoupper($period) }}";
            var dateText = "{{ $startFormatted }} to {{ $endFormatted }}";
            
            var totalSales = "Rs. {{ number_format($this->dailySummaryData['grand_total'], 0) }}";
            var completedCount = "{{ $this->dailySummaryData['count'] }} Orders Completed";
            var cashTotal = "Rs. {{ number_format($this->dailySummaryData['cash_total'], 0) }}";
            var cardTotal = "Rs. {{ number_format($this->dailySummaryData['card_total'], 0) }}";
            var onlineTotal = "Rs. {{ number_format($this->dailySummaryData['online_total'], 0) }}";
            
            var subtotal = "Rs. {{ number_format($this->dailySummaryData['subtotal'], 0) }}";
            var tax = "Rs. {{ number_format($this->dailySummaryData['tax'], 0) }}";
            var discount = "Rs. {{ number_format($this->dailySummaryData['discount'], 0) }}";
            var grandTotal = "Rs. {{ number_format($this->dailySummaryData['grand_total'], 0) }}";
            
            // Extract order list HTML safely
            var ordersTableHTML = '';
            var tableElement = document.querySelector('#daily-sales-report-content table');
            if (tableElement) {
                ordersTableHTML = tableElement.outerHTML;
            } else {
                ordersTableHTML = '<div class="no-orders">No completed orders found in this period.</div>';
            }
            
            // Create a hidden iframe
            var iframe = document.createElement('iframe');
            iframe.style.position = 'fixed';
            iframe.style.right = '0';
            iframe.style.bottom = '0';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = '0';
            document.body.appendChild(iframe);
            
            var win = iframe.contentWindow;
            win.document.open();
            win.document.write(`
                <html>
                <head>
                    <title>Daily Sales Summary</title>
                    <style>
                        body {
                            background: white;
                            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                            color: #1e293b;
                            margin: 0;
                            padding: 1cm;
                            font-size: 10pt;
                            line-height: 1.3;
                        }
                        .header {
                            text-align: center;
                            margin-bottom: 20px;
                            border-bottom: 2px solid #e2e8f0;
                            padding-bottom: 10px;
                        }
                        .header h1 {
                            font-size: 20pt;
                            font-weight: 900;
                            margin: 0;
                            text-transform: uppercase;
                            color: #0f172a;
                            letter-spacing: -0.025em;
                        }
                        .header p {
                            font-size: 9pt;
                            font-weight: bold;
                            color: #64748b;
                            text-transform: uppercase;
                            margin: 2px 0 0 0;
                            letter-spacing: 0.05em;
                        }
                        .header .meta {
                            font-size: 8.5pt;
                            color: #64748b;
                            margin-top: 4px;
                            font-weight: bold;
                        }
                        
                        /* Compact Summary Table */
                        .stats-table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 20px;
                        }
                        .stats-table th {
                            background: #f8fafc;
                            border: 1px solid #e2e8f0;
                            padding: 6px 10px;
                            font-size: 8pt;
                            text-transform: uppercase;
                            color: #64748b;
                            font-weight: 900;
                            text-align: center;
                        }
                        .stats-table td {
                            border: 1px solid #e2e8f0;
                            padding: 8px 10px;
                            text-align: center;
                            font-size: 11pt;
                            font-weight: 900;
                            color: #0f172a;
                        }
                        .stats-table td .sub {
                            font-size: 7.5pt;
                            font-weight: bold;
                            color: #64748b;
                            text-transform: uppercase;
                            display: block;
                            margin-top: 2px;
                        }

                        .row-flex {
                            display: flex;
                            justify-content: space-between;
                            gap: 20px;
                            margin-bottom: 20px;
                        }
                        .row-flex > div {
                            flex: 1;
                        }
                        
                        /* Compact Breakdown Table */
                        .breakdown-card {
                            border: 1px solid #e2e8f0;
                            border-radius: 8px;
                            padding: 10px 15px;
                            background: #f8fafc;
                        }
                        .breakdown-card h3 {
                            font-size: 8pt;
                            font-weight: 900;
                            text-transform: uppercase;
                            color: #64748b;
                            margin-bottom: 8px;
                            border-bottom: 1px solid #e2e8f0;
                            padding-bottom: 4px;
                        }
                        .breakdown-row {
                            display: flex;
                            justify-content: space-between;
                            font-size: 8.5pt;
                            margin-bottom: 6px;
                            color: #475569;
                        }
                        .breakdown-row.total {
                            font-size: 10pt;
                            font-weight: 900;
                            color: #0f172a;
                            border-top: 1px dashed #cbd5e1;
                            padding-top: 6px;
                            margin-bottom: 0;
                        }
                        .breakdown-row .val {
                            font-weight: bold;
                            color: #1e293b;
                        }
                        .breakdown-row.total .val {
                            color: #059669;
                            font-size: 11.5pt;
                        }
                        
                        /* Completed Orders Archive */
                        .archive-section h3 {
                            font-size: 8.5pt;
                            font-weight: 900;
                            text-transform: uppercase;
                            color: #475569;
                            margin-bottom: 8px;
                            border-bottom: 2px solid #e2e8f0;
                            padding-bottom: 3px;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                        }
                        table th {
                            background: #f8fafc;
                            border-bottom: 2px solid #cbd5e1;
                            padding: 6px 10px;
                            font-size: 8pt;
                            font-weight: 900;
                            color: #64748b;
                            text-transform: uppercase;
                            text-align: left;
                        }
                        table td {
                            border-bottom: 1px solid #f1f5f9;
                            padding: 6px 10px;
                            font-size: 8.5pt;
                            color: #334155;
                        }
                        table td.amount {
                            text-align: right;
                            font-weight: bold;
                            color: #0f172a;
                        }
                        table th.amount {
                            text-align: right;
                        }
                        
                        .no-orders {
                            text-align: center;
                            padding: 12px;
                            border: 1px dashed #cbd5e1;
                            border-radius: 6px;
                            color: #64748b;
                            font-size: 8.5pt;
                            font-weight: bold;
                        }
                        
                        /* Hide all SVGs/icons in print */
                        svg, img {
                            display: none !important;
                        }
                        
                        @media print {
                            body { padding: 0; }
                        }
                    </style>
                </head>
                <body>
                    <!-- Header -->
                    <div class="header">
                        <h1>${restaurantName}</h1>
                        <p>Sales Summary & Record Report</p>
                        <div class="meta">
                            PERIOD: ${periodText} | DATE: ${dateText}
                        </div>
                    </div>

                    <!-- Summary Grid Table -->
                    <table class="stats-table">
                        <thead>
                            <tr>
                                <th>Total Sales</th>
                                <th>Cash Payments</th>
                                <th>Card Payments</th>
                                <th>Online / Wallet</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    ${totalSales}
                                    <span class="sub">${completedCount}</span>
                                </td>
                                <td>
                                    ${cashTotal}
                                    <span class="sub">Cash Drawer</span>
                                </td>
                                <td>
                                    ${cardTotal}
                                    <span class="sub">Settlements</span>
                                </td>
                                <td>
                                    ${onlineTotal}
                                    <span class="sub">Fonepay & Wallet</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="row-flex">
                        <!-- Financial Breakdown Card -->
                        <div class="breakdown-card">
                            <h3>Financial Breakdown</h3>
                            <div class="breakdown-row">
                                <span>Subtotal Revenue</span>
                                <span class="val">${subtotal}</span>
                            </div>
                            <div class="breakdown-row">
                                <span>VAT & Taxes</span>
                                <span class="val">${tax}</span>
                            </div>
                            <div class="breakdown-row" style="color: #dc2626;">
                                <span>Total Discounts Given</span>
                                <span class="val" style="color: #dc2626;">${discount}</span>
                            </div>
                            <div class="breakdown-row total">
                                <span>Net Grand Total</span>
                                <span class="val">${grandTotal}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Archive Section -->
                    <div class="archive-section">
                        <h3>Completed Orders Archive</h3>
                        ${ordersTableHTML}
                    </div>
                </body>
                </html>
            `);
            win.document.close();
            
            // Wait a moment for rendering, then print
            setTimeout(function() {
                win.focus();
                win.print();
                
                // Cleanup
                setTimeout(function() {
                    document.body.removeChild(iframe);
                }, 500);
            }, 250);
        }

        document.addEventListener('livewire:initialized', function() {
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

