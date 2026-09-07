<div class="space-y-6" id="pdf-content">
    <style>
        @media print {
            @page { 
                margin: 0.4cm !important; 
                size: A4 portrait; 
            }
            
            /* Hide everything by default on the entire page */
            body * {
                visibility: hidden !important;
            }
            
            /* Only show our pdf content */
            #pdf-content, #pdf-content * {
                visibility: visible !important;
            }
            
            /* Position it exactly at the top left of the page */
            #pdf-content {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                font-family: Arial, sans-serif !important;
                font-size: 8.5pt !important;
                line-height: 1.2 !important;
                color: #000 !important;
            }

            /* Basic resets inside pdf-content */
            #pdf-content * {
                background: transparent !important;
                color: black !important;
                box-shadow: none !important;
                text-shadow: none !important;
            }

            /* Remove all borders from containers, except defined ones */
            #pdf-content div {
                border: none !important;
                border-radius: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
            }

            /* Typography & Titles */
            #pdf-content h1 {
                font-size: 14pt !important;
                font-weight: bold !important;
                border-bottom: 1px solid #000 !important;
                padding-bottom: 2px !important;
                margin: 0 0 4px 0 !important;
                display: block !important;
                color: #000 !important;
            }

            #pdf-content h3 {
                font-size: 9pt !important;
                font-weight: bold !important;
                margin: 0 0 4px 0 !important;
                color: #000 !important;
                display: block !important;
            }

            #pdf-content p {
                margin: 0 0 4px 0 !important;
            }
            
            /* Compact Header metadata */
            #pdf-content > div.flex.flex-col {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
                align-items: center !important;
                border-bottom: 1px solid #e2e8f0 !important;
                padding-bottom: 4px !important;
                margin-bottom: 6px !important;
            }
            #pdf-content > div.flex.flex-col p {
                font-size: 7.5pt !important;
                color: #475569 !important;
                margin: 0 !important;
            }

            /* 1. Stats Row: Single Inline-flex Row layout */
            #pdf-content > div.grid-cols-1.md\:grid-cols-3 {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
                align-items: center !important;
                background: #f8fafc !important;
                border: 1px solid #cbd5e1 !important;
                padding: 4px 8px !important;
                margin-bottom: 6px !important;
                page-break-inside: avoid !important;
            }
            #pdf-content > div.grid-cols-1.md\:grid-cols-3 > div {
                display: flex !important;
                flex-direction: row !important;
                gap: 6px !important;
                align-items: center !important;
                border: none !important;
            }
            #pdf-content > div.grid-cols-1.md\:grid-cols-3 > div h3 {
                font-size: 8pt !important;
                text-transform: uppercase !important;
                margin: 0 !important;
                color: #475569 !important;
                display: inline !important;
            }
            #pdf-content > div.grid-cols-1.md\:grid-cols-3 > div .text-4xl {
                font-size: 9.5pt !important;
                font-weight: 800 !important;
                margin: 0 !important;
                color: #0f172a !important;
                display: inline !important;
            }

            /* 2. Side-by-Side Grid Row (Top Selling & Payment Breakdown) */
            #pdf-content > div.grid-cols-1.lg\:grid-cols-2 {
                display: flex !important;
                flex-direction: row !important;
                gap: 10px !important;
                margin-bottom: 6px !important;
                page-break-inside: avoid !important;
            }
            #pdf-content > div.grid-cols-1.lg\:grid-cols-2 > div {
                flex: 1 !important;
                min-width: 0 !important;
                height: auto !important;
                border: 1px solid #cbd5e1 !important;
                padding: 6px !important;
                display: flex !important;
                flex-direction: column !important;
            }
            #pdf-content > div.grid-cols-1.lg\:grid-cols-2 > div > div:first-of-type {
                border-bottom: 1px solid #e2e8f0 !important;
                padding-bottom: 3px !important;
                margin-bottom: 6px !important;
            }
            #pdf-content > div.grid-cols-1.lg\:grid-cols-2 > div > div:first-of-type h3 {
                font-size: 9pt !important;
                margin: 0 !important;
            }

            /* Top Selling rows formatting */
            #pdf-content > div.grid-cols-1.lg\:grid-cols-2 .space-y-4 {
                display: flex !important;
                flex-direction: column !important;
                gap: 4px !important;
            }
            #pdf-content > div.grid-cols-1.lg\:grid-cols-2 .space-y-4 > div {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
                align-items: center !important;
                padding: 2px 0 !important;
                border-bottom: 1px dotted #e2e8f0 !important;
            }
            #pdf-content > div.grid-cols-1.lg\:grid-cols-2 .space-y-4 > div:last-child {
                border-bottom: none !important;
            }
            #pdf-content > div.grid-cols-1.lg\:grid-cols-2 .space-y-4 > div .w-8.h-8 {
                width: auto !important;
                height: auto !important;
                background: transparent !important;
                color: #000 !important;
                font-size: 8pt !important;
                font-weight: bold !important;
                margin-right: 4px !important;
            }
            #pdf-content > div.grid-cols-1.lg\:grid-cols-2 .space-y-4 > div h4 {
                font-size: 8pt !important;
                margin: 0 !important;
                display: inline-block !important;
            }
            #pdf-content > div.grid-cols-1.lg\:grid-cols-2 .space-y-4 > div .text-sm.text-slate-500 {
                font-size: 7pt !important;
                color: #475569 !important;
                margin-top: 1px !important;
            }
            #pdf-content > div.grid-cols-1.lg\:grid-cols-2 .space-y-4 > div .font-bold.text-emerald-600 {
                font-size: 8pt !important;
                color: #000 !important;
                font-weight: bold !important;
            }

            /* Payment Breakdown rows formatting */
            #pdf-content > div.grid-cols-1.lg\:grid-cols-2 .space-y-4 > div h4 {
                font-size: 8pt !important;
                font-weight: bold !important;
                text-transform: uppercase !important;
            }
            #pdf-content > div.grid-cols-1.lg\:grid-cols-2 .space-y-4 > div .text-xs.font-bold {
                font-size: 7pt !important;
                color: #0284c7 !important;
            }
            #pdf-content > div.grid-cols-1.lg\:grid-cols-2 .space-y-4 > div .font-black {
                font-size: 8.5pt !important;
                font-weight: bold !important;
            }

            /* 3. Staff Performance Table */
            #pdf-content > div.overflow-hidden {
                border: 1px solid #cbd5e1 !important;
                padding: 6px !important;
                margin-top: 0 !important;
                page-break-inside: avoid !important;
            }
            #pdf-content > div.overflow-hidden > div:first-of-type {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                border-bottom: 1px solid #e2e8f0 !important;
                padding-bottom: 3px !important;
                margin-bottom: 6px !important;
            }
            #pdf-content > div.overflow-hidden > div:first-of-type h3 {
                font-size: 9pt !important;
                margin: 0 !important;
            }
            #pdf-content > div.overflow-hidden > div:first-of-type span {
                font-size: 7pt !important;
                color: #64748b !important;
            }
            #pdf-content table {
                width: 100% !important;
                border-collapse: collapse !important;
                margin: 0 !important;
            }
            #pdf-content th, #pdf-content td {
                border: 1px solid #cbd5e1 !important;
                padding: 4px 6px !important;
                font-size: 8pt !important;
                text-align: left !important;
            }
            #pdf-content th {
                font-weight: bold !important;
                background: #f8fafc !important;
                text-transform: uppercase !important;
                font-size: 7.5pt !important;
                color: #475569 !important;
            }
            #pdf-content td {
                color: #0f172a !important;
            }

            /* Hide interactive/fluff elements */
            #pdf-content .print-hidden, 
            #pdf-content .print\:hidden,
            #pdf-content svg, 
            #pdf-content .absolute { 
                display: none !important; 
            }
        }
    </style>
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 bg-slate-50 p-6 rounded-3xl border border-slate-200/60 shadow-sm print:bg-transparent print:border-none print:p-0 print:shadow-none dark:bg-slate-900 dark:border-slate-700/60">
        <div class="hidden lg:block">
            <h1 class="text-2xl font-black text-slate-800 tracking-tight dark:text-slate-200">Reports & Analytics</h1>
            <p class="text-xs font-bold text-slate-500 mt-1 print:hidden dark:text-slate-400">Track your restaurant's performance across any period.</p>
            <p class="text-xs text-slate-400 font-black mt-2 bg-slate-100 px-3 py-1.5 rounded-xl border border-slate-200/60 inline-flex items-center gap-2 select-none print:bg-transparent print:border-none print:p-0 print:m-0 dark:border-slate-700/60 dark:bg-slate-800">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse print:hidden"></span>
                <span>Report Generated: {{ \App\Helpers\DateHelper::format(now()) }}</span>
                <span class="text-slate-300 print:hidden">|</span>
                <span class="text-slate-600 dark:text-slate-400">
                    @if($dateRange === 'custom')
                        Range: {{ \App\Helpers\DateHelper::format(\Carbon\Carbon::parse($customStartDate)) }} to {{ \App\Helpers\DateHelper::format(\Carbon\Carbon::parse($customEndDate)) }}
                    @else
                        Range: {{ ucfirst($dateRange) }}
                    @endif
                </span>
            </p>
        </div>
        
        <div class="flex flex-col md:flex-row flex-wrap items-stretch gap-4 bg-white rounded-2xl border border-slate-200 p-4 shadow-sm print-hidden w-full xl:w-auto mt-2 xl:mt-0 dark:bg-slate-900 dark:border-slate-700">
            <!-- Section 2: Custom Date Range Picker -->
            <div wire:ignore wire:key="calendar-picker-reports" class="flex flex-col gap-1.5 flex-1 min-w-[280px]"
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

            <div class="w-[1px] bg-slate-200 hidden sm:block self-stretch my-1 dark:bg-slate-800"></div>
            
            <!-- Section 3: Actions -->
            <div class="flex flex-col gap-1.5 justify-between">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Actions</span>
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <button onclick="window.print()" class="px-4 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-all flex items-center gap-2 border border-slate-200 shadow-sm dark:text-slate-300 dark:border-slate-700 dark:bg-slate-800" title="Print Reports">
                        <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Print
                    </button>
                    <button onclick="downloadPDF('Reports.pdf')" class="px-4 py-1.5 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 hover:bg-indigo-100 transition-all flex items-center gap-2 shadow-sm dark:border-indigo-800/50 dark:text-indigo-400 dark:bg-indigo-900/20" title="Save Reports as PDF">
                        <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Save PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm relative overflow-hidden group dark:bg-slate-900 dark:border-slate-700">
            <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <svg class="w-16 h-16 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2 dark:text-slate-400">Total Revenue</h3>
            <div class="text-4xl font-black text-slate-800 dark:text-slate-200">Rs. {{ number_format($stats['revenue'], 0) }}</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm relative overflow-hidden group dark:bg-slate-900 dark:border-slate-700">
            <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <svg class="w-16 h-16 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2 dark:text-slate-400">Total Orders</h3>
            <div class="text-4xl font-black text-slate-800 dark:text-slate-200">{{ $stats['orders'] }}</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm relative overflow-hidden group dark:bg-slate-900 dark:border-slate-700">
            <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <svg class="w-16 h-16 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2 dark:text-slate-400">Average Order Value</h3>
            <div class="text-4xl font-black text-slate-800 dark:text-slate-200">Rs. {{ number_format($stats['aov'], 0) }}</div>
        </div>
    </div>

    <!-- Charts & Tables Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Top Selling Items -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col h-[400px] dark:bg-slate-900 dark:border-slate-700">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-bold text-lg text-slate-800 dark:text-slate-200">Top Selling Items</h3>
            </div>
            <div class="flex-1 overflow-y-auto p-4">
                <div class="space-y-4">
                    @forelse($topItems as $index => $item)
                        <div class="flex items-center gap-4">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm {{ $index == 0 ? 'bg-amber-100 text-amber-700' : ($index == 1 ? 'bg-slate-200 text-slate-600' : ($index == 2 ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-500')) }} dark:text-amber-400 dark:text-slate-400 dark:text-orange-400 dark:bg-slate-800">
                                #{{ $index + 1 }}
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-slate-800 dark:text-slate-200">{{ $item->menuItem->name }}</h4>
                                <div class="text-sm text-slate-500 dark:text-slate-400">{{ $item->total_quantity }} sold</div>
                            </div>
                            <div class="font-bold text-emerald-600 dark:text-emerald-400">
                                Rs. {{ number_format($item->total_revenue, 0) }}
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-full text-slate-400 py-10">
                            <svg class="w-12 h-12 mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            <p>No sales data yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Payment Breakdown -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col h-[400px] dark:bg-slate-900 dark:border-slate-700">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-bold text-lg text-slate-800 dark:text-slate-200">Revenue by Payment Method</h3>
            </div>
            <div class="flex-1 overflow-y-auto p-4">
                <div class="space-y-4">
                    @forelse($paymentMethods as $method)
                        <div class="p-4 rounded-xl border border-slate-100 bg-slate-50 flex items-center justify-between dark:border-slate-800 dark:bg-slate-900">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm text-xl border border-slate-200 print:hidden dark:bg-slate-900 dark:border-slate-700">
                                    {{ $method->payment_method === 'cash' ? '💵' : ($method->payment_method === 'card' ? '💳' : ($method->payment_method === 'split' ? '⚖️' : '📱')) }}
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 uppercase tracking-wide text-sm dark:text-slate-200">{{ $method->payment_method === 'split' ? 'Split Payment' : $method->payment_method }}</h4>
                                    @if($method->payment_provider)
                                        <div class="text-xs font-bold text-blue-600 dark:text-blue-400">{{ ucfirst($method->payment_provider) }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="font-black text-slate-800 text-xl dark:text-slate-200">
                                Rs. {{ number_format($method->total, 0) }}
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-full text-slate-400 py-10">
                            <p>No payment data yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- Staff Performance Row -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden dark:bg-slate-900 dark:border-slate-700">
        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">Staff Performance</h3>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Orders Placed by Waiters</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-900/50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider dark:text-slate-400">Staff Member</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider dark:text-slate-400">Role</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center dark:text-slate-400">Orders Handled</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right dark:text-slate-400">Total Sales</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($this->staffPerformance as $perf)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xs uppercase print:hidden dark:text-emerald-400">
                                        {{ substr($perf->user->name ?? 'U', 0, 1) }}
                                    </div>
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $perf->user->name ?? 'Unknown User' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-lg bg-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wide dark:text-slate-400 dark:bg-slate-800">
                                    {{ $perf->user->role ?? 'Staff' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-slate-700 dark:text-slate-300">
                                {{ $perf->total_orders }}
                            </td>
                            <td class="px-6 py-4 text-right font-black text-emerald-600 dark:text-emerald-400">
                                Rs. {{ number_format($perf->total_revenue, 0) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-slate-400">
                                No staff activity recorded for this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Nepal E-Billing Invoices Log -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mt-6 dark:bg-slate-900 dark:border-slate-700">
        <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 dark:border-slate-800">
            <div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">E-Billing VAT Invoices Log</h3>
                <p class="text-xs text-slate-500 font-medium mt-1 dark:text-slate-400">Track and manually sync sales invoices to Inland Revenue Department (IRD) via Nepal E-Billing.</p>
            </div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nepal E-Billing API Logs</span>
        </div>

        <div class="px-6 py-2">
            @if (session()->has('ebilling_success'))
                <div class="my-3 p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs rounded-xl font-bold dark:bg-emerald-950/20 dark:border-emerald-800 dark:text-emerald-400">
                    🎉 {{ session('ebilling_success') }}
                </div>
            @endif
            @if (session()->has('ebilling_error'))
                <div class="my-3 p-4 bg-rose-50 border border-rose-100 text-rose-800 text-xs rounded-xl font-bold dark:bg-rose-950/20 dark:border-rose-800 dark:text-rose-400">
                    ⚠️ {{ session('ebilling_error') }}
                </div>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-900/50">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider dark:text-slate-400">Invoice Number</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider dark:text-slate-400">Date</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right dark:text-slate-400">Total Amount</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center dark:text-slate-400">Sync Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider dark:text-slate-400">Remote Ref / Error</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center dark:text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($this->ebillingInvoices as $inv)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-800 dark:text-slate-200">
                                {{ $inv->invoice_number }}
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                                {{ \App\Helpers\DateHelper::format($inv->created_at) }}
                            </td>
                            <td class="px-6 py-4 text-right font-black text-emerald-600 dark:text-emerald-400">
                                Rs. {{ number_format($inv->grand_total, 2) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($inv->nepal_ebilling_synced)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/20 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Synced
                                    </span>
                                @elseif($inv->nepal_ebilling_error)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/20 dark:text-rose-400" title="{{ $inv->nepal_ebilling_error }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                        Failed
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/20 dark:text-amber-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 max-w-xs truncate text-xs text-slate-600 dark:text-slate-400">
                                @if($inv->nepal_ebilling_synced)
                                    <span class="font-mono text-slate-500 dark:text-slate-400">{{ $inv->nepal_ebilling_invoice_id }}</span>
                                @else
                                    <span class="text-rose-600 dark:text-rose-400 font-medium" title="{{ $inv->nepal_ebilling_error }}">{{ $inv->nepal_ebilling_error ?: 'No attempt yet' }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button type="button" wire:click="syncSingleInvoice({{ $inv->id }})" wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 transition-all shadow-sm dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H17"></path></svg>
                                    <span>Sync</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400 dark:text-slate-500">
                                No invoices recorded in this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
