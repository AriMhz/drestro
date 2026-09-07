<div class="space-y-6" id="pdf-content">
<style>
    @media print {
        @page { margin: 1cm; size: A4 portrait; }
        body * { visibility: hidden !important; }
        #pdf-content, #pdf-content * { visibility: visible !important; }
        #pdf-content { position: absolute !important; left: 0 !important; top: 0 !important; width: 100% !important; margin: 0 !important; padding: 0 !important; }
        #pdf-content * { background: transparent !important; color: #0f172a !important; box-shadow: none !important; }
        #pdf-content { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif !important; font-size: 10pt !important; line-height: 1.3 !important; }
        #pdf-content p { margin: 0 0 4px 0 !important; }
        #pdf-content h1 { font-size: 18pt !important; font-weight: 800 !important; border-bottom: 2px solid #cbd5e1 !important; padding-bottom: 8px !important; margin: 0 0 15px 0 !important; display: block !important; text-transform: uppercase !important; }
        #pdf-content h3 { font-size: 11pt !important; font-weight: bold !important; margin: 15px 0 8px 0 !important; display: block !important; text-transform: uppercase !important; color: #475569 !important; border-bottom: 1px solid #e2e8f0 !important; padding-bottom: 4px !important; }
        
        /* Stats cards side-by-side columns */
        #pdf-content .grid { display: flex !important; flex-direction: row !important; gap: 15px !important; margin: 0 0 15px 0 !important; padding: 0 !important; }
        #pdf-content .grid > div, #pdf-content .grid > a { flex: 1 !important; display: block !important; border: 1px solid #e2e8f0 !important; border-radius: 8px !important; padding: 10px 15px !important; background: #f8fafc !important; }
        
        #pdf-content .text-3xl { font-size: 14pt !important; font-weight: 800 !important; margin: 2px 0 0 0 !important; }
        #pdf-content .text-sm.font-medium { font-size: 8.5pt !important; text-transform: uppercase !important; color: #64748b !important; font-weight: bold !important; margin: 0 !important; }
        #pdf-content .text-xs { font-size: 7.5pt !important; color: #94a3b8 !important; margin: 0 !important; }
        
        #pdf-content .print-hidden, #pdf-content svg, #pdf-content .absolute, #pdf-content .shrink-0 { display: none !important; }
        
        /* Table styles */
        #pdf-content table { width: 100% !important; border-collapse: collapse !important; margin: 10px 0 !important; }
        #pdf-content th { background: #f8fafc !important; border-bottom: 2px solid #cbd5e1 !important; padding: 6px 10px !important; font-size: 8pt !important; font-weight: 900 !important; color: #64748b !important; text-transform: uppercase !important; text-align: left !important; }
        #pdf-content td { border-bottom: 1px solid #f1f5f9 !important; padding: 6px 10px !important; font-size: 8.5pt !important; color: #334155 !important; }
        #pdf-content td.font-bold { font-weight: bold !important; color: #0f172a !important; }
        #pdf-content td.text-emerald-600 { color: #059669 !important; font-weight: bold !important; }
    }
