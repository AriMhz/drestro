<div class="space-y-6" id="pdf-content">
    <style>
        .print-header-container { display: none; }
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
            #pdf-content .grid { display: block !important; margin: 0 !important; padding: 0 !important; }
            #pdf-content .grid > div { display: flex !important; flex-direction: row !important; justify-content: space-between !important; align-items: center !important; border-bottom: 1px dotted #999 !important; padding: 8px 0 !important; margin: 0 !important; page-break-inside: avoid !important; }
            #pdf-content .text-2xl { font-size: 14pt !important; font-weight: bold !important; margin: 0 !important; }
            #pdf-content .text-sm.font-medium { font-size: 11pt !important; margin: 0 !important; }
            #pdf-content .print-hidden, #pdf-content svg, #pdf-content .absolute { display: none !important; }
            #pdf-content .print-header-container { display: block !important; }
            #pdf-content .overflow-x-auto, #pdf-content .overflow-x-hidden { overflow: visible !important; width: 100% !important; }
            #pdf-content table { width: 100% !important; table-layout: auto !important; border-collapse: collapse !important; margin: 15px 0 !important; page-break-inside: auto !important; }
            #pdf-content tr { page-break-inside: avoid !important; page-break-after: auto !important; }
            #pdf-content th, #pdf-content td { border: 1px solid #000 !important; padding: 6px 8px !important; text-align: left !important; font-size: 9pt !important; word-wrap: break-word !important; word-break: break-word !important; }
            #pdf-content th { font-weight: bold !important; text-transform: uppercase !important; }
        }
    </style>
    <!-- Print-Only Header Block -->
    @php
        $restaurantForPrint = current_restaurant();
        $restaurantNameForPrint = $restaurantForPrint->name ?? 'DrestroPOS';
        $calTypeForPrint = strtoupper($restaurantForPrint->date_calendar_type ?? 'AD');
        $nowForPrint = now();
        $dateStrForPrint = '';
        if ($calTypeForPrint === 'BS') {
            $ncForPrint = new \App\Helpers\NepaliCalendar();
            $bsForPrint = $ncForPrint->eng_to_nep($nowForPrint->year, $nowForPrint->month, $nowForPrint->day);
            if (isset($bsForPrint['date'])) {
                $weekdayForPrint = strtoupper(substr($bsForPrint['day'], 0, 3));
                $nmonthForPrint = strtoupper(substr($bsForPrint['nmonth'], 0, 3));
                $dayNumForPrint = str_pad($bsForPrint['date'], 2, '0', STR_PAD_LEFT);
                $dateStrForPrint = "{$weekdayForPrint}, {$nmonthForPrint} {$dayNumForPrint}";
            } else {
                $dateStrForPrint = $nowForPrint->format('D, M d');
            }
        } else {
            $dateStrForPrint = $nowForPrint->format('D, M d');
        }
        $timeStrForPrint = $nowForPrint->format('h:i A');
    @endphp

    <div class="print-header-container">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 20px;">
            <div>
                <h2 style="font-size: 20pt; font-weight: bold; margin: 0; color: #1e293b;">{{ $restaurantNameForPrint }}</h2>
                <p style="font-size: 11pt; color: #64748b; margin: 2px 0 0 0; font-weight: 600;">INVENTORY REPORT</p>
            </div>
            <div style="text-align: right; font-size: 10pt; color: #475569; font-weight: bold;">
                <div>DATE: {{ $dateStrForPrint }}</div>
                <div style="margin-top: 2px;">TIME: {{ $timeStrForPrint }}</div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 print-hidden">
        <div class="hidden lg:block">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Inventory Management</h1>
            <p class="text-sm text-slate-500 mt-1 dark:text-slate-400">Track raw materials, stock levels, and purchase costs.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto print-hidden">
            <button onclick="window.print()" class="flex-1 sm:flex-none px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl border border-slate-200 hover:bg-slate-200 transition-all flex items-center justify-center gap-2 whitespace-nowrap dark:text-slate-300 dark:border-slate-700 dark:bg-slate-800" title="Print Inventory">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print
            </button>
            <button onclick="downloadPDF('Inventory_Report.pdf')" class="flex-1 sm:flex-none px-4 py-2 bg-indigo-50 text-indigo-700 font-bold rounded-xl border border-indigo-200 hover:bg-indigo-100 transition-all flex items-center justify-center gap-2 whitespace-nowrap dark:text-indigo-400 dark:bg-indigo-900/20" title="Save Inventory as PDF">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Save PDF
            </button>
            <button wire:click="openItemModal" class="w-full sm:w-auto px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/20 hover:bg-emerald-700 active:scale-95 transition-all flex items-center justify-center gap-2 whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Item
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4 dark:border-slate-800 dark:bg-slate-900">
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 dark:text-blue-400 dark:bg-blue-900/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
            <div>
                <p class="text-sm text-slate-500 font-medium dark:text-slate-400">Total Items</p>
                <p class="text-2xl font-black text-slate-800 dark:text-slate-200">{{ $this->items->count() }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl border {{ $this->lowStockCount > 0 ? 'border-red-200 bg-red-50/30' : 'border-slate-100' }} shadow-sm flex items-center gap-4 dark:border-slate-800 dark:bg-slate-900 dark:border-red-800">
            <div class="w-12 h-12 rounded-xl {{ $this->lowStockCount > 0 ? 'bg-red-100' : 'bg-amber-50' }} flex items-center justify-center {{ $this->lowStockCount > 0 ? 'text-red-600' : 'text-amber-600' }} dark:text-amber-400 dark:text-red-400 dark:bg-amber-900/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div>
                <p class="text-sm text-slate-500 font-medium dark:text-slate-400">Low Stock Alerts</p>
                <p class="text-2xl font-black {{ $this->lowStockCount > 0 ? 'text-red-600' : 'text-slate-800' }} dark:text-red-400 dark:text-slate-200">{{ $this->lowStockCount }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4 dark:border-slate-800 dark:bg-slate-900">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-sm text-slate-500 font-medium dark:text-slate-400">Total Stock Value</p>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($this->totalValue, 0) }}</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-3 sm:items-center print-hidden">
        <div class="relative flex-1 max-w-md">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input wire:model.live.debounce.300ms="searchQuery" type="text" placeholder="Search inventory..." class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none dark:bg-slate-900 dark:border-slate-700">
        </div>
        <select wire:model.live="filterCategory" class="px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-emerald-500 outline-none dark:bg-slate-900 dark:border-slate-700">
            <option value="">All Categories</option>
            <option value="meat">🥩 Meat</option>
            <option value="vegetables">🥬 Vegetables</option>
            <option value="dairy">🧀 Dairy</option>
            <option value="spices">🌶️ Spices</option>
            <option value="grains">🌾 Grains & Flour</option>
            <option value="beverages">🍺 Beverages</option>
            <option value="packaging">📦 Packaging</option>
            <option value="general">📋 General</option>
        </select>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider dark:text-slate-400">Item</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider dark:text-slate-400">Category</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center dark:text-slate-400">Stock</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right dark:text-slate-400">Cost/Unit</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right dark:text-slate-400">Value</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center dark:text-slate-400">Added Date</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center dark:text-slate-400">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right print-hidden dark:text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($this->items as $item)
                    <tr class="hover:bg-slate-50/50 transition-colors {{ $item->isLowStock() ? 'bg-red-50/30' : '' }}">
                        <td class="px-6 py-4">
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $item->name }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $catIcons = ['meat'=>'🥩','vegetables'=>'🥬','dairy'=>'🧀','spices'=>'🌶️','grains'=>'🌾','beverages'=>'🍺','packaging'=>'📦','general'=>'📋'];
                            @endphp
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-xs font-semibold text-slate-600 dark:text-slate-400 dark:bg-slate-800">
                                {{ $catIcons[$item->category] ?? '📋' }} {{ ucfirst($item->category) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-bold {{ $item->isLowStock() ? 'text-red-600' : 'text-slate-800' }} dark:text-red-400 dark:text-slate-200">{{ number_format($item->quantity, 1) }}</span>
                            <span class="text-slate-400 text-xs ml-1">{{ $item->unit }}</span>
                        </td>
                        <td class="px-6 py-4 text-right text-slate-600 font-medium dark:text-slate-400">Rs. {{ number_format($item->cost_per_unit, 0) }}</td>
                        <td class="px-6 py-4 text-right font-bold text-slate-700 dark:text-slate-300">Rs. {{ number_format($item->quantity * $item->cost_per_unit, 0) }}</td>
                        <td class="px-6 py-4 text-center text-xs font-medium text-slate-500 dark:text-slate-400">{{ \App\Helpers\DateHelper::format($item->created_at) }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($item->isLowStock())
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 animate-pulse dark:text-red-400">
                                    ⚠ Low Stock
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:text-emerald-400">
                                    ✓ In Stock
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right print-hidden">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="openHistoryModal({{ $item->id }})" class="px-2.5 py-1.5 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold hover:bg-slate-200 transition-colors dark:text-slate-400 dark:bg-slate-800" title="View History">
                                    History
                                </button>
                                <button wire:click="openStockModal({{ $item->id }})" class="px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-bold hover:bg-blue-100 transition-colors dark:text-blue-400 dark:bg-blue-900/20" title="Adjust Stock">
                                    ± Stock
                                </button>
                                <button wire:click="openItemModal({{ $item->id }})" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <button wire:click="deleteItem({{ $item->id }})" wire:confirm="Delete {{ $item->name }}?" class="p-1.5 text-slate-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3 dark:bg-slate-900">
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            </div>
                            <p class="font-medium text-slate-500 dark:text-slate-400">No inventory items yet</p>
                            <p class="text-xs text-slate-400 mt-1">Click "Add Item" to start tracking your stock.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Item Modal -->
    @if($showItemModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full mx-4 overflow-hidden dark:bg-slate-900">
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center dark:border-slate-800">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">{{ $editingItemId ? 'Edit' : 'Add New' }} Inventory Item</h3>
                <button wire:click="closeItemModal" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1 dark:text-slate-300">Item Name</label>
                    <input wire:model.blur="itemName" type="text" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none dark:border-slate-700" placeholder="e.g. Chicken Breast">
                    @error('itemName') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1 dark:text-slate-300">Unit</label>
                        <select wire:model.blur="itemUnit" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none dark:border-slate-700">
                            <option value="kg">Kilogram (kg)</option>
                            <option value="g">Gram (g)</option>
                            <option value="litre">Litre (L)</option>
                            <option value="ml">Millilitre (ml)</option>
                            <option value="pcs">Pieces (pcs)</option>
                            <option value="packet">Packet</option>
                            <option value="bottle">Bottle</option>
                            <option value="box">Box</option>
                            <option value="dozen">Dozen</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1 dark:text-slate-300">Category</label>
                        <select wire:model.blur="itemCategory" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none dark:border-slate-700">
                            <option value="meat">🥩 Meat</option>
                            <option value="vegetables">🥬 Vegetables</option>
                            <option value="dairy">🧀 Dairy</option>
                            <option value="spices">🌶️ Spices</option>
                            <option value="grains">🌾 Grains & Flour</option>
                            <option value="beverages">🍺 Beverages</option>
                            <option value="packaging">📦 Packaging</option>
                            <option value="general">📋 General</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1 dark:text-slate-300">Cost per Unit (Rs.)</label>
                        <input wire:model.blur="itemCostPerUnit" type="number" step="0.01" min="0" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none dark:border-slate-700" placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1 dark:text-slate-300">Low Stock Alert</label>
                        <input wire:model.blur="itemLowStockThreshold" type="number" step="0.1" min="0" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none dark:border-slate-700" placeholder="5">
                    </div>
                </div>
                @if(!$editingItemId)
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1 dark:text-slate-300">Opening Stock</label>
                        <input wire:model.blur="itemQuantity" type="number" step="0.1" min="0" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none dark:border-slate-700" placeholder="0">
                        @error('itemQuantity') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1 dark:text-slate-300">Stock Date</label>
                        <div wire:ignore wire:key="calendar-picker-item-date" class="relative animate-fade-in" 
                             x-ref="itemDateWrap"
                             x-data="{
                                pickerOpen: false,
                                bsMap: @js((new \App\Helpers\NepaliCalendar())->_bs ?? []),
                                nepMonthNames: ['Baisakh', 'Jestha', 'Ashadh', 'Shrawan', 'Bhadra', 'Ashwin', 'Kartik', 'Mangsir', 'Poush', 'Magh', 'Falgun', 'Chaitra'],
                                engMonthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                                selectedDate: '{{ $itemDate ?? now()->format('Y-m-d') }}',
                                viewYear: 2083,
                                viewMonth: 2,
                                calMode: '{{ strtoupper(current_restaurant()->date_calendar_type ?? 'AD') }}' === 'BS' ? 'bs' : 'ad',
                                
                                init() {
                                    this.syncFromSelected();
                                    this.$watch('selectedDate', (val) => {
                                        $wire.set('itemDate', val);
                                    });
                                    this.$watch('$wire.itemDate', (val) => {
                                        if (val && val !== this.selectedDate) {
                                            this.selectedDate = val;
                                            this.syncFromSelected();
                                        }
                                    });
                                    this.$watch('pickerOpen', (val) => {
                                        const viewport = document.getElementById('main-content-viewport');
                                        if (val) {
                                            document.body.style.overflow = 'hidden';
                                            if (viewport) viewport.style.overflowY = 'hidden';
                                        } else {
                                            document.body.style.overflow = '';
                                            if (viewport) viewport.style.overflowY = '';
                                        }
                                    });
                                    this.$cleanup(() => {
                                        const viewport = document.getElementById('main-content-viewport');
                                        document.body.style.overflow = '';
                                        if (viewport) viewport.style.overflowY = '';
                                    });
                                },
                                syncFromSelected() {
                                    if (!this.selectedDate) return;
                                    let parts = this.selectedDate.split('-');
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
                                                isSelected: this.selectedDate === formattedEng
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
                                                isSelected: this.selectedDate === formattedEng
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
                                    this.selectedDate = item.gregorian;
                                    this.pickerOpen = false;
                                },
                                getFormattedSelectedDate() {
                                    if (!this.selectedDate) return 'Select Date';
                                    if (this.calMode === 'bs') {
                                        let parts = this.selectedDate.split('-');
                                        let nep = this.engToNep(parseInt(parts[0]), parseInt(parts[1]), parseInt(parts[2]));
                                        if (nep) return this.nepMonthNames[nep.month - 1] + ' ' + nep.day + ', ' + nep.year;
                                        return this.selectedDate;
                                    } else {
                                        return new Date(this.selectedDate.replace(/-/g, '/')).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'});
                                    }
                                }
                             }" 
                             @click.outside="pickerOpen = false">
                            <div @click="pickerOpen = !pickerOpen" class="w-full flex items-center justify-between px-4 py-2.5 bg-white border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-all select-none dark:bg-slate-900 dark:border-slate-700">
                                <span class="text-sm font-bold text-slate-700 dark:text-slate-300" x-text="getFormattedSelectedDate()"></span>
                                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            
                            <div x-show="pickerOpen" x-cloak 
                                 style="position: fixed; z-index: 9999; width: 320px; max-height: calc(100vh - 20px); overflow-y: auto;"
                                 :style="pickerOpen ? {
                                     top: Math.max(10, Math.min(window.innerHeight - 440, (window.innerHeight - $refs.itemDateWrap.getBoundingClientRect().bottom < 440 && $refs.itemDateWrap.getBoundingClientRect().top > (window.innerHeight - $refs.itemDateWrap.getBoundingClientRect().bottom)) ? ($refs.itemDateWrap.getBoundingClientRect().top - 435) : ($refs.itemDateWrap.getBoundingClientRect().bottom + 8))) + 'px',
                                     bottom: 'auto',
                                     left: Math.max(8, Math.min(window.innerWidth - 328, $refs.itemDateWrap.getBoundingClientRect().left)) + 'px'
                                 } : {}"
                                 class="bg-white border border-slate-200 rounded-2xl shadow-2xl p-4 space-y-3 dark:bg-slate-900 dark:border-slate-700">
                                
                                <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-slate-800">
                                    <span class="text-xs font-black text-slate-800 uppercase tracking-wider dark:text-slate-200" x-text="calMode === 'bs' ? 'Nepali Calendar (BS)' : 'Gregorian Calendar (AD)'"></span>
                                    <div class="flex items-center bg-slate-50 border border-slate-200 px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest select-none shrink-0 text-slate-500 font-bold dark:bg-slate-900 dark:text-slate-400 dark:border-slate-700">
                                        Calendar: {{ strtoupper(current_restaurant()->date_calendar_type ?? 'AD') }}
                                    </div>
                                </div>
                                
                                <div class="space-y-2">
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
                                    
                                    <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; text-align: center;" class="text-[10px] font-black text-slate-400 uppercase">
                                        <div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div>
                                    </div>
                                    
                                    <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px;">
                                        <template x-for="item in getGrid()">
                                            <div style="width: 36px; height: 36px;">
                                                <template x-if="item.blank">
                                                    <div style="width: 36px; height: 36px;"></div>
                                                </template>
                                                <template x-if="!item.blank">
                                                    <button type="button" 
                                                        @click.stop="selectDay(item)"
                                                        :class="item.isSelected ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : (item.isToday ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'hover:bg-slate-100 text-slate-700')"
                                                        style="width:36px;height:36px;border-radius:8px;position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;transition:all 0.15s;">
                                                        <span style="font-size:13px;font-weight:900;line-height:1;" x-text="item.day"></span>
                                                        <template x-if="calMode === 'bs' && item.engDay">
                                                            <span style="font-size:7px;line-height:1;font-weight:700;position:absolute;bottom:2px;right:3px;opacity:0.6;" x-text="item.engDay"></span>
                                                        </template>
                                                    </button>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @error('itemDate') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
                @endif
            </div>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 dark:border-slate-800 dark:bg-slate-900">
                <button wire:click="closeItemModal" class="px-5 py-2.5 text-slate-600 font-semibold rounded-xl hover:bg-slate-200 transition-colors dark:text-slate-400">Cancel</button>
                <button wire:click="saveItem" class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/20 hover:bg-emerald-700 active:scale-95 transition-all">
                    {{ $editingItemId ? 'Update' : 'Add Item' }}
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Stock Adjustment Modal -->
    @if($showStockModal)
    @php $stockItem = \App\Models\InventoryItem::find($stockItemId); @endphp
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 overflow-hidden dark:bg-slate-900">
            <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">Adjust Stock</h3>
                <p class="text-sm text-slate-500 mt-1 dark:text-slate-400">{{ $stockItem->name ?? '' }} — Current: <strong>{{ number_format($stockItem->quantity ?? 0, 1) }} {{ $stockItem->unit ?? '' }}</strong></p>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2 dark:text-slate-300">Type</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button wire:click="$set('stockType', 'stock_in')" class="py-2.5 rounded-xl text-sm font-bold text-center border-2 transition-all {{ $stockType == 'stock_in' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-slate-200 text-slate-500' }} dark:bg-emerald-900/20 dark:text-slate-400 dark:border-slate-700 dark:text-emerald-400">
                            + Stock In
                        </button>
                        <button wire:click="$set('stockType', 'stock_out')" class="py-2.5 rounded-xl text-sm font-bold text-center border-2 transition-all {{ $stockType == 'stock_out' ? 'border-red-500 bg-red-50 text-red-700' : 'border-slate-200 text-slate-500' }} dark:text-red-400 dark:text-slate-400 dark:border-slate-700 dark:bg-red-900/20">
                            − Stock Out
                        </button>
                        <button wire:click="$set('stockType', 'adjustment')" class="py-2.5 rounded-xl text-sm font-bold text-center border-2 transition-all {{ $stockType == 'adjustment' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-500' }} dark:text-blue-400 dark:text-slate-400 dark:border-slate-700 dark:bg-blue-900/20">
                            ✎ Set Exact
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1 dark:text-slate-300">Quantity ({{ $stockItem->unit ?? '' }})</label>
                        <input wire:model.blur="stockQuantity" type="number" step="0.01" min="0.01" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none text-lg font-bold dark:border-slate-700" placeholder="0" autofocus>
                        @error('stockQuantity') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1 dark:text-slate-300">Date</label>
                        <div wire:ignore wire:key="calendar-picker-stock-date" class="relative animate-fade-in" 
                             x-ref="stockDateWrap"
                             x-data="{
                                pickerOpen: false,
                                bsMap: @js((new \App\Helpers\NepaliCalendar())->_bs ?? []),
                                nepMonthNames: ['Baisakh', 'Jestha', 'Ashadh', 'Shrawan', 'Bhadra', 'Ashwin', 'Kartik', 'Mangsir', 'Poush', 'Magh', 'Falgun', 'Chaitra'],
                                engMonthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                                selectedDate: '{{ $stockDate ?? now()->format('Y-m-d') }}',
                                viewYear: 2083,
                                viewMonth: 2,
                                calMode: '{{ strtoupper(current_restaurant()->date_calendar_type ?? 'AD') }}' === 'BS' ? 'bs' : 'ad',
                                
                                init() {
                                    this.syncFromSelected();
                                    this.$watch('selectedDate', (val) => {
                                        $wire.set('stockDate', val);
                                    });
                                    this.$watch('$wire.stockDate', (val) => {
                                        if (val && val !== this.selectedDate) {
                                            this.selectedDate = val;
                                            this.syncFromSelected();
                                        }
                                    });
                                    this.$watch('pickerOpen', (val) => {
                                        const viewport = document.getElementById('main-content-viewport');
                                        if (val) {
                                            document.body.style.overflow = 'hidden';
                                            if (viewport) viewport.style.overflowY = 'hidden';
                                        } else {
                                            document.body.style.overflow = '';
                                            if (viewport) viewport.style.overflowY = '';
                                        }
                                    });
                                    this.$cleanup(() => {
                                        const viewport = document.getElementById('main-content-viewport');
                                        document.body.style.overflow = '';
                                        if (viewport) viewport.style.overflowY = '';
                                    });
                                },
                                syncFromSelected() {
                                    if (!this.selectedDate) return;
                                    let parts = this.selectedDate.split('-');
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
                                                isSelected: this.selectedDate === formattedEng
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
                                                isSelected: this.selectedDate === formattedEng
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
                                    this.selectedDate = item.gregorian;
                                    this.pickerOpen = false;
                                },
                                getFormattedSelectedDate() {
                                    if (!this.selectedDate) return 'Select Date';
                                    if (this.calMode === 'bs') {
                                        let parts = this.selectedDate.split('-');
                                        let nep = this.engToNep(parseInt(parts[0]), parseInt(parts[1]), parseInt(parts[2]));
                                        if (nep) return this.nepMonthNames[nep.month - 1] + ' ' + nep.day + ', ' + nep.year;
                                        return this.selectedDate;
                                    } else {
                                        return new Date(this.selectedDate.replace(/-/g, '/')).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'});
                                    }
                                }
                             }" 
                             @click.outside="pickerOpen = false">
                            <div @click="pickerOpen = !pickerOpen" class="w-full flex items-center justify-between px-4 py-2.5 bg-white border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-all select-none dark:bg-slate-900 dark:border-slate-700">
                                <span class="text-sm font-bold text-slate-700 dark:text-slate-300" x-text="getFormattedSelectedDate()"></span>
                                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            
                            <div x-show="pickerOpen" x-cloak 
                                 style="position: fixed; z-index: 9999; width: 320px; max-height: calc(100vh - 20px); overflow-y: auto;"
                                 :style="pickerOpen ? {
                                     top: Math.max(10, Math.min(window.innerHeight - 440, (window.innerHeight - $refs.stockDateWrap.getBoundingClientRect().bottom < 440 && $refs.stockDateWrap.getBoundingClientRect().top > (window.innerHeight - $refs.stockDateWrap.getBoundingClientRect().bottom)) ? ($refs.stockDateWrap.getBoundingClientRect().top - 435) : ($refs.stockDateWrap.getBoundingClientRect().bottom + 8))) + 'px',
                                     bottom: 'auto',
                                     left: Math.max(8, Math.min(window.innerWidth - 328, $refs.stockDateWrap.getBoundingClientRect().left)) + 'px'
                                 } : {}"
                                 class="bg-white border border-slate-200 rounded-2xl shadow-2xl p-4 space-y-3 dark:bg-slate-900 dark:border-slate-700">
                                
                                <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-slate-800">
                                    <span class="text-xs font-black text-slate-800 uppercase tracking-wider dark:text-slate-200" x-text="calMode === 'bs' ? 'Nepali Calendar (BS)' : 'Gregorian Calendar (AD)'"></span>
                                    <div class="flex items-center bg-slate-50 border border-slate-200 px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest select-none shrink-0 text-slate-500 font-bold dark:bg-slate-900 dark:text-slate-400 dark:border-slate-700">
                                        Calendar: {{ strtoupper(current_restaurant()->date_calendar_type ?? 'AD') }}
                                    </div>
                                </div>
                                
                                <div class="space-y-2">
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
                                    
                                    <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; text-align: center;" class="text-[10px] font-black text-slate-400 uppercase">
                                        <div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div>
                                    </div>
                                    
                                    <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px;">
                                        <template x-for="item in getGrid()">
                                            <div style="width: 36px; height: 36px;">
                                                <template x-if="item.blank">
                                                    <div style="width: 36px; height: 36px;"></div>
                                                </template>
                                                <template x-if="!item.blank">
                                                    <button type="button" 
                                                        @click.stop="selectDay(item)"
                                                        :class="item.isSelected ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : (item.isToday ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'hover:bg-slate-100 text-slate-700')"
                                                        style="width:36px;height:36px;border-radius:8px;position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;transition:all 0.15s;">
                                                        <span style="font-size:13px;font-weight:900;line-height:1;" x-text="item.day"></span>
                                                        <template x-if="calMode === 'bs' && item.engDay">
                                                            <span style="font-size:7px;line-height:1;font-weight:700;position:absolute;bottom:2px;right:3px;opacity:0.6;" x-text="item.engDay"></span>
                                                        </template>
                                                    </button>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @error('stockDate') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1 dark:text-slate-300">Note (optional)</label>
                    <input wire:model.blur="stockNote" type="text" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none dark:border-slate-700" placeholder="e.g. Purchased from market or Sent to Kitchen">
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 dark:border-slate-800 dark:bg-slate-900">
                <button wire:click="closeStockModal" class="px-5 py-2.5 text-slate-600 font-semibold rounded-xl hover:bg-slate-200 transition-colors dark:text-slate-400">Cancel</button>
                <button wire:click="adjustStock" class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/20 hover:bg-emerald-700 active:scale-95 transition-all">
                    Update Stock
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- History Modal -->
    @if($showHistoryModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full mx-4 overflow-hidden flex flex-col max-h-[85vh] dark:bg-slate-900">
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center dark:border-slate-800">
                <div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">Stock History</h3>
                    <p class="text-sm text-slate-500 mt-1 dark:text-slate-400">{{ $historyItem->name ?? '' }}</p>
                </div>
                <button wire:click="closeHistoryModal" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-0 overflow-y-auto bg-slate-50 flex-1 dark:bg-slate-900">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 bg-slate-100 border-b border-slate-200 dark:border-slate-700 dark:bg-slate-800">
                        <tr>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider dark:text-slate-400">Date</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider dark:text-slate-400">Type</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right dark:text-slate-400">Qty</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right dark:text-slate-400">Balance</th>
                            <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider dark:text-slate-400">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm bg-white dark:bg-slate-900">
                        @forelse($historyLogs as $log)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3 text-slate-600 whitespace-nowrap dark:text-slate-400">{{ \App\Helpers\DateHelper::format($log->created_at) }} • {{ $log->created_at->format('h:i A') }}</td>
                            <td class="px-6 py-3">
                                @if($log->type === 'stock_in')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 uppercase tracking-wide dark:text-emerald-400">Stock In</span>
                                @elseif($log->type === 'stock_out')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 uppercase tracking-wide dark:text-red-400">Stock Out</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 uppercase tracking-wide dark:text-blue-400">Adjustment</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right font-bold {{ $log->type === 'stock_in' ? 'text-emerald-600' : ($log->type === 'stock_out' ? 'text-red-600' : 'text-blue-600') }} dark:text-blue-400 dark:text-red-400 dark:text-emerald-400">
                                {{ $log->type === 'stock_out' ? '-' : ($log->type === 'stock_in' ? '+' : '') }}{{ number_format($log->quantity, 1) }}
                            </td>
                            <td class="px-6 py-3 text-right font-medium text-slate-800 dark:text-slate-200">{{ number_format($log->balance_after, 1) }}</td>
                            <td class="px-6 py-3 text-slate-500 text-xs dark:text-slate-400">{{ $log->note ?: '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400 italic text-sm">No history records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 bg-white flex justify-end dark:border-slate-800 dark:bg-slate-900">
                <button wire:click="closeHistoryModal" class="px-5 py-2.5 bg-slate-800 text-white font-bold rounded-xl shadow-md hover:bg-slate-900 transition-all">
                    Close
                </button>
            </div>
        </div>
    </div>
    @endif
    <!-- Daily Activity Logs Section -->
    <div class="mt-12 space-y-4 print-hidden">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800 dark:text-slate-200">Daily Stock Movement</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Track exactly what happened during a specific period.</p>
            </div>
            
            <div class="flex flex-wrap items-stretch gap-4 bg-white rounded-2xl border border-slate-200 p-4 shadow-sm w-full lg:w-auto dark:bg-slate-900 dark:border-slate-700">
                <!-- Section 2: Custom Date Range Picker -->
                <div wire:ignore wire:key="calendar-picker-inventory" class="flex flex-col gap-1.5 flex-1 min-w-[280px]"
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
                            this.$watch('pickerOpen', (val) => {
                                const viewport = document.getElementById('main-content-viewport');
                                if (val) {
                                    document.body.style.overflow = 'hidden';
                                    if (viewport) viewport.style.overflowY = 'hidden';
                                } else {
                                    document.body.style.overflow = '';
                                    if (viewport) viewport.style.overflowY = '';
                                }
                            });
                            this.$cleanup(() => {
                                const viewport = document.getElementById('main-content-viewport');
                                document.body.style.overflow = '';
                                if (viewport) viewport.style.overflowY = '';
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
                                                :class="item.isSelected ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : (item.isInRange ? 'bg-emerald-50 text-emerald-800' : (item.isToday ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'hover:bg-slate-100 text-slate-700'))"
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
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden dark:border-slate-800 dark:bg-slate-900">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-900/50">
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider dark:text-slate-400">Time</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider dark:text-slate-400">Item</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider dark:text-slate-400">Movement</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right dark:text-slate-400">Quantity</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right dark:text-slate-400">Final Balance</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider dark:text-slate-400">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($this->dailyLogs as $log)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 text-slate-500 font-medium dark:text-slate-400">{{ $log->created_at->format('h:i A') }}</td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $log->item->name ?? 'Deleted Item' }}</span>
                                    <span class="text-[10px] bg-slate-100 px-1.5 py-0.5 rounded ml-1 text-slate-500 dark:text-slate-400 dark:bg-slate-800">{{ $log->item->unit ?? '' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($log->type === 'stock_in')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 uppercase tracking-wide dark:text-emerald-400">Stock In</span>
                                    @elseif($log->type === 'stock_out')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 uppercase tracking-wide dark:text-red-400">Stock Out</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 uppercase tracking-wide dark:text-blue-400">Adjustment</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-bold {{ $log->type === 'stock_in' ? 'text-emerald-600' : ($log->type === 'stock_out' ? 'text-red-600' : 'text-blue-600') }} dark:text-blue-400 dark:text-red-400 dark:text-emerald-400">
                                    {{ $log->type === 'stock_out' ? '-' : ($log->type === 'stock_in' ? '+' : '') }}{{ number_format($log->quantity, 1) }}
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-slate-700 dark:text-slate-300">{{ number_format($log->balance_after, 1) }}</td>
                                <td class="px-6 py-4 text-slate-500 text-xs italic dark:text-slate-400">{{ $log->note ?: 'No note' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    <p class="font-medium">No stock movements recorded on this day.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
