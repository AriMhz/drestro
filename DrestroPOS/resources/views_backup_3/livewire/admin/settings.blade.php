<div class="space-y-6 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Restaurant Settings</h1>
            <p class="text-sm text-slate-500 mt-1">Configure your restaurant's profile, billing, and branding.</p>
        </div>
    </div>

    <!-- Premium Slide-in Toast Notification -->
    <div 
        x-data="{ show: false }" 
        x-init="
            @if($saved)
                show = true;
                setTimeout(() => show = false, 3500);
                @php $this->saved = false; @endphp
            @endif
        "
        x-show="show"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed top-5 right-5 z-[9999] max-w-sm w-full bg-white rounded-2xl shadow-xl border border-emerald-100 p-4 pointer-events-auto flex items-start gap-3"
        x-cloak
    >
        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <div class="flex-1">
            <h3 class="font-bold text-slate-800 text-sm">Success</h3>
            <p class="text-xs text-slate-500 mt-0.5">Restaurant settings saved successfully!</p>
        </div>
        <button @click="show = false" class="text-slate-400 hover:text-slate-600 rounded-lg p-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    <form id="settings-form" wire:submit="saveSettings" class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <!-- Left Column: Identity, Billing, QR, System & Backup -->
        <div class="space-y-6">
            <!-- Restaurant Identity -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h2 class="font-bold text-slate-800">Restaurant Identity</h2>
                </div>
                <div class="p-6 space-y-5">
                    <!-- Logo Upload -->
                    <div class="flex items-center gap-6">
                        <div class="w-20 h-20 rounded-2xl bg-slate-100 border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden flex-shrink-0">
                            @if($newLogo)
                                <img src="{{ $newLogo->temporaryUrl() }}" class="w-full h-full object-cover">
                            @elseif($restaurant && $restaurant->logo)
                                <img src="{{ Storage::url($restaurant->logo) }}" class="w-full h-full object-cover">
                            @else
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Restaurant Logo</label>
                            <input wire:model="newLogo" type="file" accept="image/*" class="text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Restaurant Name *</label>
                        <input wire:model="name" type="text" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" placeholder="e.g. Himalayan Kitchen">
                        @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Tagline</label>
                        <input wire:model="tagline" type="text" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" placeholder="e.g. Authentic Nepali Flavors">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Address / Street</label>
                        <input wire:model="address" type="text" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" placeholder="e.g. Lakeside Road-6">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Ward No.</label>
                            <input wire:model="ward" type="text" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" placeholder="e.g. Ward 6">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">City / Municipality</label>
                            <input wire:model="city" type="text" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" placeholder="e.g. Pokhara">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Phone</label>
                            <input wire:model.live="phone" type="tel" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" placeholder="e.g. 9800000000">
                            @error('phone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
                            <input wire:model="email" type="email" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" placeholder="e.g. info@restaurant.com">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Billing & Tax -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h2 class="font-bold text-slate-800">Billing & Tax</h2>
                </div>
                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Currency Symbol *</label>
                            <select wire:model="currency" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                                <option value="Rs.">Rs. (Nepali Rupee)</option>
                                <option value="₹">₹ (Indian Rupee)</option>
                                <option value="$">$ (US Dollar)</option>
                                <option value="€">€ (Euro)</option>
                                <option value="£">£ (British Pound)</option>
                                <option value="¥">¥ (Japanese Yen)</option>
                                <option value="AED">AED (UAE Dirham)</option>
                                <option value="SAR">SAR (Saudi Riyal)</option>
                                <option value="QAR">QAR (Qatari Riyal)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">PAN / VAT Number</label>
                            <input wire:model="panNumber" type="text" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none" placeholder="e.g. 123456789">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Tax / VAT (%)</label>
                            <input wire:model="taxPercent" type="number" step="0.01" min="0" max="100" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none" placeholder="13">
                            <p class="text-xs text-slate-400 mt-1">Nepal VAT is typically 13%</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Service Charge (%)</label>
                            <input wire:model="serviceChargePercent" type="number" step="0.01" min="0" max="100" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none" placeholder="10">
                            <p class="text-xs text-slate-400 mt-1">Usually 10% in Nepal</p>
                        </div>
                    </div>

                    @if(auth()->user()->role === 'super_admin')
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Calendar/Date System</label>
                            <select wire:model="dateCalendarType" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                                <option value="AD">English Date (AD)</option>
                                <option value="BS">Nepali Date (BS)</option>
                            </select>
                            <p class="text-xs text-slate-400 mt-1">Select whether to display Gregorian (AD) or Bikram Sambat (BS) dates system-wide.</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Device Connection & QR Links -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h2 class="font-bold text-slate-800">Device Connection & QR Setup</h2>
                </div>
                <div class="p-6">
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 rounded-xl flex items-center gap-4">
                        <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
                        </div>
                        <div>
                            <p class="font-bold text-emerald-800 text-sm">Server Local IP: <code class="bg-emerald-200/50 px-2 py-0.5 rounded">{{ $localIp }}:8000</code></p>
                            <p class="text-xs text-emerald-600">Connect other devices (Waiters/Kitchen) to the <strong>same Wi-Fi</strong>.</p>
                        </div>
                    </div>

                    <div class="flex justify-center">
                        @php
                            $features = $activeLicense['features'] ?? [];
                            $isSuperAdmin = auth()->user()->role === 'super_admin';
                            $userPages = auth()->user()->allowed_pages ?? [];
                            $hasAllowedPages = !empty($userPages);
                            
                            $hasFeature = function($key) use ($features, $isSuperAdmin, $hasAllowedPages, $userPages) {
                                if ($isSuperAdmin) return true;
                                if ($hasAllowedPages && in_array($key, $userPages)) return true;
                                if (in_array('all', $features) || in_array('Enterprise', $features)) return true;
                                
                                $aliases = [
                                    'reports' => ['reports_analytics', 'reports', 'Reports & Analytics'],
                                    'cashier_panel' => ['cashier', 'cashier_panel', 'Cashier Panel'],
                                    'take_room_service' => ['room_service', 'take_room_service', 'Take Room Service'],
                                    'take_order' => ['restaurant_order', 'take_order', 'Take Order'],
                                    'menu_manager' => ['menus', 'menu_manager', 'Menu Manager'],
                                    'restaurant_tables' => ['tables', 'restaurant_tables', 'Restaurant Tables'],
                                    'hotel_room_manager' => ['rooms', 'hotel_room_manager', 'Hotel Room Manager'],
                                    'hotel_reception' => ['reception', 'hotel_reception', 'Hotel Reception Cashier'],
                                    'kitchen_display' => ['kitchen', 'kitchen_display', 'Kitchen Display'],
                                    'bar_display' => ['bar', 'bar_display', 'Bar Display'],
                                    'waiter_dashboard' => ['waiter', 'waiter_dashboard', 'Waiter Dashboard'],
                                    'inventory' => ['inventory_manager', 'inventory', 'Inventory'],
                                    'staff_management' => ['staff', 'staff_management', 'Staff & Roles'],
                                ];
                                
                                if (in_array($key, $features)) return true;
                                if (isset($aliases[$key])) {
                                    foreach($aliases[$key] as $alias) {
                                        if (in_array($alias, $features)) return true;
                                    }
                                }
                                return false;
                            };

                            $baseUrl = 'http://' . $localIp . ':8000';
                        @endphp

                        <!-- Admin / Staff Login QR -->
                        <div class="flex flex-col items-center p-6 border border-slate-100 rounded-2xl bg-slate-50/50 hover:shadow-md transition-shadow max-w-xs w-full">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($baseUrl . '/login') }}" class="w-36 h-36 rounded-xl shadow-sm border border-white" alt="Staff Login QR">
                            <p class="mt-3 font-bold text-slate-700 text-sm">🧑‍💼 Staff & Admin Login</p>
                            <a href="{{ $baseUrl }}/login" target="_blank" class="text-[10px] text-blue-500 mt-1 font-mono hover:underline text-center break-all">{{ $baseUrl }}/login</a>
                        </div>
                    </div>
                    
                    <div class="mt-6 text-center">
                        <p class="text-xs text-slate-400">Scan this QR code with a Tablet or Phone connected to the <strong>same Wi-Fi</strong> to log in.</p>
                        <p class="text-[10px] text-amber-500 font-semibold mt-1">⚠️ Your devices must be on the same router network ({{ $localIp }})</p>
                    </div>
                </div>
            </div>

            <!-- System & Backup -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h2 class="font-bold text-slate-800">System & Backup</h2>
                </div>
                <div class="p-6 space-y-4">
                    {{-- Download Backup --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-blue-50 border border-blue-100 rounded-xl gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            </div>
                            <div>
                                <p class="font-bold text-blue-800 text-sm">Download Database Backup</p>
                                <p class="text-xs text-blue-600">Save a copy of your menu, orders, and inventory. Keep this on a USB drive for safety.</p>
                            </div>
                        </div>
                        <button type="button" wire:click="downloadBackup" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition-colors shadow-md shadow-blue-600/20 shrink-0">
                            Download .sqlite
                        </button>
                    </div>

                    {{-- Upload/Restore Backup --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-amber-50 border border-amber-100 rounded-xl gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            </div>
                            <div>
                                <p class="font-bold text-amber-800 text-sm">Upload & Restore Database Backup</p>
                                <p class="text-xs text-amber-600">Restore your database from a previously saved .sqlite file. <strong class="text-rose-600">WARNING: This will replace all current data!</strong></p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-2 shrink-0">
                            <label class="px-4 py-2 bg-amber-600 text-white text-xs font-bold rounded-lg hover:bg-amber-700 transition-colors shadow-md shadow-amber-600/20 cursor-pointer text-center">
                                Upload & Restore .sqlite
                                <input type="file" wire:model="backupFile" class="hidden" accept=".sqlite">
                            </label>
                            <div wire:loading wire:target="backupFile" class="text-[10px] text-amber-600 font-semibold animate-pulse">
                                Restoring database...
                            </div>
                        </div>
                    </div>
                    
                    {{-- USB Pendrive Auto-Backup --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-emerald-50 border border-emerald-100 rounded-xl gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center shrink-0 text-xl">
                                💾
                            </div>
                            <div>
                                <p class="font-bold text-emerald-800 text-sm">Backup to USB Pendrive</p>
                                <p class="text-xs text-emerald-600">Scan connected drive letters (D: to Z:) and instantly back up your database to any connected USB drive.</p>
                            </div>
                        </div>
                        <button type="button" wire:click="backupToUsbManual" wire:loading.attr="disabled"
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-colors shadow-md shadow-emerald-600/20 shrink-0 flex items-center gap-2">
                            <span wire:loading.remove wire:target="backupToUsbManual" class="flex items-center gap-1.5">
                                🔍 Scan & Backup to USB
                            </span>
                            <span wire:loading wire:target="backupToUsbManual" class="flex items-center gap-2">
                                <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Scanning & Backing up...
                            </span>
                        </button>
                    </div>

                    @if (session()->has('usb_success'))
                        <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs rounded-xl font-bold">
                            🎉 {{ session('usb_success') }}
                        </div>
                    @endif
                    @if (session()->has('usb_error'))
                        <div class="p-4 bg-rose-50 border border-rose-100 text-rose-800 text-xs rounded-xl font-bold">
                            ⚠️ {{ session('usb_error') }}
                        </div>
                    @endif

                    @if (session()->has('backup_success'))
                        <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs rounded-xl font-bold">
                            {{ session('backup_success') }}
                        </div>
                    @endif
                    @if (session()->has('backup_error'))
                        <div class="p-4 bg-rose-50 border border-rose-100 text-rose-800 text-xs rounded-xl font-bold">
                            {{ session('backup_error') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Hardware & Printers -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h2 class="font-bold text-slate-800">Hardware & Printers</h2>
                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded">ESC/POS Supported</span>
                </div>
                
                @if(auth()->user()->role === 'super_admin')
                <div class="p-6 space-y-6">
                    <!-- Network Printer Scanner -->
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm">Network Printer Scanner</h3>
                                <p class="text-xs text-slate-500 mt-1">If your printer's IP changes frequently (DHCP), we strongly recommend setting a <strong class="text-slate-700">Static IP / DHCP Reservation</strong> in your Wi-Fi router. Otherwise, use the scanner to find the current IP.</p>
                            </div>
                            <button type="button" wire:click="scanPrinters" class="px-4 py-2 bg-white border border-slate-300 rounded-lg shadow-sm text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-2 shrink-0 self-start" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="scanPrinters">🔍 Scan Network</span>
                                <span wire:loading wire:target="scanPrinters" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Scanning...
                                </span>
                            </button>
                        </div>

                        @if(session('scan_error'))
                            <div class="mt-3 p-2 bg-red-50 text-red-600 text-xs font-semibold rounded border border-red-100">
                                {{ session('scan_error') }}
                            </div>
                        @endif

                        @if(!empty($scannedPrinters))
                            <div class="mt-4 pt-4 border-t border-slate-200">
                                <p class="text-xs font-bold text-slate-700 mb-2">Found Printers (Click IP to copy):</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($scannedPrinters as $printerIp)
                                        <button type="button" 
                                            onclick="navigator.clipboard.writeText('{{ $printerIp }}'); alert('Copied IP: {{ $printerIp }}. You can paste it below.');"
                                            class="px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-mono font-bold rounded-lg border border-emerald-200 hover:bg-emerald-100 transition-colors cursor-pointer flex items-center gap-1"
                                            title="Click to copy">
                                            🖨️ {{ $printerIp }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- USB Printer Scanner -->
                    <div class="p-4 bg-blue-50 rounded-xl border border-blue-200">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                            <div>
                                <h3 class="font-bold text-blue-800 text-sm">USB Printer Auto-Detect</h3>
                                <p class="text-xs text-blue-600 mt-1">Using a USB printer? Scan to find your locally installed printers. <br><strong>Note:</strong> You MUST enable sharing in Windows Printer settings first!</p>
                            </div>
                            <button type="button" wire:click="scanUsbPrinters" class="px-4 py-2 bg-white border border-blue-300 rounded-lg shadow-sm text-sm font-semibold text-blue-700 hover:bg-slate-50 transition-colors flex items-center gap-2 shrink-0 self-start" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="scanUsbPrinters">🔌 Find USB Printers</span>
                                <span wire:loading wire:target="scanUsbPrinters" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Scanning...
                                </span>
                            </button>
                        </div>

                        @if(session('usb_scan_error'))
                            <div class="mt-3 p-2 bg-red-50 text-red-600 text-xs font-semibold rounded border border-red-100">
                                {{ session('usb_scan_error') }}
                            </div>
                        @endif

                        @if(!empty($usbPrinters))
                            <div class="mt-4 pt-4 border-t border-blue-200 space-y-2">
                                <p class="text-xs font-bold text-blue-800">Installed Printers ({{ count($usbPrinters) }} found):</p>
                                <div class="space-y-3">
                                    @foreach($usbPrinters as $printer)
                                        <div class="p-3 bg-white border {{ $printer['is_shared'] ? 'border-emerald-200' : 'border-slate-200' }} rounded-lg shadow-sm">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="text-sm font-bold text-slate-800">🖨️ {{ $printer['name'] }}</p>
                                                    @if($printer['is_shared'])
                                                        <p class="text-xs text-emerald-600 font-semibold flex items-center gap-1 mt-1">
                                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                                            Shared as: {{ $printer['share_name'] }}
                                                        </p>
                                                    @else
                                                        <p class="text-xs text-amber-600 font-semibold mt-1">⚠️ Not shared — enable sharing in Windows first</p>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($printer['is_shared'])
                                                @php $path = 'smb://127.0.0.1/' . $printer['share_name']; @endphp
                                                <div class="mt-2 pt-2 border-t border-slate-100 flex flex-wrap items-center gap-2">
                                                    <span class="text-[10px] text-slate-400 font-bold uppercase">Assign to:</span>
                                                    <button type="button" wire:click="$set('kitchenPrinterPath', '{{ $path }}')" 
                                                        class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-colors {{ $kitchenPrinterPath === $path ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-emerald-100 hover:text-emerald-700' }}">
                                                        🔪 Kitchen
                                                    </button>
                                                    <button type="button" wire:click="$set('cashierPrinterPath', '{{ $path }}')"
                                                        class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-colors {{ $cashierPrinterPath === $path ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-blue-100 hover:text-blue-700' }}">
                                                        💰 Cashier
                                                    </button>
                                                    <button type="button" wire:click="$set('hotelPrinterPath', '{{ $path }}')"
                                                        class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-colors {{ $hotelPrinterPath === $path ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-indigo-100 hover:text-indigo-700' }}">
                                                        🏨 Hotel
                                                    </button>
                                                    <button type="button" wire:click="$set('botPrinterPath', '{{ $path }}')"
                                                        class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-colors {{ $botPrinterPath === $path ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-amber-100 hover:text-amber-700' }}">
                                                        🍸 Bar
                                                    </button>
                                                    <button type="button" 
                                                        onclick="navigator.clipboard.writeText('{{ $path }}'); alert('Copied: {{ $path }}')"
                                                        class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">
                                                        📋 Copy
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Combined/Separate Toggle -->
                    <div class="p-5 bg-gradient-to-br from-emerald-50 to-teal-50 rounded-2xl border border-emerald-200 shadow-sm">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-emerald-600 text-white rounded-lg flex items-center justify-center shadow-md shadow-emerald-600/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                    </div>
                                    <h3 class="font-black text-emerald-900 text-sm tracking-tight">Printing Logic (KOT/BOT)</h3>
                                </div>
                                <p class="text-[11px] text-emerald-700/70 mt-1 font-bold">Smart routing for kitchen & bar orders</p>
                            </div>
                            
                            <div class="inline-flex p-1.5 bg-white/80 backdrop-blur-sm rounded-2xl border border-emerald-200/50 shadow-inner">
                                <button type="button" wire:click="$set('separateKotBot', false)" 
                                    class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-300 {{ !$separateKotBot ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/40 ring-1 ring-emerald-400' : 'text-slate-400 hover:text-emerald-600 hover:bg-emerald-50/50' }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    Combined
                                </button>
                                <button type="button" wire:click="$set('separateKotBot', true)" 
                                    class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-300 {{ $separateKotBot ? 'bg-teal-600 text-white shadow-lg shadow-teal-600/40 ring-1 ring-teal-400' : 'text-slate-400 hover:text-teal-600 hover:bg-teal-50/50' }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                    Separate
                                </button>
                            </div>
                        </div>
                        
                        <div class="mt-4 flex items-start gap-3 p-3 bg-white/40 rounded-xl border border-emerald-200/30">
                            <svg class="w-4 h-4 text-emerald-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                            <p class="text-[11px] text-emerald-800 leading-relaxed font-medium">
                                @if(!$separateKotBot)
                                    <strong class="font-black">COMBINED MODE:</strong> Food and Bar items will be printed together on a single ticket (KOT/BOT) using the Kitchen Printer.
                                @else
                                    <strong class="font-black text-teal-700">SEPARATE MODE:</strong> Food items will go to the <span class="underline decoration-2">Kitchen Printer</span>, and Drinks will go to the <span class="underline decoration-2">Bar Printer</span>.
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Kitchen Printer -->
                    <div class="border border-slate-100 rounded-xl p-4 bg-slate-50/30">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                <h3 class="font-bold text-slate-700">
                                    @if(!$separateKotBot)
                                        Kitchen Printer (KOT/BOT)
                                    @else
                                        Kitchen Printer (KOT Only)
                                    @endif
                                </h3>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="autoPrintKot" class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <span class="text-sm font-semibold text-slate-600">
                                    @if(!$separateKotBot)
                                        Auto-print KOT/BOT
                                    @else
                                        Auto-print KOT
                                    @endif
                                </span>
                            </label>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase">Connection Type</label>
                                <select wire:model.live="kitchenPrinterType" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                                    <option value="network">Network (LAN/Wi-Fi)</option>
                                    <option value="usb">Windows USB Share</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase">Address / Share Name</label>
                                <input wire:model="kitchenPrinterPath" type="text" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 outline-none" placeholder="{{ $kitchenPrinterType == 'network' ? '192.168.1.100:9100' : 'smb://127.0.0.1/KitchenPrinter' }}">
                            </div>
                        </div>
                        <div class="mt-3 flex items-center gap-3">
                            <button type="button" wire:click="testKitchenPrinter" class="px-4 py-2 bg-slate-700 text-white text-xs font-bold rounded-lg hover:bg-slate-800 transition-colors shadow-sm">
                                🖨️ Test Kitchen
                            </button>
                            @if($printerTestResult === 'kitchen_ok')
                            <span class="text-xs font-bold text-emerald-600">✅ Test print sent!</span>
                            @elseif($printerTestResult === 'kitchen_fail')
                            <span class="text-xs font-bold text-red-600">❌ {{ $printerTestError }}</span>
                            @endif
                        </div>
                    </div>

                    @if($separateKotBot)
                    <!-- BOT Printer -->
                    <div class="border border-amber-100 rounded-xl p-4 bg-amber-50/20">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                <h3 class="font-bold text-amber-700">Bar Printer (BOT)</h3>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase">Connection Type</label>
                                <select wire:model.live="botPrinterType" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                                    <option value="network">Network (LAN/Wi-Fi)</option>
                                    <option value="usb">Windows USB Share</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase">Address / Share Name</label>
                                <input wire:model="botPrinterPath" type="text" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 outline-none" placeholder="{{ $botPrinterType == 'network' ? '192.168.1.103:9100' : 'smb://127.0.0.1/BarPrinter' }}">
                            </div>
                        </div>
                        <div class="mt-3 flex items-center gap-3">
                            <button type="button" wire:click="testBotPrinter" class="px-4 py-2 bg-amber-600 text-white text-xs font-bold rounded-lg hover:bg-amber-700 transition-colors shadow-sm">
                                🖨️ Test Bar
                            </button>
                            @if($printerTestResult === 'bot_ok')
                            <span class="text-xs font-bold text-emerald-600">✅ Test print sent!</span>
                            @elseif($printerTestResult === 'bot_fail')
                            <span class="text-xs font-bold text-red-600">❌ {{ $printerTestError }}</span>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Cashier Printer -->
                    <div class="border border-slate-100 rounded-xl p-4 bg-slate-50/30">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <h3 class="font-bold text-slate-700">Cashier Printer (Receipts)</h3>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="autoPrintReceipt" class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <span class="text-sm font-semibold text-slate-600">Auto-print Receipt</span>
                            </label>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase">Connection Type</label>
                                <select wire:model.live="cashierPrinterType" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                                    <option value="network">Network (LAN/Wi-Fi)</option>
                                    <option value="usb">Windows USB Share</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase">Address / Share Name</label>
                                <input wire:model="cashierPrinterPath" type="text" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 outline-none" placeholder="{{ $cashierPrinterType == 'network' ? '192.168.1.101:9100' : 'smb://127.0.0.1/CashierPrinter' }}">
                            </div>
                        </div>
                        <div class="mt-3 flex items-center gap-3">
                            <button type="button" wire:click="testCashierPrinter" class="px-4 py-2 bg-slate-700 text-white text-xs font-bold rounded-lg hover:bg-slate-800 transition-colors shadow-sm">
                                🖨️ Test Print
                            </button>
                            @if($printerTestResult === 'cashier_ok')
                            <span class="text-xs font-bold text-emerald-600">✅ Test print sent!</span>
                            @elseif($printerTestResult === 'cashier_fail')
                            <span class="text-xs font-bold text-red-600">❌ {{ $printerTestError }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Hotel Printer -->
                    <div class="border border-indigo-100 rounded-xl p-4 bg-indigo-50/20">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                <h3 class="font-bold text-indigo-700">Hotel Printer (Reception)</h3>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="hotelAutoPrint" class="w-4 h-4 text-indigo-600 rounded border-indigo-300 focus:ring-indigo-500">
                                <span class="text-sm font-semibold text-indigo-600">Auto-print Bills</span>
                            </label>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase">Connection Type</label>
                                <select wire:model.live="hotelPrinterType" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                    <option value="network">Network (LAN/Wi-Fi)</option>
                                    <option value="usb">Windows USB Share</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase">Address / Share Name</label>
                                <input wire:model="hotelPrinterPath" type="text" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="{{ $hotelPrinterType == 'network' ? '192.168.1.102:9100' : 'smb://127.0.0.1/HotelPrinter' }}">
                            </div>
                        </div>
                        <div class="mt-3 flex items-center gap-3">
                            <button type="button" wire:click="testHotelPrinter" class="px-4 py-2 bg-indigo-700 text-white text-xs font-bold rounded-lg hover:bg-indigo-800 transition-colors shadow-sm">
                                🖨️ Test Print
                            </button>
                            @if($printerTestResult === 'hotel_ok')
                            <span class="text-xs font-bold text-emerald-600">✅ Test print sent!</span>
                            @elseif($printerTestResult === 'hotel_fail')
                            <span class="text-xs font-bold text-red-600">❌ {{ $printerTestError }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <p class="px-6 pb-6 text-xs text-slate-400"><strong>Note:</strong> When Auto-print is enabled, placing an order from the Waiter App will instantly send the print command to these hardware printers. No browser print dialog will appear.</p>
                @else
                <div class="p-8 flex flex-col items-center justify-center text-center space-y-5">
                    <div class="w-16 h-16 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center shadow-sm border border-amber-100/50">
                        <!-- Premium Lock Icon -->
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-base font-bold text-slate-800">Super Admin Access Required</h3>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto leading-relaxed">Hardware and printer configurations are restricted to prevent operational disruption. Please contact your system administrator or reach out to support for any assistance.</p>
                    </div>
                    <div class="w-full pt-4 border-t border-slate-100 max-w-sm">
                        <div class="p-4 bg-emerald-50/60 border border-emerald-100 rounded-2xl flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <p class="text-[10px] text-emerald-600 font-bold uppercase tracking-wider">Need Support?</p>
                                    <p class="font-black text-emerald-900 text-sm tracking-tight">Support: +977 9865029558</p>
                                </div>
                            </div>
                            <a href="tel:+9779865029558" class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-emerald-600/10 hover:shadow-lg hover:shadow-emerald-600/20 active:scale-[0.98] shrink-0 min-w-[70px] text-center">
                                Call
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </form>

    @if(auth()->user()->role === 'super_admin')
    <!-- Dangerous Area -->
    <div class="mt-8 bg-white rounded-2xl shadow-sm border border-red-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-red-100 bg-red-50/50 flex justify-between items-center">
            <h2 class="font-bold text-red-700 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                Dangerous Area
            </h2>
        </div>
        
        <div class="p-6 space-y-6">
            @if (session()->has('danger_success'))
                <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs rounded-xl font-bold">
                    ✅ {{ session('danger_success') }}
                </div>
            @endif
            @if (session()->has('danger_error'))
                <div class="p-4 bg-red-50 border border-red-100 text-red-800 text-xs rounded-xl font-bold">
                    ❌ {{ session('danger_error') }}
                </div>
            @endif

            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-5 bg-orange-50/50 border border-orange-100 rounded-xl gap-4">
                <div>
                    <h3 class="font-bold text-orange-800 text-sm">Reset Restaurant Data</h3>
                    <p class="text-xs text-orange-600 mt-1">This will permanently delete all Orders, Items, Invoices, and Inventory Logs. Settings and Menu will be kept.</p>
                </div>
                <button type="button" wire:click="resetRestaurantData" onclick="confirm('Are you absolutely sure you want to RESET all transaction data? This cannot be undone!') || event.stopImmediatePropagation()" class="px-5 py-2.5 bg-white border-2 border-orange-200 text-orange-700 text-xs font-bold rounded-xl hover:bg-orange-100 transition-colors shrink-0">
                    Reset Data
                </button>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-5 bg-red-50 border border-red-200 rounded-xl gap-4">
                <div>
                    <h3 class="font-bold text-red-800 text-sm">Delete Restaurant</h3>
                    <p class="text-xs text-red-600 mt-1">This will permanently delete the entire restaurant from the database, including all staff and data.</p>
                </div>
                <button type="button" wire:click="deleteRestaurant" onclick="confirm('Are you absolutely sure you want to DELETE the entire restaurant? This action is PERMANENT and irreversible!') || event.stopImmediatePropagation()" class="px-5 py-2.5 bg-red-600 text-white text-xs font-bold rounded-xl hover:bg-red-700 transition-colors shadow-md shadow-red-600/20 shrink-0">
                    Delete Restaurant
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
