<div class="h-full bg-slate-50 font-sans">
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
        .bg-amber-500 {
            background-color: #f59e0b !important;
            color: #ffffff !important;
        }
        .hover\:bg-amber-600:hover {
            background-color: #d97706 !important;
            color: #ffffff !important;
        }
    </style>
    <div class="flex flex-col md:flex-row h-[calc(100vh-100px)]">
        
        <!-- Sidebar: Room Navigator -->
        <div class="{{ $selectedRoom ? 'hidden md:flex' : 'flex' }} w-full md:w-72 bg-white border-r border-slate-200 flex-col h-full shrink-0">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-tight">Active Guests</h2>
                <a href="{{ route('staff.cashier') }}?activeTab=hotel&statusFilter=completed" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-all flex items-center gap-1" title="View Past Stays">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    History
                </a>
            </div>
            
            <div class="flex-1 overflow-y-auto p-3 space-y-2">
                @forelse($rooms as $room)
                <button wire:click="selectRoom({{ $room->id }})" class="w-full p-4 rounded-xl text-left transition-all {{ $selectedRoomId == $room->id ? 'bg-indigo-600 text-white shadow-lg' : 'bg-white border border-slate-200 text-slate-600 hover:border-indigo-300' }}">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold">Room {{ $room->name }}</span>
                        <div class="w-2 h-2 rounded-full {{ $selectedRoomId == $room->id ? 'bg-white' : 'bg-emerald-500' }}"></div>
                    </div>
                    <p class="text-[10px] font-medium uppercase opacity-70 mt-1 truncate">{{ $room->guest_name }}</p>
                </button>
                @empty
                <div class="py-10 text-center text-slate-400">
                    <p class="text-xs uppercase font-bold italic">No active stays</p>
                </div>
                @endforelse
            </div>

            <!-- Book Room Button (always visible at bottom of sidebar) -->
            <div class="p-3 border-t border-slate-100">
                <a href="{{ route('staff.room-service') }}" class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 shadow-md shadow-amber-500/10 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Book Room / Room Service
                </a>
            </div>
        </div>

        <!-- Main Workspace -->
        <div class="{{ $selectedRoom ? 'block' : '' }} flex-1 overflow-y-auto p-4 md:p-8 relative">
            @if($selectedRoom)
                <!-- Guest Header -->
                <div class="bg-white rounded-2xl p-4 md:p-6 shadow-sm border border-slate-200 mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 md:gap-0">
                    <div class="flex items-center gap-3 md:gap-5 w-full md:w-auto">
                        <button wire:click="$set('selectedRoom', null)" class="md:hidden p-2 -ml-2 bg-slate-100 rounded-lg text-slate-600 hover:bg-slate-200 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </button>
                        <div class="w-12 h-12 bg-slate-800 text-white rounded-xl flex items-center justify-center text-xl font-bold shrink-0">
                            {{ substr($selectedRoom->guest_name, 0, 1) }}
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">{{ $selectedRoom->guest_name }}</h2>
                            <p class="text-xs text-slate-500">
                                Room {{ $selectedRoom->name }} 
                                @if($selectedRoom->guest_phone)
                                    • Phone: {{ $selectedRoom->guest_phone }}
                                @endif
                                @if($selectedRoom->guest_id_number)
                                    • ID: {{ $selectedRoom->guest_id_number }}
                                @endif
                                • Checked in {{ \App\Helpers\DateHelper::format($selectedRoom->check_in_at) }} • {{ \Carbon\Carbon::parse($selectedRoom->check_in_at)->format('h:i A') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <!-- Room Service Order -->
                        <a href="{{ route('staff.room-service', ['room' => $selectedRoom->id]) }}" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-2 shadow-md shadow-amber-500/10 active:scale-95" title="Order Food / Room Service">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Room Service
                        </a>

                        <!-- Direct Thermal Printer -->
                        <button wire:click="printInterimBill" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-2 shadow-md shadow-indigo-600/10 active:scale-95" title="Print Invoice">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Print
                        </button>
                        
                        <!-- Complete Checkout -->
                        <button wire:click="openPaymentModal" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md transition-all active:scale-95 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            Checkout
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 md:gap-8" id="pdf-content">
                    <!-- Left: Details -->
                    <div class="lg:col-span-8 space-y-8">
                        <!-- Stay Charges -->
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Accommodation</h3>
                                <button wire:click="addDay" class="text-[10px] font-bold text-indigo-600 hover:underline">+ Add Stay Day</button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6">
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 text-center">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Rate</p>
                                    <p class="text-lg font-bold text-slate-900">Rs. {{ number_format($selectedRoom->room_rate, 0) }}</p>
                                </div>
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 text-center">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Duration</p>
                                    <p class="text-lg font-bold text-slate-900">{{ max(1, \Carbon\Carbon::parse($selectedRoom->check_in_at)->diffInDays(now())) + $manualDays }} Days</p>
                                </div>
                                <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100 text-center">
                                    <p class="text-[10px] font-bold text-indigo-400 uppercase mb-1">Subtotal</p>
                                    <p class="text-lg font-bold text-indigo-600">Rs. {{ number_format((max(1, \Carbon\Carbon::parse($selectedRoom->check_in_at)->diffInDays(now())) + $manualDays) * $selectedRoom->room_rate, 0) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Orders -->
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6">Service Charges</h3>
                            <div class="space-y-4">
                                @forelse($activeOrders as $order)
                                <div class="p-4 bg-slate-50 rounded-xl flex justify-between items-center border border-slate-100">
                                    <div>
                                        <p class="text-xs font-bold text-slate-800 uppercase tracking-tight">Order #{{ $order->order_number }}</p>
                                        <p class="text-[10px] text-slate-500">{{ $order->created_at->format('h:i A') }} • {{ $order->items->count() }} items</p>
                                    </div>
                                    <span class="text-sm font-bold text-slate-900">Rs. {{ number_format($order->total_amount, 0) }}</span>
                                </div>
                                @empty
                                <p class="text-center py-6 text-xs text-slate-400 italic">No hospitality charges found</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Right: Summary -->
                    <div class="lg:col-span-4">
                        <div class="bg-slate-900 rounded-2xl p-6 shadow-lg text-white">
                            <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-8 text-center">Stay Settlement</h3>
                            <div class="space-y-4 mb-8">
                                <div class="flex justify-between text-xs font-medium text-slate-300">
                                    <span>Room Total</span>
                                    <span>Rs. {{ number_format((max(1, \Carbon\Carbon::parse($selectedRoom->check_in_at)->diffInDays(now())) + $manualDays) * $selectedRoom->room_rate, 0) }}</span>
                                </div>
                                <div class="flex justify-between text-xs font-medium text-slate-300">
                                    <span>Service Total</span>
                                    <span>Rs. {{ number_format(collect($activeOrders)->sum('total_amount'), 0) }}</span>
                                </div>
                                <div class="pt-6 border-t border-white/10">
                                    <p class="text-[10px] font-bold text-indigo-400 uppercase mb-1">Total Payable</p>
                                    <p class="text-3xl font-bold">Rs. {{ number_format($totalBill, 0) }}</p>
                                </div>
                            </div>
                            <button wire:click="openPaymentModal" class="w-full py-4 bg-white text-slate-900 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-emerald-500 hover:text-white transition-all">
                                Confirm Checkout
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <!-- Printable Hotel Reception Report -->
                <div class="h-full flex flex-col overflow-hidden bg-slate-50 print:bg-white print:overflow-visible">
                    <!-- Top capsule header (print-hidden) -->
                    <div class="p-6 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4 bg-white shadow-sm print-hidden rounded-2xl mb-6">
                        <div>
                            <h3 class="text-xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                Hotel Reception Summary
                            </h3>
                            <p class="text-xs text-slate-500 font-medium">Occupancy status and stay settlements record.</p>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row flex-wrap items-stretch gap-4 bg-white rounded-2xl border border-slate-200 p-4 shadow-sm print-hidden w-full xl:w-auto mt-2 xl:mt-0">
                            <!-- Section 1: Presets -->
                            <div class="flex flex-col gap-1.5 justify-between">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Presets</span>
                                <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl h-[38px] shrink-0">
                                    <button wire:click="setPeriod('today')"
                                        class="h-full px-5 rounded-lg text-xs font-bold transition-all {{ $period === 'today' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">Today</button>
                                    <button wire:click="setPeriod('week')"
                                        class="h-full px-5 rounded-lg text-xs font-bold transition-all {{ $period === 'week' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">Week</button>
                                    <button wire:click="setPeriod('month')"
                                        class="h-full px-5 rounded-lg text-xs font-bold transition-all {{ $period === 'month' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">Month</button>
                                </div>
                            </div>
                            
                            <div class="w-[1px] bg-slate-200 hidden sm:block self-stretch my-1"></div>
                            
                            <!-- Section 2: Custom Date Range Picker -->
                            <div wire:ignore wire:key="calendar-picker-hotel" class="flex flex-col gap-1.5"
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
                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 bg-slate-100/60 p-1.5 border border-slate-200 rounded-xl shrink-0">
                                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                        <!-- Date From Input -->
                                        <div @click.stop="pickingField = 'start'; pickerOpen = true; syncFromSelected()" 
                                             :class="pickingField === 'start' && pickerOpen ? 'bg-emerald-50 text-emerald-800 border-emerald-300 ring-2 ring-emerald-500/10' : 'bg-white hover:bg-slate-50 border-slate-200'"
                                             class="flex items-center justify-between gap-2 px-3 py-1.5 border rounded-lg cursor-pointer transition-all shadow-sm">
                                            <span class="text-xs font-black text-slate-700 select-none whitespace-nowrap" x-text="getFormattedDate(startDate)"></span>
                                            <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>

                                        <span class="text-[10px] font-black text-slate-400 text-center">to</span>

                                        <!-- Date To Input -->
                                        <div @click.stop="pickingField = 'end'; pickerOpen = true; syncFromSelected()" 
                                             :class="pickingField === 'end' && pickerOpen ? 'bg-emerald-50 text-emerald-800 border-emerald-300 ring-2 ring-emerald-500/10' : 'bg-white hover:bg-slate-50 border-slate-200'"
                                             class="flex items-center justify-between gap-2 px-3 py-1.5 border rounded-lg cursor-pointer transition-all shadow-sm">
                                            <span class="text-xs font-black text-slate-700 select-none whitespace-nowrap" x-text="getFormattedDate(endDate)"></span>
                                            <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    </div>

                                    <div class="h-[1px] w-full sm:h-5 sm:w-[1px] bg-slate-200 mx-0.5"></div>

                                    <!-- Quick selectors -->
                                    <div class="flex items-center gap-1 justify-center">
                                        <button type="button" @click="quickSelect(7)" class="flex-1 sm:flex-none px-3 py-1.5 text-[10px] font-black text-emerald-600 bg-white hover:bg-emerald-50 border border-slate-200 rounded-lg transition-all shadow-sm uppercase tracking-wider flex items-center justify-center">7d</button>
                                        <button type="button" @click="quickSelect(14)" class="flex-1 sm:flex-none px-3 py-1.5 text-[10px] font-black text-emerald-600 bg-white hover:bg-emerald-50 border border-slate-200 rounded-lg transition-all shadow-sm uppercase tracking-wider flex items-center justify-center">14d</button>
                                        <button type="button" @click="quickSelect(30)" class="flex-1 sm:flex-none px-3 py-1.5 text-[10px] font-black text-emerald-600 bg-white hover:bg-emerald-50 border border-slate-200 rounded-lg transition-all shadow-sm uppercase tracking-wider flex items-center justify-center">30d</button>
                                    </div>
                                </div>
                                
                                <!-- Floating AD/BS Visual Grid Picker -->
                                <div x-show="pickerOpen" x-cloak 
                                     style="position: fixed; z-index: 9999; width: 320px;"
                                     :style="pickerOpen ? { top: $refs.pickerWrap.getBoundingClientRect().bottom + 8 + 'px', left: $refs.pickerWrap.getBoundingClientRect().left + 'px' } : {}"
                                     class="bg-white border border-slate-200 rounded-2xl shadow-2xl p-4 space-y-3">
                                    
                                    <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                                        <span class="text-xs font-black text-slate-800 uppercase tracking-wider" x-text="calMode === 'bs' ? 'Nepali Calendar (BS)' : 'Gregorian Calendar (AD)'"></span>
                                        @php
                                            $currentCal = strtoupper(current_restaurant()->date_calendar_type ?? 'AD');
                                        @endphp
                                        @if(auth()->user()->role === 'super_admin')
                                            <div class="flex items-center bg-slate-100 border border-slate-200 p-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest select-none shrink-0">
                                                <a href="{{ route('toggle-calendar', 'ad') }}" class="px-2 py-0.5 rounded transition-all {{ $currentCal === 'AD' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">AD</a>
                                                <a href="{{ route('toggle-calendar', 'bs') }}" class="px-2 py-0.5 rounded transition-all {{ $currentCal === 'BS' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">BS</a>
                                            </div>
                                        @else
                                            <div class="flex items-center bg-slate-50 border border-slate-200 px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest select-none shrink-0 text-slate-500 font-bold">
                                                Calendar: {{ $currentCal }}
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Interactive Visual Calendar Grid -->
                                    <div class="space-y-2">
                                        <!-- Month Selector Header -->
                                        <div class="flex items-center justify-between pb-1">
                                            <button type="button" @click.stop="prevMonth()" class="p-1 hover:bg-slate-100 rounded-lg text-slate-500 hover:text-slate-700 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                            </button>
                                            <div class="text-sm font-black text-slate-800 flex items-center gap-1 select-none">
                                                <span x-text="calMode === 'bs' ? nepMonthNames[viewMonth - 1] : engMonthNames[viewMonth - 1]"></span>
                                                <span x-text="viewYear" class="text-xs text-slate-400 font-black"></span>
                                            </div>
                                            <button type="button" @click.stop="nextMonth()" class="p-1 hover:bg-slate-100 rounded-lg text-slate-500 hover:text-slate-700 transition-colors">
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
                                                            :class="item.isSelected ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : (item.isInRange ? 'bg-indigo-50 text-indigo-800' : (item.isToday ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'hover:bg-slate-100 text-slate-700'))"
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
                                    <div class="pt-2 border-t border-slate-100 flex flex-col gap-1 text-[10px] select-none">
                                        <div class="flex justify-between items-center text-[9px] text-slate-400 font-bold uppercase">
                                            <span>Picking Field</span>
                                            <span class="text-indigo-600 font-black uppercase tracking-wider" x-text="pickingField === 'start' ? 'Selecting From' : 'Selecting To'"></span>
                                        </div>
                                        <div class="flex justify-between items-center font-bold text-slate-700">
                                            <span class="text-slate-500">Range:</span>
                                            <span class="text-slate-800" x-text="getFormattedDate(startDate) + ' → ' + getFormattedDate(endDate)"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="w-[1px] bg-slate-200 hidden sm:block self-stretch my-1"></div>
                            
                            <!-- Section 3: Actions -->
                            <div class="flex flex-col gap-1.5 justify-between">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Actions</span>
                                <div class="flex flex-wrap items-center gap-2 shrink-0">
                                    <button onclick="window.print()" class="px-4 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-all flex items-center gap-2 border border-slate-200 shadow-sm" title="Print Report">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        Print
                                    </button>
                                    <button onclick="downloadPDF('Hotel_Cashier_Report.pdf')" class="px-4 py-1.5 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 hover:bg-indigo-100 transition-all flex items-center gap-2 shadow-sm" title="Save Report as PDF">
                                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        Save PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scrollable Report View -->
                    <div class="flex-1 overflow-y-auto p-6 sm:p-8 space-y-8 print:overflow-visible print:p-0" id="hotel-sales-report-content">
                        <!-- Stats Overview (Grid Cards) -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Card 1: Occupancy -->
                            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100/50 border border-indigo-100 p-5 rounded-2xl shadow-sm flex flex-col justify-between">
                                <span class="text-[10px] font-black text-indigo-800 uppercase tracking-wider">Occupancy</span>
                                <div class="mt-2">
                                    <span class="text-2xl font-black text-slate-900">{{ round(($stats['total_occupied'] / max(1, $stats['total_rooms'])) * 100) }}%</span>
                                    <p class="text-[10px] text-slate-500 font-bold mt-1">{{ $stats['total_occupied'] }}/{{ $stats['total_rooms'] }} Rooms occupied</p>
                                </div>
                            </div>
                            
                            <!-- Card 2: Revenue -->
                            <div class="bg-gradient-to-br from-emerald-50 to-emerald-100/50 border border-emerald-100 p-5 rounded-2xl shadow-sm flex flex-col justify-between">
                                <span class="text-[10px] font-black text-emerald-800 uppercase tracking-wider">Stay Revenue in Selected Period</span>
                                <div class="mt-2">
                                    <span class="text-2xl font-black text-slate-900">Rs. {{ number_format($stats['revenue_today'], 0) }}</span>
                                    <p class="text-[10px] text-slate-500 font-bold mt-1">From hotel checkouts in range</p>
                                </div>
                            </div>

                            <!-- Card 3: Available -->
                            <div class="bg-gradient-to-br from-slate-50 to-slate-100/50 border border-slate-200 p-5 rounded-2xl shadow-sm flex flex-col justify-between">
                                <span class="text-[10px] font-black text-slate-700 uppercase tracking-wider">Available Rooms</span>
                                <div class="mt-2">
                                    <span class="text-2xl font-black text-slate-900">{{ $stats['total_rooms'] - $stats['total_occupied'] }} Rooms</span>
                                    <p class="text-[10px] text-slate-500 font-bold mt-1">Ready for check-in</p>
                                </div>
                            </div>
                        </div>

                        <!-- Today's Checked-out Stay Orders Archive -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">Stay Settlements (Selected Period)</h3>
                            
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="border-b border-slate-200 bg-slate-50/50">
                                            <th class="py-3 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Room</th>
                                            <th class="py-3 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Guest Name</th>
                                            <th class="py-3 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Checkout Date/Time</th>
                                            <th class="py-3 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Method</th>
                                            <th class="py-3 px-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Settlement Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse($this->todayCheckouts as $checkout)
                                            @php
                                                $guestName = $checkout->table->guest_name;
                                                $guestPhone = '';
                                                $guestId = '';
                                                if ($checkout->special_instructions && strpos($checkout->special_instructions, '(') !== false) {
                                                    $parts = explode('(', $checkout->special_instructions);
                                                    if (isset($parts[1])) {
                                                        $inner = rtrim($parts[1], ')');
                                                        $guestParts = explode(' - ', $inner);
                                                        $guestName = $guestParts[0];
                                                        $guestPhone = $guestParts[1] ?? '';
                                                        
                                                        if(isset($guestParts[2]) && strpos($guestParts[2], 'ID: ') === 0) {
                                                            $guestId = substr($guestParts[2], 4);
                                                        } elseif(isset($guestParts[1]) && strpos($guestParts[1], 'ID: ') === 0) {
                                                            $guestId = substr($guestParts[1], 4);
                                                            $guestPhone = '';
                                                        }
                                                    }
                                                }
                                            @endphp
                                            <tr class="hover:bg-slate-50/50 transition-colors">
                                                <td class="py-3.5 px-4">
                                                    <span class="text-xs font-bold text-slate-900">Room {{ $checkout->table->name }}</span>
                                                </td>
                                                <td class="py-3.5 px-4">
                                                    <div class="flex flex-col">
                                                        <span class="text-xs font-bold text-slate-700">{{ $guestName ?: 'Guest' }}</span>
                                                        @if($guestPhone)
                                                            <span class="text-[9px] text-slate-400 font-semibold">{{ $guestPhone }}</span>
                                                        @endif
                                                        @if($guestId)
                                                            <span class="text-[9px] text-indigo-400 font-semibold">ID: {{ $guestId }}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="py-3.5 px-4 text-slate-500 text-xs font-medium">
                                                    {{ $checkout->updated_at->format('M d, Y') }} at {{ $checkout->updated_at->format('h:i A') }}
                                                </td>
                                                <td class="py-3.5 px-4">
                                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-[9px] font-black uppercase tracking-widest">
                                                        {{ $checkout->invoice->payment_method ?? 'CASH' }}
                                                    </span>
                                                </td>
                                                <td class="py-3.5 px-4 text-right">
                                                    <span class="text-xs font-black text-slate-900">Rs. {{ number_format($checkout->invoice->grand_total ?? $checkout->total_amount, 0) }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="py-8 text-center text-xs text-slate-400 italic">
                                                    No stays completed or settled in the selected period.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Payment Modal -->
    @if($showPaymentModal)
    <div class="fixed inset-0 z-[100] bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl overflow-hidden w-full max-w-lg shadow-2xl flex flex-col">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-bold text-xl text-slate-800">Final Stay Settlement</h3>
                <button wire:click="closePaymentModal" class="p-2 hover:bg-slate-200 rounded-full text-slate-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="p-8">
                <div class="text-center mb-8">
                    <div class="text-sm text-slate-500 font-bold uppercase tracking-wider mb-2">Grand Total Payable</div>
                    <div class="text-5xl font-black text-indigo-600">Rs. {{ number_format($totalBill - $discount, 0) }}</div>
                    @if($discount > 0)
                        <p class="text-xs text-red-500 font-bold mt-2">Original: Rs. {{ number_format($totalBill, 0) }}</p>
                    @endif
                </div>

                <div class="mb-8 p-6 bg-slate-50 rounded-2xl border border-slate-200">
                    <label class="block text-sm font-black text-slate-700 mb-3 uppercase tracking-widest">Apply Discount (Rs.)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-slate-400 font-bold">Rs.</span>
                        </div>
                        <input type="number" wire:model.blur="discount" class="w-full pl-12 pr-4 py-4 bg-white border-2 border-indigo-100 rounded-xl text-xl font-black text-indigo-600 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all" placeholder="0.00">
                    </div>
                </div>

                <div class="space-y-4">
                    <h4 class="font-bold text-slate-700 mb-3">Select Payment Method</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="paymentMethod" value="cash" class="sr-only">
                            <div :class="$wire.paymentMethod === 'cash' ? 'border-indigo-500 bg-indigo-50 shadow-sm' : 'border-slate-200 hover:bg-slate-50'" class="p-4 rounded-xl border-2 transition-all flex items-center gap-3">
                                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm">💵</div>
                                <span class="font-bold text-slate-700">Cash</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="paymentMethod" value="card" class="sr-only">
                            <div :class="$wire.paymentMethod === 'card' ? 'border-indigo-500 bg-indigo-50 shadow-sm' : 'border-slate-200 hover:bg-slate-50'" class="p-4 rounded-xl border-2 transition-all flex items-center gap-3">
                                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm">💳</div>
                                <span class="font-bold text-slate-700">Card</span>
                            </div>
                        </label>
                        <label class="cursor-pointer col-span-2">
                            <input type="radio" wire:model.live="paymentMethod" value="online" class="sr-only">
                            <div :class="$wire.paymentMethod === 'online' ? 'border-indigo-500 bg-indigo-50 shadow-sm' : 'border-slate-200 hover:bg-slate-50'" class="p-4 rounded-xl border-2 transition-all flex items-center gap-3">
                                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm">📱</div>
                                <span class="font-bold text-slate-700">Online Wallet / Transfer</span>
                            </div>
                        </label>
                        <label class="cursor-pointer col-span-2">
                            <input type="radio" wire:model.live="paymentMethod" value="split" class="sr-only">
                            <div :class="$wire.paymentMethod === 'split' ? 'border-indigo-500 bg-indigo-50 shadow-sm' : 'border-slate-200 hover:bg-slate-50'" class="p-4 rounded-xl border-2 transition-all flex items-center gap-3">
                                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm">⚖️</div>
                                <span class="font-bold text-slate-700">Split Payment (Cash & Online)</span>
                            </div>
                        </label>
                    </div>

                    @if($paymentMethod === 'online')
                        <div class="mt-4 p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Select Provider</label>
                            <select wire:model="paymentProvider" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">-- Choose Provider --</option>
                                <option value="esewa">eSewa</option>
                                <option value="khalti">Khalti</option>
                                <option value="fonepay">Fonepay</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                    @endif

                    @if($paymentMethod === 'split')
                        <div class="mt-4 p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Cash Portion Paid (Rs.)</label>
                                <input type="number" wire:model.live.debounce.500ms="splitCashAmount" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 font-bold" placeholder="Enter Cash Paid">
                            </div>
                            <div class="flex justify-between items-center text-sm font-bold p-3 bg-indigo-50 border border-indigo-100 rounded-lg text-indigo-800">
                                <span>Remaining Online Portion:</span>
                                <span>Rs. {{ number_format(max(0, ($totalBill - $discount) - (float)$splitCashAmount), 0) }}</span>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Select Online Provider</label>
                                <select wire:model="paymentProvider" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">-- Choose Provider --</option>
                                    <option value="esewa">eSewa</option>
                                    <option value="khalti">Khalti</option>
                                    <option value="fonepay">Fonepay</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="p-6 border-t border-slate-100 bg-slate-50 flex gap-3 justify-end">
                <button wire:click="closePaymentModal" class="px-6 py-3 font-bold text-slate-600 hover:bg-slate-200 rounded-xl transition-all">
                    Cancel
                </button>
                <button wire:click="checkOut" wire:loading.attr="disabled" onclick="confirm('Process final checkout and print bill?') || event.stopImmediatePropagation()" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/20 active:scale-95 transition-all flex items-center gap-2 disabled:opacity-75 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="checkOut">Confirm & Print</span>
                    <span wire:loading wire:target="checkOut" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Processing...
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif

    @if($selectedRoom)
    @php $restaurant = current_restaurant(); @endphp
    <!-- Hidden Hotel Invoice Printable Area -->
    <div id="hotel-invoice" class="hidden">
        <div style="font-family: 'Courier New', Courier, monospace; width: 72mm; padding: 3mm 4mm; color: #000;">
            <div style="text-align: center; margin-bottom: 15px; border-bottom: 1px dashed #000; padding-bottom: 15px;">
                <h1 style="font-size: 16px; font-weight: 900; margin: 0 0 2px 0; text-transform: uppercase;">{{ $restaurant->name }}</h1>
                @if($restaurant->address)
                    <p style="font-size: 11px; margin: 2px 0;">{{ $restaurant->address }}</p>
                @endif
                @if($restaurant->phone)
                    <p style="font-size: 11px; margin: 2px 0;">Phone: {{ $restaurant->phone }}</p>
                @endif
                @if($restaurant->pan_number)
                    <p style="font-size: 11px; font-weight: bold; margin: 2px 0;">PAN: {{ $restaurant->pan_number }}</p>
                @endif
                <p style="font-size: 11px; font-weight: bold; margin: 5px 0 0 0;">--- HOTEL GUEST BILL ---</p>
            </div>

            <div style="margin-bottom: 15px; font-size: 11px; border-bottom: 1px dashed #000; padding-bottom: 10px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Guest Name:</span>
                    <span style="font-weight: bold;">{{ $selectedRoom->guest_name }}</span>
                </div>
                @if($selectedRoom->guest_phone)
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Phone:</span>
                    <span style="font-weight: bold;">{{ $selectedRoom->guest_phone }}</span>
                </div>
                @endif
                @if($selectedRoom->guest_id_number)
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Citizenship/ID:</span>
                    <span style="font-weight: bold;">{{ $selectedRoom->guest_id_number }}</span>
                </div>
                @endif
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Room:</span>
                    <span style="font-weight: bold;">Room {{ $selectedRoom->name }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Check In:</span>
                    <span>{{ \App\Helpers\DateHelper::format($selectedRoom->check_in_at) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Check Out:</span>
                    <span>{{ \App\Helpers\DateHelper::format(now()) }}</span>
                </div>
            </div>

            <h3 style="font-size: 11px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; border-bottom: 1px dashed #000; padding-bottom: 3px;">Accommodation</h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 15px;">
                <thead>
                    <tr style="border-bottom: 1px dashed #000;">
                        <th style="text-align: left; padding: 3px 0;">Description</th>
                        <th style="text-align: center; padding: 3px 0;">Days</th>
                        <th style="text-align: right; padding: 3px 0;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 4px 0;">Stay Room {{ $selectedRoom->name }}</td>
                        <td style="text-align: center; padding: 4px 0;">{{ max(1, \Carbon\Carbon::parse($selectedRoom->check_in_at)->diffInDays(now())) + $manualDays }}</td>
                        <td style="text-align: right; padding: 4px 0;">{{ number_format((max(1, \Carbon\Carbon::parse($selectedRoom->check_in_at)->diffInDays(now())) + $manualDays) * $selectedRoom->room_rate, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            @if(count($activeOrders) > 0)
            <h3 style="font-size: 11px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; border-bottom: 1px dashed #000; padding-bottom: 3px;">Hospitality Charges</h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 15px;">
                <thead>
                    <tr style="border-bottom: 1px dashed #000;">
                        <th style="text-align: left; padding: 3px 0;">Order No</th>
                        <th style="text-align: left; padding: 3px 0;">Date</th>
                        <th style="text-align: right; padding: 3px 0;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activeOrders as $order)
                    <tr>
                        <td style="padding: 4px 0;">Order #{{ $order->order_number }}</td>
                        <td style="padding: 4px 0;">{{ \App\Helpers\DateHelper::format($order->created_at) }}</td>
                        <td style="text-align: right; padding: 4px 0;">{{ number_format($order->total_amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            <div style="border-top: 1px dashed #000; padding-top: 10px; font-size: 11px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span>Room Charges:</span>
                    <span>Rs. {{ number_format((max(1, \Carbon\Carbon::parse($selectedRoom->check_in_at)->diffInDays(now())) + $manualDays) * $selectedRoom->room_rate, 2) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span>Hospitality Total:</span>
                    <span>Rs. {{ number_format(collect($activeOrders)->sum('total_amount'), 2) }}</span>
                </div>
                @if($discount > 0)
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px; color: #ff0000; font-weight: bold;">
                    <span>Discount:</span>
                    <span>- Rs. {{ number_format($discount, 2) }}</span>
                </div>
                @endif
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px; font-weight: bold; font-size: 13px; border-top: 1px dashed #000; padding-top: 5px; margin-top: 5px;">
                    <span>Grand Total:</span>
                    <span>Rs. {{ number_format(max(0, $totalBill - $discount), 2) }}</span>
                </div>
            </div>

            <div style="text-align: center; margin-top: 25px; padding-top: 15px; border-top: 1px dashed #000; font-size: 11px;">
                <p style="font-weight: bold;">THANK YOU FOR VISITING!</p>
                <p style="margin-top: 3px;">Have a safe journey!</p>
                <p style="margin-top: 6px;">Please collect orginal bill from the counter :) thank you</p>
            </div>
        </div>
    </div>
    @endif

    <script>
        function printHotelInvoice() {
            var printContents = document.getElementById('hotel-invoice').innerHTML;
            if (!printContents) {
                alert('No active stay bill to print.');
                return;
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
            
            // Write contents into the iframe
            var win = iframe.contentWindow;
            win.document.open();
            win.document.write(`
                <html>
                <head>
                    <title>Hotel Bill</title>
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
                    </style>
                </head>
                <body>
                    ` + printContents + `
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

        function printHotelSummary() {
            var restaurantName = "{{ $restaurant->name ?? 'DrestroPOS' }}";
            var dateText = "{{ $this->startDate->format('M d, Y') }} to {{ $this->endDate->format('M d, Y') }}";
            
            var occupancy = "{{ round(($stats['total_occupied'] / max(1, $stats['total_rooms'])) * 100) }}%";
            var occupiedDetails = "{{ $stats['total_occupied'] }}/{{ $stats['total_rooms'] }} Rooms occupied";
            var availableRooms = "{{ $stats['total_rooms'] - $stats['total_occupied'] }} Rooms";
            var revenueToday = "Rs. {{ number_format($stats['revenue_today'], 0) }}";
            
            // Extract checkout list table HTML safely
            var archiveHTML = '';
            var tableElement = document.querySelector('#hotel-sales-report-content table');
            if (tableElement) {
                archiveHTML = tableElement.outerHTML;
            } else {
                archiveHTML = '<div class="no-orders">No hotel checkouts completed in this period.</div>';
            }
            
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
                    <title>Hotel Reception Daily Summary</title>
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
                        
                        /* Checkout Archive */
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
                        <p>Hotel Reception Daily Report</p>
                        <div class="meta">
                            REPORT DATE: ${dateText}
                        </div>
                    </div>

                    <!-- Summary Table -->
                    <table class="stats-table">
                        <thead>
                            <tr>
                                <th>Room Occupancy</th>
                                <th>Available Rooms</th>
                                <th>Total Stay Revenue (Selected Period)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    ${occupancy}
                                    <span class="sub">${occupiedDetails}</span>
                                </td>
                                <td>
                                    ${availableRooms}
                                    <span class="sub">Ready for check-in</span>
                                </td>
                                <td>
                                    ${revenueToday}
                                    <span class="sub">Stay Settlements</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Archive Section -->
                    <div class="archive-section">
                        <h3>Stay Checkouts & Settlements (Selected Period)</h3>
                        ${archiveHTML}
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

        function downloadA4PDF() {
            alert('To save this hotel stay bill as a PDF:\n\n1. The Print Dialog will now open.\n2. Change your Destination to "Save as PDF".\n3. Click Save!');
            window.print();
        }
    </script>
</div>