</style>
    <!-- 15 Days Backup Reminder Popup -->
    @if($showBackupReminder)
        <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity">
            <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all duration-300 scale-100 dark:border-slate-800 dark:bg-slate-900">
                <div class="relative p-6 sm:p-8 flex flex-col items-center text-center">
                    
                    <!-- Pulsing Warning/Backup Icon -->
                    <div class="w-16 h-16 rounded-full bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-500 animate-bounce mb-4 dark:bg-amber-900/20 dark:border-amber-800/50">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>

                    <h3 class="text-xl font-extrabold text-slate-800 tracking-tight dark:text-slate-200">💾 Database Backup Reminder</h3>
                    <p class="text-sm text-slate-500 mt-2 leading-relaxed dark:text-slate-400">
                        It has been <strong>15 days</strong> since your last database backup check. To safeguard your menu, hotel bookings, and restaurant invoices against data loss, please backup your database now!
                    </p>

                    <!-- Alert message container -->
                    <div class="w-full mt-5">
                        @if (session()->has('success'))
                            <div class="p-3 bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs rounded-xl font-bold text-left flex items-start gap-2 dark:text-emerald-300 dark:bg-emerald-900/20 dark:border-emerald-800/50">
                                <span>🎉</span> {{ session('success') }}
                            </div>
                        @endif
                        @if (session()->has('error'))
                            <div class="p-3 bg-rose-50 border border-rose-100 text-rose-800 text-xs rounded-xl font-bold text-left flex items-start gap-2 dark:border-rose-800/50 dark:text-rose-300 dark:bg-rose-900/20">
                                <span>⚠️</span> {{ session('error') }}
                            </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full mt-6">
                        <button type="button" wire:click="backupToUsbFromDashboard" wire:loading.attr="disabled"
                            class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                            <span wire:loading.remove wire:target="backupToUsbFromDashboard" class="flex items-center justify-center gap-2">
                                💾 Scan & Backup to USB
                            </span>
                            <span wire:loading wire:target="backupToUsbFromDashboard" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Backing up...
                            </span>
                        </button>
                        <button type="button" wire:click="dismissBackupReminder"
                            class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl active:scale-[0.98] transition-all dark:text-slate-300 dark:bg-slate-800">
                            Dismiss Reminder
                        </button>
                    </div>

                    <p class="text-[10px] text-slate-400 mt-4 font-semibold">⚠️ Connect a USB Pendrive before clicking "Backup to USB"</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Basic Tier Notice -->
    @if(($activeLicense['plan'] ?? '') === 'Basic (Activation Required)')
        <div
            class="bg-indigo-600 rounded-2xl p-4 sm:p-6 text-white shadow-lg shadow-indigo-200 relative overflow-hidden group">
            <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold">Welcome to Drestro Basic Edition! 🚀</h3>
                    <p class="text-indigo-100 text-sm mt-1">You are using the free core version. Upgrade to **Standard
                        Edition** to unlock Inventory, Reports, and Unlimited Items.</p>
                </div>
                <a href="/admin/license"
                    class="inline-flex items-center px-5 py-2.5 bg-white text-indigo-600 rounded-xl font-bold text-sm hover:bg-indigo-50 transition-colors shadow-sm dark:bg-slate-900">
                    Upgrade Now
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6">
                        </path>
                    </svg>
                </a>
            </div>
            <!-- Decorative blobs -->
            <div
                class="absolute -right-8 -top-8 w-32 h-32 bg-indigo-500 rounded-full blur-3xl opacity-50 group-hover:scale-125 transition-transform duration-1000">
            </div>
            <div
                class="absolute -left-8 -bottom-8 w-32 h-32 bg-indigo-400 rounded-full blur-3xl opacity-30 group-hover:scale-125 transition-transform duration-1000">
            </div>
        </div>
    @endif

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1 flex-wrap">
                <h1 class="text-2xl font-black text-slate-800 dark:text-slate-200 tracking-tight">{{ current_restaurant()->name }}</h1>
                @if(current_restaurant()->address)
                <div class="flex items-center gap-1.5 px-3 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-350 text-xs font-bold rounded-xl border border-slate-200/50 dark:border-slate-700/50 shadow-sm">
                    <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span>{{ current_restaurant()->address }}</span>
                </div>
                @endif
            </div>
            <p class="text-sm text-slate-500 print-hidden dark:text-slate-400">Track your restaurant's real-time performance.</p>
            
            <!-- Subtitle including selected date range for printing only -->
            <div class="hidden print:block text-xs font-bold text-slate-600 mt-1 uppercase tracking-wider dark:text-slate-400">
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
                <span>Selected Period: {{ $period === 'today' ? 'Today' : ($period === 'week' ? 'This Week' : ($period === 'month' ? 'This Month' : 'Custom Range')) }} ({{ $startFormatted }} to {{ $endFormatted }})</span>
            </div>
        </div>
        
        <div class="flex flex-col md:flex-row flex-wrap items-stretch gap-4 bg-white rounded-2xl border border-slate-200 p-4 shadow-sm print-hidden w-full lg:w-auto mt-2 dark:bg-slate-900 dark:border-slate-700">
            <!-- Section 2: Custom Date Range Picker -->
            <div wire:ignore wire:key="calendar-picker-dashboard" class="flex flex-col gap-1.5"
                 x-ref="pickerWrap"
                 x-data="{
                    pickerOpen: false,
                    pickingField: 'start', // 'start' or 'end'
                    bsMap: @js((new \App\Helpers\NepaliCalendar())->_bs ?? []),
                    nepMonthNames: ['Baisakh', 'Jestha', 'Ashadh', 'Shrawan', 'Bhadra', 'Ashwin', 'Kartik', 'Mangsir', 'Poush', 'Magh', 'Falgun', 'Chaitra'],
                    engMonthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                    startDate: '{{ $customStartDate ?? now()->subDays(6)->format('Y-m-d') }}',
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
                            <span class="text-emerald-600 font-black uppercase tracking-wider dark:text-emerald-400" x-text="pickingField === 'start' ? 'Selecting From Date' : 'Selecting To Date'"></span>
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
                    <button onclick="window.print()" class="px-4 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-all flex items-center gap-2 border border-slate-200 shadow-sm dark:text-slate-300 dark:border-slate-700 dark:bg-slate-800" title="Print Dashboard">
                        <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Print
                    </button>
                    <button onclick="window.print()" class="px-4 py-1.5 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 hover:bg-indigo-100 transition-all flex items-center gap-2 shadow-sm dark:border-indigo-800/50 dark:text-indigo-400 dark:bg-indigo-900/20" title="Save Dashboard as PDF">
                        <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Save PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Orders -->
        <div
            class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between group hover:shadow-md transition-shadow dark:border-slate-800 dark:bg-slate-900">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Orders</p>
                <p class="text-3xl font-black text-slate-800 mt-1 dark:text-slate-200">{{ number_format($this->totalOrders) }}</p>
                <p class="text-xs text-slate-400 mt-2 capitalize">
                    {{ $period === 'today' ? "Today's orders" : ($period === 'week' ? 'This week' : 'This month') }}</p>
            </div>
            <div
                class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform dark:text-blue-400 dark:bg-blue-900/20">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </div>
        </div>

        <!-- Revenue -->
        <div
            class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between group hover:shadow-md transition-shadow dark:border-slate-800 dark:bg-slate-900">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Revenue</p>
                <p class="text-3xl font-black text-emerald-600 mt-1 dark:text-emerald-400">Rs. {{ number_format($this->totalRevenue, 0) }}</p>
                <p class="text-xs text-slate-400 mt-2">Completed orders only</p>
            </div>
            <div
                class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform dark:bg-emerald-900/20 dark:text-emerald-400">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                    </path>
                </svg>
            </div>
        </div>

        <!-- Active Tables -->
        <a href="/staff/waiter"
            class="block bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between group hover:shadow-md hover:border-amber-200 transition-all cursor-pointer print-hidden dark:border-slate-800 dark:bg-slate-900">
            <div>
                <p class="text-sm font-medium text-slate-500 group-hover:text-amber-600 transition-colors dark:text-slate-400">Active Tables
                    (Click to Manage)</p>
                <p class="text-3xl font-black text-slate-800 mt-1 dark:text-slate-200">{{ $this->activeTables['active'] }} <span
                        class="text-lg font-medium text-slate-400">/ {{ $this->activeTables['total'] }}</span></p>
                <p class="text-xs text-slate-400 mt-2">Currently occupied</p>
            </div>
            <div
                class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 group-hover:bg-amber-100 group-hover:scale-110 transition-all dark:text-amber-400 dark:bg-amber-900/20">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                    </path>
                </svg>
            </div>
        </a>

        <!-- Pending Orders -->
        <div
            class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between group hover:shadow-md transition-shadow print-hidden dark:border-slate-800 dark:bg-slate-900">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Pending Orders</p>
                <p
                    class="text-3xl font-black {{ $this->pendingOrders > 0 ? 'text-orange-500' : 'text-slate-800' }} mt-1 dark:text-slate-200">
                    {{ $this->pendingOrders }}</p>
                <p class="text-xs text-slate-400 mt-2">Awaiting kitchen / bar</p>
            </div>
            <div
                class="w-14 h-14 rounded-2xl bg-orange-50 flex items-center justify-center text-orange-500 group-hover:scale-110 transition-transform {{ $this->pendingOrders > 0 ? 'animate-pulse' : '' }} dark:bg-orange-900/20">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    <!-- Recent Orders Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mt-6 dark:border-slate-800 dark:bg-slate-900">
        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">Recent Orders</h3>
            <a href="/staff/cashier"
                class="text-sm font-semibold text-emerald-600 hover:text-emerald-700 transition-colors dark:text-emerald-400">View All →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-900/50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider dark:text-slate-400">Order ID</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider dark:text-slate-400">Table</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider dark:text-slate-400">Amount</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider dark:text-slate-400">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider dark:text-slate-400">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($this->recentOrders as $order)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-800 dark:text-slate-200">#{{ $order->order_number }}</td>
                            <td class="px-6 py-4 text-slate-600 font-medium dark:text-slate-400">
                                {{ $order->table ? ($order->table->type === 'room' ? 'Room ' : 'Table ') . $order->table->name : 'Takeaway' }}</td>
                            <td class="px-6 py-4 font-bold text-emerald-600 dark:text-emerald-400">Rs.
                                {{ number_format($order->total_amount, 0) }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-orange-100 text-orange-800',
                                        'preparing' => 'bg-amber-100 text-amber-800',
                                        'ready' => 'bg-blue-100 text-blue-800',
                                        'completed' => 'bg-emerald-100 text-emerald-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$order->status] ?? 'bg-slate-100 text-slate-600' }} dark:text-slate-400 dark:bg-slate-800">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 dark:text-slate-400">{{ $order->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                                <p class="font-medium">No orders yet</p>
                                <p class="text-xs mt-1">Orders will appear here when customers place them.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>