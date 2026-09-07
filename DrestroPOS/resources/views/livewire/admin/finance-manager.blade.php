<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-3">
            @if($activeView !== 'list')
            <button wire:click="navigate('list')" class="p-2.5 bg-white dark:bg-[#1A1A1A] hover:bg-slate-50 dark:hover:bg-[#222222] border border-slate-200 dark:border-[#222222] text-slate-700 dark:text-gray-300 rounded-xl transition-all shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </button>
            @endif
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800 dark:text-white tracking-tight">
                    @if($activeView === 'list') Finance
                    @elseif($activeView === 'daybook') Day Book
                    @elseif($activeView === 'transactions') Transactions Ledger
                    @elseif($activeView === 'sales_purchase') Sales & Purchase
                    @elseif($activeView === 'income_expenses') Income & Expenses
                    @elseif($activeView === 'payments') Payments Ledger
                    @elseif($activeView === 'cash_banks') Cash & Banks
                    @elseif($activeView === 'reports') Reports Directory
                    @endif
                </h1>
                <p class="text-sm text-slate-500 mt-1 dark:text-neutral-400">
                    @if($activeView === 'list') Manage daybook, cashflow accounts, manual sales invoices, and Nepal VAT tax registers.
                    @else Active Workspace: {{ str_replace('_', ' ', $activeView) }}.
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($activeView !== 'list')
            <button onclick="window.print()" class="print-hide px-4 py-2.5 bg-white dark:bg-[#222222] border border-slate-200 dark:border-[#333] hover:bg-slate-50 dark:hover:bg-[#2A2A2A] text-slate-700 dark:text-gray-300 font-bold rounded-xl transition-all text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print / PDF
            </button>
            @endif

            @if($activeView === 'daybook' || $activeView === 'income_expenses')
            <button wire:click="openAddTxModal" class="print-hide px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-lg shadow-rose-950/20 active:scale-95 transition-all text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Expense
            </button>
            @elseif($activeView === 'sales_purchase')
            <button wire:click="openSalesInvoiceModal" class="print-hide px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-950/20 active:scale-95 transition-all text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create Sales Invoice
            </button>
            @elseif($activeView === 'cash_banks')
            <button wire:click="openAddAccountModal" class="print-hide px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-lg shadow-rose-950/20 active:scale-95 transition-all text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create Account
            </button>
            @endif
        </div>
    </div>

    @if(session()->has('message'))
    <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-800 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30 rounded-xl text-sm font-semibold">
        {{ session('message') }}
    </div>
    @endif

    <!-- MAIN RESTROX SIDEBAR DIRECTORY -->
    @if($activeView === 'list')
    <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl border border-slate-200 dark:border-[#222222] overflow-hidden shadow-sm dark:shadow-2xl max-w-lg text-left">
        <div class="divide-y divide-slate-100 dark:divide-[#222222]">
            <!-- Dashboard / Main Overview -->
            <div wire:click="navigate('daybook')" class="flex items-center justify-between p-5 hover:bg-rose-50 dark:hover:bg-[#252525] cursor-pointer transition-colors group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-500 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 12v-6m-9-9h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 dark:text-white text-base group-hover:text-rose-700 dark:group-hover:text-rose-400 transition-colors">Day Book</h3>
                        <p class="text-xs text-slate-500 dark:text-neutral-400 mt-0.5 group-hover:text-slate-700 dark:group-hover:text-neutral-300 transition-colors">Manage daily balance sheets</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-slate-400 dark:text-neutral-500 group-hover:translate-x-0.5 group-hover:text-rose-500 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>

            <!-- Transactions -->
            <div wire:click="navigate('transactions')" class="flex items-center justify-between p-5 hover:bg-purple-50 dark:hover:bg-[#252525] cursor-pointer transition-colors group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 dark:text-white text-base group-hover:text-purple-700 dark:group-hover:text-purple-400 transition-colors">Transactions</h3>
                        <p class="text-xs text-slate-500 dark:text-neutral-400 mt-0.5 group-hover:text-slate-700 dark:group-hover:text-neutral-300 transition-colors">All financial transaction logs</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-slate-400 dark:text-neutral-500 group-hover:translate-x-0.5 group-hover:text-purple-500 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>

            <!-- Sales & Purchase -->
            <div wire:click="navigate('sales_purchase')" class="flex items-center justify-between p-5 hover:bg-blue-50 dark:hover:bg-[#252525] cursor-pointer transition-colors group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 dark:text-white text-base group-hover:text-blue-700 dark:group-hover:text-blue-400 transition-colors">Sales & Purchase</h3>
                        <p class="text-xs text-slate-500 dark:text-neutral-400 mt-0.5 group-hover:text-slate-700 dark:group-hover:text-neutral-300 transition-colors">Manage sales invoices and supplier purchasing</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-slate-400 dark:text-neutral-500 group-hover:translate-x-0.5 group-hover:text-blue-500 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>

            <!-- Income & Expenses -->
            <div wire:click="navigate('income_expenses')" class="flex items-center justify-between p-5 hover:bg-rose-50 dark:hover:bg-[#252525] cursor-pointer transition-colors group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-500 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 dark:text-white text-base group-hover:text-rose-700 dark:group-hover:text-rose-400 transition-colors">Income & Expenses</h3>
                        <p class="text-xs text-slate-500 dark:text-neutral-400 mt-0.5 group-hover:text-slate-700 dark:group-hover:text-neutral-300 transition-colors">Track utilities, wages, and raw materials</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-slate-400 dark:text-neutral-500 group-hover:translate-x-0.5 group-hover:text-rose-500 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>

            <!-- Payments -->
            <div wire:click="navigate('payments')" class="flex items-center justify-between p-5 hover:bg-teal-50 dark:hover:bg-[#252525] cursor-pointer transition-colors group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-500 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 dark:text-white text-base group-hover:text-teal-700 dark:group-hover:text-teal-400 transition-colors">Payments</h3>
                        <p class="text-xs text-slate-500 dark:text-neutral-400 mt-0.5 group-hover:text-slate-700 dark:group-hover:text-neutral-300 transition-colors">POS checkout split payment tracking</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-slate-400 dark:text-neutral-500 group-hover:translate-x-0.5 group-hover:text-teal-500 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>

            <!-- Cash & Banks -->
            <div wire:click="navigate('cash_banks')" class="flex items-center justify-between p-5 hover:bg-amber-50 dark:hover:bg-[#252525] cursor-pointer transition-colors group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 dark:text-white text-base group-hover:text-amber-700 dark:group-hover:text-amber-400 transition-colors">Cash & Banks</h3>
                        <p class="text-xs text-slate-500 dark:text-neutral-400 mt-0.5 group-hover:text-slate-700 dark:group-hover:text-neutral-300 transition-colors">Setup Counter, Bank Account, digital wallets</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-slate-400 dark:text-neutral-500 group-hover:translate-x-0.5 group-hover:text-amber-500 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>

            <!-- Reports -->
            <div wire:click="navigate('reports')" class="flex items-center justify-between p-5 hover:bg-emerald-50 dark:hover:bg-[#252525] cursor-pointer transition-colors group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-500 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 dark:text-white text-base group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">Reports</h3>
                        <p class="text-xs text-slate-500 dark:text-neutral-400 mt-0.5 group-hover:text-slate-700 dark:group-hover:text-neutral-300 transition-colors">Trial balance, VAT ledger reports</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-slate-400 dark:text-neutral-500 group-hover:translate-x-0.5 group-hover:text-emerald-500 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>
        </div>
    </div>
    @endif

    <!-- DAY BOOK MATRIX VIEW -->
    @if($activeView === 'daybook')
    <div class="space-y-6">
        <!-- Date / Title & 24-Hour Auto-Close Status Panel -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-50 dark:bg-[#111111] p-4 rounded-xl border border-slate-200 dark:border-[#222222]">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                <div class="text-sm font-semibold text-slate-700 dark:text-gray-300 flex items-center gap-2">
                    <span>📅 Day Book Date:</span>
                    <input type="date" wire:model.live="daybookDate" class="px-3 py-1.5 bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333] text-xs font-bold text-rose-500 rounded-lg outline-none focus:ring-2 focus:ring-rose-500">
                </div>
                <div class="flex items-center gap-1.5 print-hide">
                    <button wire:click="$set('daybookDate', '{{ date('Y-m-d') }}')" class="px-2.5 py-1 text-[11px] font-extrabold rounded-lg {{ $daybookDate === date('Y-m-d') ? 'bg-rose-600 text-white' : 'bg-slate-200 dark:bg-[#222] text-slate-700 dark:text-gray-300' }}">Today</button>
                    <button wire:click="$set('daybookDate', '{{ date('Y-m-d', strtotime('-1 day')) }}')" class="px-2.5 py-1 text-[11px] font-extrabold rounded-lg {{ $daybookDate === date('Y-m-d', strtotime('-1 day')) ? 'bg-rose-600 text-white' : 'bg-slate-200 dark:bg-[#222] text-slate-700 dark:text-gray-300' }}">Yesterday</button>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                @if(isset($activeSession) && $activeSession->status === 'auto_closed')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-500/10 border border-amber-500/20 text-amber-600 text-xs font-extrabold rounded-full animate-pulse">
                        🔒 Session Auto-Closed (24-Hour Expiration)
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 text-xs font-extrabold rounded-full">
                        🟢 Daybook Session Active (24h Auto-Close Enabled)
                    </span>
                @endif
            </div>
        </div>

        @php
            $txList = $daybookTransactions ?? $transactions;
            $cashId = collect($accounts)->firstWhere('name', 'Counter (Cash)')->id ?? 999;
            $bankId = collect($accounts)->firstWhere('name', 'Bank Account')->id ?? 998;
            $personalId = collect($accounts)->firstWhere('name', 'Owner\'s Account')->id ?? 997;

            $cashSales = collect($txList)->where('type', 'sales')->where('account_id', $cashId)->sum('amount') + collect($orderSales)->sum('total_amount');
            $bankSales = collect($txList)->where('type', 'sales')->where('account_id', $bankId)->sum('amount');
            $ownerSales = collect($txList)->where('type', 'sales')->where('account_id', $personalId)->sum('amount');
            $creditSales = collect($txList)->where('type', 'sales')->where('payment_status', 'unpaid')->sum('amount');

            $cashIncome = collect($txList)->where('type', 'income')->where('account_id', $cashId)->sum('amount');
            $bankIncome = collect($txList)->where('type', 'income')->where('account_id', $bankId)->sum('amount');
            $ownerIncome = collect($txList)->where('type', 'income')->where('account_id', $personalId)->sum('amount');
            $creditIncome = collect($txList)->where('type', 'income')->where('payment_status', 'unpaid')->sum('amount');

            $cashPurchase = collect($txList)->where('type', 'purchase')->where('account_id', $cashId)->sum('amount');
            $bankPurchase = collect($txList)->where('type', 'purchase')->where('account_id', $bankId)->sum('amount');
            $ownerPurchase = collect($txList)->where('type', 'purchase')->where('account_id', $personalId)->sum('amount');
            $creditPurchase = collect($txList)->where('type', 'purchase')->where('payment_status', 'unpaid')->sum('amount');

            $cashExpense = collect($txList)->where('type', 'expense')->where('account_id', $cashId)->sum('amount');
            $bankExpense = collect($txList)->where('type', 'expense')->where('account_id', $bankId)->sum('amount');
            $ownerExpense = collect($txList)->where('type', 'expense')->where('account_id', $personalId)->sum('amount');
            $creditExpense = collect($txList)->where('type', 'expense')->where('payment_status', 'unpaid')->sum('amount');
        @endphp

        <!-- Matrix Table -->
        <div class="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#222222] rounded-2xl overflow-hidden shadow-sm dark:shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-[#1A1A1A] text-slate-500 dark:text-gray-400 font-bold uppercase tracking-wider border-b border-slate-200 dark:border-[#222222]">
                            <th class="px-6 py-4">PMT Accounts</th>
                            <th class="px-6 py-4">Counter (Cash)</th>
                            <th class="px-6 py-4">Bank Account</th>
                            <th class="px-6 py-4">Owner's Account</th>
                            <th class="px-6 py-4">Total</th>
                            <th class="px-6 py-4">Credit (Due)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-[#222222] text-slate-700 dark:text-gray-300">
                        <tr class="bg-slate-100/50 dark:bg-[#1A1A1A]/50"><td colspan="6" class="px-6 py-2.5 font-black text-rose-500 tracking-wide">RECEIPTS</td></tr>
                        <tr>
                            <td class="px-6 py-3 font-semibold text-slate-800 dark:text-white">Net Sales</td>
                            <td class="px-6 py-3">Rs. {{ number_format($cashSales, 0) }}</td>
                            <td class="px-6 py-3">Rs. {{ number_format($bankSales, 0) }}</td>
                            <td class="px-6 py-3">Rs. {{ number_format($ownerSales, 0) }}</td>
                            <td class="px-6 py-3 font-bold text-slate-800 dark:text-white">Rs. {{ number_format($cashSales + $bankSales + $ownerSales, 0) }}</td>
                            <td class="px-6 py-3 text-rose-500 font-bold">Rs. {{ number_format($creditSales, 0) }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3 font-semibold text-slate-800 dark:text-white">Income</td>
                            <td class="px-6 py-3">Rs. {{ number_format($cashIncome, 0) }}</td>
                            <td class="px-6 py-3">Rs. {{ number_format($bankIncome, 0) }}</td>
                            <td class="px-6 py-3">Rs. {{ number_format($ownerIncome, 0) }}</td>
                            <td class="px-6 py-3 font-bold text-slate-800 dark:text-white">Rs. {{ number_format($cashIncome + $bankIncome + $ownerIncome, 0) }}</td>
                            <td class="px-6 py-3 text-rose-500 font-bold">Rs. {{ number_format($creditIncome, 0) }}</td>
                        </tr>
                        @php
                            $totCashRec = $cashSales + $cashIncome;
                            $totBankRec = $bankSales + $bankIncome;
                            $totOwnerRec = $ownerSales + $ownerIncome;
                            $totRec = $totCashRec + $totBankRec + $totOwnerRec;
                            $totCreditRec = $creditSales + $creditIncome;
                        @endphp
                        <tr class="bg-slate-50 dark:bg-[#1A1A1A] font-bold text-slate-800 dark:text-white border-t-2 border-slate-200 dark:border-[#222222]">
                            <td class="px-6 py-3.5">Total Receipts [A]</td>
                            <td class="px-6 py-3.5">Rs. {{ number_format($totCashRec, 0) }}</td>
                            <td class="px-6 py-3.5">Rs. {{ number_format($totBankRec, 0) }}</td>
                            <td class="px-6 py-3.5">Rs. {{ number_format($totOwnerRec, 0) }}</td>
                            <td class="px-6 py-3.5 text-rose-500 font-black">Rs. {{ number_format($totRec, 0) }}</td>
                            <td class="px-6 py-3.5 text-red-500">-</td>
                        </tr>

                        <tr class="bg-slate-100/50 dark:bg-[#1A1A1A]/50"><td colspan="6" class="px-6 py-2.5 font-black text-rose-500 tracking-wide">PAYMENTS</td></tr>
                        <tr>
                            <td class="px-6 py-3 font-semibold text-slate-800 dark:text-white">Purchase</td>
                            <td class="px-6 py-3">Rs. {{ number_format($cashPurchase, 0) }}</td>
                            <td class="px-6 py-3">Rs. {{ number_format($bankPurchase, 0) }}</td>
                            <td class="px-6 py-3">Rs. {{ number_format($ownerPurchase, 0) }}</td>
                            <td class="px-6 py-3 font-bold text-slate-800 dark:text-white">Rs. {{ number_format($cashPurchase + $bankPurchase + $ownerPurchase, 0) }}</td>
                            <td class="px-6 py-3 text-rose-500 font-bold">Rs. {{ number_format($creditPurchase, 0) }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3 font-semibold text-slate-800 dark:text-white">Expenses</td>
                            <td class="px-6 py-3">Rs. {{ number_format($cashExpense, 0) }}</td>
                            <td class="px-6 py-3">Rs. {{ number_format($bankExpense, 0) }}</td>
                            <td class="px-6 py-3">Rs. {{ number_format($ownerExpense, 0) }}</td>
                            <td class="px-6 py-3 font-bold text-slate-800 dark:text-white">Rs. {{ number_format($cashExpense + $bankExpense + $ownerExpense, 0) }}</td>
                            <td class="px-6 py-3 text-rose-500 font-bold">Rs. {{ number_format($creditExpense, 0) }}</td>
                        </tr>
                        @php
                            $totCashPay = $cashPurchase + $cashExpense;
                            $totBankPay = $bankPurchase + $bankExpense;
                            $totOwnerPay = $ownerPurchase + $ownerExpense;
                            $totPay = $totCashPay + $totBankPay + $totOwnerPay;
                            $totCreditPay = $creditPurchase + $creditExpense;
                        @endphp
                        <tr class="bg-slate-50 dark:bg-[#1A1A1A] font-bold text-slate-800 dark:text-white border-t-2 border-slate-200 dark:border-[#222222]">
                            <td class="px-6 py-3.5">Total Payments [B]</td>
                            <td class="px-6 py-3.5">Rs. {{ number_format($totCashPay, 0) }}</td>
                            <td class="px-6 py-3.5">Rs. {{ number_format($totBankPay, 0) }}</td>
                            <td class="px-6 py-3.5">Rs. {{ number_format($totOwnerPay, 0) }}</td>
                            <td class="px-6 py-3.5 text-rose-500 font-black">Rs. {{ number_format($totPay, 0) }}</td>
                            <td class="px-6 py-3.5 text-red-500">-</td>
                        </tr>

                        <!-- Totals C, D, E -->
                        @php
                            $netCash = $totCashRec - $totCashPay;
                            $netBank = $totBankRec - $totBankPay;
                            $netOwner = $totOwnerRec - $totOwnerPay;
                            $netTot = $totRec - $totPay;
                        @endphp
                        <tr class="bg-slate-100 dark:bg-[#222222] font-black border-t-2 border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white">
                            <td class="px-6 py-4 text-sm">Net Receipt [C = A - B]</td>
                            <td class="px-6 py-4">Rs. {{ number_format($netCash, 0) }}</td>
                            <td class="px-6 py-4">Rs. {{ number_format($netBank, 0) }}</td>
                            <td class="px-6 py-4">Rs. {{ number_format($netOwner, 0) }}</td>
                            <td class="px-6 py-4 text-emerald-650 dark:text-emerald-450 text-sm">Rs. {{ number_format($netTot, 0) }}</td>
                            <td class="px-6 py-4">-</td>
                        </tr>
                        <tr class="bg-slate-50 dark:bg-[#111111] font-semibold text-slate-500 dark:text-gray-400">
                            <td class="px-6 py-3.5">Opening Balance (D)</td>
                            <td class="px-6 py-3.5">Rs. 0</td>
                            <td class="px-6 py-3.5">Rs. 0</td>
                            <td class="px-6 py-3.5">Rs. 0</td>
                            <td class="px-6 py-3.5">Rs. 0</td>
                            <td class="px-6 py-3.5">-</td>
                        </tr>
                        <tr class="bg-slate-100 dark:bg-[#222222] font-black border-t-2 border-slate-200 dark:border-slate-800 text-slate-800 dark:text-white">
                            <td class="px-6 py-4 text-sm">Closing Balance [E = C + D]</td>
                            <td class="px-6 py-4">Rs. {{ number_format($netCash, 0) }}</td>
                            <td class="px-6 py-4">Rs. {{ number_format($netBank, 0) }}</td>
                            <td class="px-6 py-4">Rs. {{ number_format($netOwner, 0) }}</td>
                            <td class="px-6 py-4 text-emerald-650 dark:text-emerald-400 text-sm">Rs. {{ number_format($netTot, 0) }}</td>
                            <td class="px-6 py-4">-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- TRANSACTIONS VIEW -->
    @if($activeView === 'transactions')
    <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl border border-slate-200 dark:border-[#222222] overflow-hidden shadow-sm dark:shadow-2xl">
        <div class="px-6 py-4 bg-slate-50 dark:bg-[#1A1A1A] border-b border-slate-200 dark:border-[#222222]">
            <h2 class="font-extrabold text-slate-800 dark:text-white">All Financial Ledger Logs</h2>
        </div>
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-100/50 dark:bg-[#222222] text-slate-500 dark:text-gray-400 font-bold uppercase border-b border-slate-200 dark:border-[#222222]">
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Party Name</th>
                        <th class="px-6 py-3">Category</th>
                        <th class="px-6 py-3">Reference</th>
                        <th class="px-6 py-3">Type</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-[#222222] text-slate-700 dark:text-gray-300">
                    <!-- Automated Sales -->
                    @foreach($orderSales as $sale)
                    <tr class="hover:bg-slate-50 dark:hover:bg-[#222222]/50 transition-colors">
                        <td class="px-6 py-4">{{ explode(' ', $sale->created_at)[0] }}</td>
                        <td class="px-6 py-4 font-semibold text-slate-800 dark:text-white">POS Customer</td>
                        <td class="px-6 py-4">POS Sales</td>
                        <td class="px-6 py-4 text-slate-500 dark:text-gray-400">{{ $sale->order_number }}</td>
                        <td class="px-6 py-4"><span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-450 rounded-full uppercase">Sales (In)</span></td>
                        <td class="px-6 py-4"><span class="px-2 py-0.5 text-[10px] bg-emerald-100 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-450 rounded-full font-bold">PAID</span></td>
                        <td class="px-6 py-4 text-sm font-bold text-right text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($sale->total_amount, 0) }}</td>
                    </tr>
                    @endforeach

                    <!-- Manual ledger entries -->
                    @foreach($transactions as $t)
                    <tr class="hover:bg-slate-50 dark:hover:bg-[#222222]/50 transition-colors">
                        <td class="px-6 py-4">{{ $t->date }}</td>
                        <td class="px-6 py-4 font-semibold text-slate-800 dark:text-white">{{ $t->party_name ?: '-' }}</td>
                        <td class="px-6 py-4 font-bold text-slate-800 dark:text-white">{{ $t->category }}</td>
                        <td class="px-6 py-4 text-slate-500 dark:text-gray-400">{{ $t->reference_number ?: '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full uppercase {{ $t->type === 'income' || $t->type === 'sales' ? 'bg-emerald-100 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-450' : 'bg-rose-100 dark:bg-rose-950/20 text-rose-700 dark:text-rose-500' }}">
                                {{ $t->type }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 text-[10px] rounded-full font-bold uppercase {{ $t->payment_status === 'paid' ? 'bg-emerald-100 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-450' : 'bg-amber-100 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400' }}">
                                {{ $t->payment_status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-right {{ $t->type === 'income' || $t->type === 'sales' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-500' }}">
                            Rs. {{ number_format($t->amount, 0) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- SALES & PURCHASE VIEW -->
    @if($activeView === 'sales_purchase')
    <div x-data="{ spTab: 'sales' }" class="space-y-5">
        <!-- Tabs -->
        <div class="flex gap-1 bg-slate-100 dark:bg-[#111111] p-1 rounded-xl w-fit border border-slate-200 dark:border-[#222222] print-hide">
            <button @click="spTab = 'sales'" :class="spTab === 'sales' ? 'bg-white dark:bg-[#1A1A1A] text-emerald-600 dark:text-emerald-400 shadow-sm border-slate-200 dark:border-[#333]' : 'text-slate-500 dark:text-gray-400 hover:text-slate-700 dark:hover:text-white border-transparent'" class="px-5 py-2.5 rounded-lg text-xs font-bold transition-all border flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                Sales Invoices
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400">Rs. {{ number_format(collect($transactions)->where('type', 'sales')->sum('amount'), 0) }}</span>
            </button>
            <button @click="spTab = 'purchase'" :class="spTab === 'purchase' ? 'bg-white dark:bg-[#1A1A1A] text-rose-600 dark:text-rose-400 shadow-sm border-slate-200 dark:border-[#333]' : 'text-slate-500 dark:text-gray-400 hover:text-slate-700 dark:hover:text-white border-transparent'" class="px-5 py-2.5 rounded-lg text-xs font-bold transition-all border flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                Purchasing Ledger
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-rose-100 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400">Rs. {{ number_format(collect($transactions)->where('type', 'purchase')->sum('amount'), 0) }}</span>
            </button>
        </div>

        <!-- Sales Tab -->
        <div x-show="spTab === 'sales'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl border border-slate-200 dark:border-[#222222] overflow-hidden shadow-sm dark:shadow-2xl">
                <div class="px-6 py-4 bg-slate-50 dark:bg-[#1A1A1A] border-b border-slate-200 dark:border-[#222222] flex justify-between items-center">
                    <h2 class="font-extrabold text-slate-800 dark:text-white">Manual Sales Invoices</h2>
                    <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/30 px-2 py-0.5 rounded">Total: Rs. {{ number_format(collect($transactions)->where('type', 'sales')->sum('amount'), 0) }}</span>
                </div>
                <div class="overflow-x-auto text-xs">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-[#1A1A1A] text-slate-500 dark:text-gray-400 font-bold uppercase border-b border-slate-200 dark:border-[#222222]">
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Invoice Ref</th>
                                <th class="px-6 py-3">Customer</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-[#222222] text-slate-700 dark:text-gray-300">
                            @forelse(collect($transactions)->where('type', 'sales') as $s)
                            <tr class="hover:bg-slate-50 dark:hover:bg-[#222222]/30">
                                <td class="px-6 py-3.5 text-slate-500 dark:text-gray-400">{{ $s->date }}</td>
                                <td class="px-6 py-3.5 font-bold text-slate-800 dark:text-white">{{ $s->reference_number }}</td>
                                <td class="px-6 py-3.5 text-slate-500 dark:text-gray-400">{{ $s->party_name ?: 'Walk-in' }}</td>
                                <td class="px-6 py-3.5">
                                    <span class="px-2 py-0.5 text-[10px] rounded-full font-bold uppercase {{ $s->payment_status === 'paid' ? 'bg-emerald-100 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400' : 'bg-amber-100 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400' }}">{{ $s->payment_status }}</span>
                                </td>
                                <td class="px-6 py-3.5 text-sm font-bold text-right text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($s->amount, 0) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-500 dark:text-neutral-400">No manual sales invoice records created yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Purchase Tab -->
        <div x-show="spTab === 'purchase'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl border border-slate-200 dark:border-[#222222] overflow-hidden shadow-sm dark:shadow-2xl">
                <div class="px-6 py-4 bg-slate-50 dark:bg-[#1A1A1A] border-b border-slate-200 dark:border-[#222222] flex justify-between items-center">
                    <h2 class="font-extrabold text-slate-800 dark:text-white">Purchasing Ledger</h2>
                    <span class="text-xs font-bold text-rose-600 dark:text-rose-500 bg-rose-100 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/30 px-2 py-0.5 rounded">Total: Rs. {{ number_format(collect($transactions)->where('type', 'purchase')->sum('amount'), 0) }}</span>
                </div>
                <div class="overflow-x-auto text-xs">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-[#1A1A1A] text-slate-500 dark:text-gray-400 font-bold uppercase border-b border-slate-200 dark:border-[#222222]">
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Category</th>
                                <th class="px-6 py-3">Supplier Name</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-[#222222] text-slate-700 dark:text-gray-300">
                            @forelse(collect($transactions)->where('type', 'purchase') as $p)
                            <tr class="hover:bg-slate-50 dark:hover:bg-[#222222]/30">
                                <td class="px-6 py-3.5 text-slate-500 dark:text-gray-400">{{ $p->date }}</td>
                                <td class="px-6 py-3.5 font-bold text-slate-800 dark:text-white">{{ $p->category }}</td>
                                <td class="px-6 py-3.5 text-slate-500 dark:text-gray-400">{{ $p->party_name ?: '-' }}</td>
                                <td class="px-6 py-3.5">
                                    <span class="px-2 py-0.5 text-[10px] rounded-full font-bold uppercase {{ $p->payment_status === 'paid' ? 'bg-emerald-100 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400' : 'bg-amber-100 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400' }}">{{ $p->payment_status }}</span>
                                </td>
                                <td class="px-6 py-3.5 text-sm font-bold text-right text-rose-600 dark:text-rose-500">Rs. {{ number_format($p->amount, 0) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-500 dark:text-neutral-400">No purchases or stock expenses logged yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- INCOME & EXPENSES VIEW -->
    @if($activeView === 'income_expenses')
    <div x-data="{ ieTab: 'expenses' }" class="space-y-5">
        <!-- Summary Cards -->
        @php
            $totalExpenses = collect($transactions)->where('type', 'expense')->sum('amount');
            $totalIncome = collect($transactions)->where('type', 'income')->sum('amount');
            $netBalance = $totalIncome - $totalExpenses;
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl border border-slate-200 dark:border-[#222222] p-5 shadow-sm dark:shadow-2xl">
                <p class="text-[11px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider">Total Income</p>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">Rs. {{ number_format($totalIncome, 0) }}</p>
            </div>
            <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl border border-slate-200 dark:border-[#222222] p-5 shadow-sm dark:shadow-2xl">
                <p class="text-[11px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider">Total Expenses</p>
                <p class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">Rs. {{ number_format($totalExpenses, 0) }}</p>
            </div>
            <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl border border-slate-200 dark:border-[#222222] p-5 shadow-sm dark:shadow-2xl">
                <p class="text-[11px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider">Net Balance</p>
                <p class="text-2xl font-black {{ $netBalance >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} mt-1">Rs. {{ number_format($netBalance, 0) }}</p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex gap-1 bg-slate-100 dark:bg-[#111111] p-1 rounded-xl w-fit border border-slate-200 dark:border-[#222222] print-hide">
            <button @click="ieTab = 'expenses'" :class="ieTab === 'expenses' ? 'bg-white dark:bg-[#1A1A1A] text-rose-600 dark:text-rose-400 shadow-sm border-slate-200 dark:border-[#333]' : 'text-slate-500 dark:text-gray-400 hover:text-slate-700 dark:hover:text-white border-transparent'" class="px-5 py-2.5 rounded-lg text-xs font-bold transition-all border flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                Expenses
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-rose-100 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400">{{ collect($transactions)->where('type', 'expense')->count() }}</span>
            </button>
            <button @click="ieTab = 'income'" :class="ieTab === 'income' ? 'bg-white dark:bg-[#1A1A1A] text-emerald-600 dark:text-emerald-400 shadow-sm border-slate-200 dark:border-[#333]' : 'text-slate-500 dark:text-gray-400 hover:text-slate-700 dark:hover:text-white border-transparent'" class="px-5 py-2.5 rounded-lg text-xs font-bold transition-all border flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                Income
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400">{{ collect($transactions)->where('type', 'income')->count() }}</span>
            </button>
        </div>

        <!-- Expenses Tab -->
        <div x-show="ieTab === 'expenses'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl border border-slate-200 dark:border-[#222222] overflow-hidden shadow-sm dark:shadow-2xl">
                <div class="px-6 py-4 bg-slate-50 dark:bg-[#1A1A1A] border-b border-slate-200 dark:border-[#222222] flex justify-between items-center">
                    <h2 class="font-extrabold text-slate-800 dark:text-white">Expenses Ledger</h2>
                    <span class="text-xs font-bold text-rose-600 dark:text-rose-400 bg-rose-100 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/30 px-2 py-0.5 rounded">Total: Rs. {{ number_format($totalExpenses, 0) }}</span>
                </div>
                <div class="overflow-x-auto text-xs">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-100/50 dark:bg-[#222222] text-slate-500 dark:text-gray-400 font-bold uppercase border-b border-slate-200 dark:border-[#222222]">
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Category</th>
                                <th class="px-6 py-3">Party Name</th>
                                <th class="px-6 py-3">Remarks</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Amount</th>
                                <th class="px-6 py-3 text-right print-hide">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-[#222222] text-slate-700 dark:text-gray-300">
                            @forelse(collect($transactions)->where('type', 'expense') as $e)
                            <tr class="hover:bg-slate-50 dark:hover:bg-[#222222]/50 transition-colors">
                                <td class="px-6 py-3.5 text-slate-500 dark:text-gray-400">{{ $e->date }}</td>
                                <td class="px-6 py-3.5 font-bold text-slate-800 dark:text-white">{{ $e->category }}</td>
                                <td class="px-6 py-3.5">{{ $e->party_name ?: '-' }}</td>
                                <td class="px-6 py-3.5 text-slate-500 dark:text-gray-400 max-w-[200px] truncate">{{ $e->remarks ?: '-' }}</td>
                                <td class="px-6 py-3.5">
                                    <span class="px-2 py-0.5 text-[10px] rounded-full font-bold uppercase {{ $e->payment_status === 'paid' ? 'bg-emerald-100 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400' : 'bg-amber-100 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400' }}">{{ $e->payment_status }}</span>
                                </td>
                                <td class="px-6 py-3.5 text-sm font-bold text-right text-rose-600 dark:text-rose-500">Rs. {{ number_format($e->amount, 0) }}</td>
                                <td class="px-6 py-3.5 text-right print-hide">
                                    <button wire:click="deleteTransaction({{ $e->id }})" wire:confirm="Are you sure you want to delete this expense?" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-500/10 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-500 dark:text-neutral-400">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="w-10 h-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <span class="font-semibold">No expenses recorded yet.</span>
                                        <span class="text-slate-400 dark:text-slate-500">Click "+ Add Expense" to record your first expense.</span>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Income Tab -->
        <div x-show="ieTab === 'income'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl border border-slate-200 dark:border-[#222222] overflow-hidden shadow-sm dark:shadow-2xl">
                <div class="px-6 py-4 bg-slate-50 dark:bg-[#1A1A1A] border-b border-slate-200 dark:border-[#222222] flex justify-between items-center">
                    <h2 class="font-extrabold text-slate-800 dark:text-white">Income Ledger</h2>
                    <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/30 px-2 py-0.5 rounded">Total: Rs. {{ number_format($totalIncome, 0) }}</span>
                </div>
                <div class="overflow-x-auto text-xs">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-100/50 dark:bg-[#222222] text-slate-500 dark:text-gray-400 font-bold uppercase border-b border-slate-200 dark:border-[#222222]">
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Category</th>
                                <th class="px-6 py-3">Party Name</th>
                                <th class="px-6 py-3">Remarks</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Amount</th>
                                <th class="px-6 py-3 text-right print-hide">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-[#222222] text-slate-700 dark:text-gray-300">
                            @forelse(collect($transactions)->where('type', 'income') as $i)
                            <tr class="hover:bg-slate-50 dark:hover:bg-[#222222]/50 transition-colors">
                                <td class="px-6 py-3.5 text-slate-500 dark:text-gray-400">{{ $i->date }}</td>
                                <td class="px-6 py-3.5 font-bold text-slate-800 dark:text-white">{{ $i->category }}</td>
                                <td class="px-6 py-3.5">{{ $i->party_name ?: '-' }}</td>
                                <td class="px-6 py-3.5 text-slate-500 dark:text-gray-400 max-w-[200px] truncate">{{ $i->remarks ?: '-' }}</td>
                                <td class="px-6 py-3.5">
                                    <span class="px-2 py-0.5 text-[10px] rounded-full font-bold uppercase {{ $i->payment_status === 'paid' ? 'bg-emerald-100 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400' : 'bg-amber-100 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400' }}">{{ $i->payment_status }}</span>
                                </td>
                                <td class="px-6 py-3.5 text-sm font-bold text-right text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($i->amount, 0) }}</td>
                                <td class="px-6 py-3.5 text-right print-hide">
                                    <button wire:click="deleteTransaction({{ $i->id }})" wire:confirm="Are you sure you want to delete this income entry?" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-500/10 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-500 dark:text-neutral-400">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="w-10 h-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span class="font-semibold">No income recorded yet.</span>
                                        <span class="text-slate-400 dark:text-slate-500">Income entries will appear here when added.</span>
                                    </div>
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

    <!-- PAYMENTS VIEW -->
    @if($activeView === 'payments')
    <div class="space-y-5">
        <!-- POS Payment Summary -->
        @php
            $cashPayments = collect($orderSales)->sum('total_amount');
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl border border-slate-200 dark:border-[#222222] p-5 shadow-sm dark:shadow-2xl">
                <p class="text-[11px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider">Total POS Payments</p>
                <p class="text-2xl font-black text-teal-600 dark:text-teal-400 mt-1">Rs. {{ number_format($cashPayments, 0) }}</p>
            </div>
            <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl border border-slate-200 dark:border-[#222222] p-5 shadow-sm dark:shadow-2xl">
                <p class="text-[11px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider">Total Orders</p>
                <p class="text-2xl font-black text-slate-800 dark:text-white mt-1">{{ count($orderSales) }}</p>
            </div>
            <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl border border-slate-200 dark:border-[#222222] p-5 shadow-sm dark:shadow-2xl">
                <p class="text-[11px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider">Average Order</p>
                <p class="text-2xl font-black text-slate-800 dark:text-white mt-1">Rs. {{ count($orderSales) > 0 ? number_format($cashPayments / count($orderSales), 0) : '0' }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1A1A1A] rounded-2xl border border-slate-200 dark:border-[#222222] overflow-hidden shadow-sm dark:shadow-2xl">
            <div class="px-6 py-4 bg-slate-50 dark:bg-[#1A1A1A] border-b border-slate-200 dark:border-[#222222] flex justify-between items-center">
                <h2 class="font-extrabold text-slate-800 dark:text-white">POS Checkout Payments</h2>
                <span class="text-xs font-bold text-teal-700 dark:text-teal-400 bg-teal-100 dark:bg-teal-950/30 border border-teal-200 dark:border-teal-900/30 px-2 py-0.5 rounded">{{ count($orderSales) }} Orders</span>
            </div>
            <div class="overflow-x-auto text-xs">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-100/50 dark:bg-[#222222] text-slate-500 dark:text-gray-400 font-bold uppercase border-b border-slate-200 dark:border-[#222222]">
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Order Number</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-[#222222] text-slate-700 dark:text-gray-300">
                        @forelse($orderSales as $sale)
                        <tr class="hover:bg-slate-50 dark:hover:bg-[#222222]/50 transition-colors">
                            <td class="px-6 py-3.5 text-slate-500 dark:text-gray-400">{{ explode(' ', $sale->created_at)[0] }}</td>
                            <td class="px-6 py-3.5 font-bold text-slate-800 dark:text-white">{{ $sale->order_number }}</td>
                            <td class="px-6 py-3.5"><span class="px-2 py-0.5 text-[10px] bg-emerald-100 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 rounded-full font-bold uppercase">Completed</span></td>
                            <td class="px-6 py-3.5 text-sm font-bold text-right text-teal-600 dark:text-teal-400">Rs. {{ number_format($sale->total_amount, 0) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-500 dark:text-neutral-400">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                    <span class="font-semibold">No POS payments recorded yet.</span>
                                    <span class="text-slate-400 dark:text-slate-500">Completed POS orders will appear here.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- CASH & BANKS VIEW -->
    @if($activeView === 'cash_banks')
    <div class="space-y-6 text-left">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($accounts as $acc)
            <div class="bg-white dark:bg-[#1A1A1A] p-6 rounded-2xl border border-slate-200 dark:border-[#222222] shadow-sm dark:shadow-2xl flex flex-col justify-between relative group">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5 text-slate-800 dark:text-white">
                        <div class="w-9 h-9 rounded-xl bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#222222] flex items-center justify-center text-rose-500">
                            @if($acc->type === 'cash')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            @elseif($acc->type === 'bank')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            @endif
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-800 dark:text-white text-sm">{{ $acc->name }}</h3>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 bg-slate-100 dark:bg-[#222222] text-slate-500 dark:text-neutral-450 border border-slate-200 dark:border-[#222222] rounded-md mt-0.5 inline-block">{{ $acc->type }}</span>
                        </div>
                    </div>
                </div>
                <div class="mt-5">
                    <span class="text-[11px] text-slate-400 dark:text-gray-400 block mb-0.5">Account Balance</span>
                    <span class="text-2xl font-black text-slate-800 dark:text-white">Rs. {{ number_format($acc->balance, 0) }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- REPORTS LIST VIEW -->
    @if($activeView === 'reports')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
        <!-- 1. Accounting -->
        <div class="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#222222] rounded-2xl p-6 space-y-4 shadow-sm dark:shadow-2xl">
            <h3 class="font-extrabold text-rose-500 flex items-center gap-2 text-sm border-b border-slate-100 dark:border-[#222222] pb-2 uppercase tracking-wide">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 12v-6m-9-9h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Accounting Reports
            </h3>
            <ul class="space-y-2.5 text-xs text-slate-700 dark:text-gray-300">
                <li class="hover:text-rose-500 cursor-pointer flex justify-between"><span>Transaction List</span></li>
                <li class="hover:text-rose-500 cursor-pointer flex justify-between"><span>Day Book</span></li>
                <li class="hover:text-rose-500 cursor-pointer flex justify-between"><span>Account Summary</span></li>
                <li class="hover:text-rose-500 cursor-pointer flex justify-between text-rose-500 font-bold"><span>Trial Balance 📌</span></li>
                <li class="hover:text-rose-500 cursor-pointer flex justify-between"><span>Profit Or Loss Statement <span class="bg-emerald-650 text-white text-[9px] px-1.5 py-0.5 rounded font-black">NEW</span></span></li>
                <li class="hover:text-rose-500 cursor-pointer flex justify-between"><span>Balance Sheet</span></li>
            </ul>
        </div>

        <!-- 2. Nepal Tax Report -->
        <div class="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#222222] rounded-2xl p-6 space-y-4 shadow-sm dark:shadow-2xl">
            <h3 class="font-extrabold text-rose-500 flex items-center gap-2 text-sm border-b border-slate-100 dark:border-[#222222] pb-2 uppercase tracking-wide">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                Nepal VAT Tax Reports
            </h3>
            <ul class="space-y-2.5 text-xs text-slate-700 dark:text-gray-300">
                <li class="hover:text-rose-500 cursor-pointer">Sales Register (VAT Annex 13)</li>
                <li class="hover:text-rose-500 cursor-pointer">Sales Return Register</li>
                <li class="hover:text-rose-500 cursor-pointer">Purchase Register</li>
                <li class="hover:text-rose-500 cursor-pointer">Purchase Return Register</li>
                <li class="hover:text-rose-500 cursor-pointer">VAT Summary Report</li>
                <li class="hover:text-rose-500 cursor-pointer text-slate-400 dark:text-slate-500">Annex 5 Materialized View</li>
            </ul>
        </div>

        <!-- 3. Sales Report Directory -->
        <div class="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#222222] rounded-2xl p-6 space-y-4 shadow-sm dark:shadow-2xl">
            <h3 class="font-extrabold text-rose-500 flex items-center gap-2 text-sm border-b border-slate-100 dark:border-[#222222] pb-2 uppercase tracking-wide">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                Sales & Performance
            </h3>
            <ul class="space-y-2.5 text-xs text-slate-700 dark:text-gray-300">
                <li class="hover:text-rose-500 cursor-pointer">Sales Master Report</li>
                <li class="hover:text-rose-500 cursor-pointer">Sales Ledger Ledger</li>
                <li class="hover:text-rose-500 cursor-pointer">Customer Monthly Sales</li>
                <li class="hover:text-rose-500 cursor-pointer">Dish Quantity Sales Report</li>
                <li class="hover:text-rose-500 cursor-pointer flex justify-between"><span>Complimentary Items</span> <span class="bg-slate-100 dark:bg-[#2A2A2A] border border-slate-200 dark:border-[#222222] text-[9px] px-1.5 py-0.5 rounded font-bold text-rose-500">NEW</span></li>
                <li class="hover:text-rose-500 cursor-pointer text-slate-400 dark:text-slate-500">Complimentary Add-Ons</li>
            </ul>
        </div>
    </div>
    @endif

    <!-- MODAL: ADD EXPENSE -->
    @if($showTxModal)
    <div class="fixed inset-0 z-50 flex items-center justify-end">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" wire:click="$set('showTxModal', false)"></div>
        <!-- Modal Card -->
        <div class="bg-white dark:bg-[#1A1A1A] border-l border-slate-200 dark:border-[#222222] h-full w-full max-w-lg shadow-2xl flex flex-col p-6 space-y-5 overflow-y-auto relative z-10">
            <div class="flex justify-between items-center border-b border-slate-200 dark:border-[#222222] pb-4">
                <h3 class="font-extrabold text-lg text-slate-800 dark:text-white">Add Expense</h3>
                <button wire:click="$set('showTxModal', false)" class="text-slate-500 dark:text-gray-400 hover:text-slate-800 dark:hover:text-white p-2.5 rounded-lg bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#222222] transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="space-y-4 text-left text-xs">
                <!-- Amount -->
                <div>
                    <label class="block font-bold text-slate-700 dark:text-gray-300 mb-1.5">Amount *</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-slate-400 dark:text-gray-400 font-semibold">Rs.</span>
                        <input wire:model="txAmount" type="number" class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#222222] text-slate-800 dark:text-white rounded-xl focus:ring-2 focus:ring-rose-500/25 focus:border-rose-500 outline-none font-bold text-sm" />
                    </div>
                    @error('txAmount') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Remarks -->
                <div>
                    <label class="block font-bold text-slate-700 dark:text-gray-300 mb-1.5">Remarks</label>
                    <input wire:model="txRemarks" type="text" placeholder="Enter Remarks" class="w-full px-4 py-3 bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#222222] text-slate-800 dark:text-white rounded-xl focus:ring-2 focus:ring-rose-500/25 focus:border-rose-500 outline-none font-medium" />
                </div>

                <!-- Account Head -->
                <div>
                    <label class="block font-bold text-slate-700 dark:text-gray-300 mb-1.5">Account Head *</label>
                    <select wire:model="txCategory" class="w-full px-4 py-3 bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#222222] text-slate-800 dark:text-white rounded-xl focus:ring-2 focus:ring-rose-500/25 focus:border-rose-500 outline-none font-medium">
                        <option value="Raw Materials">Raw Materials</option>
                        <option value="Salaries & Wages">Salaries & Wages</option>
                        <option value="Rent & Utilities">Rent & Utilities</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Office Expenses">Office Expenses</option>
                    </select>
                </div>

                <!-- Party Type Selector -->
                <div>
                    <label class="block font-bold text-slate-700 dark:text-gray-300 mb-1.5">Party Type</label>
                    <div class="grid grid-cols-3 gap-2.5">
                        <button wire:click="$set('txPartyType', 'supplier')" class="py-2.5 rounded-xl border text-xs font-bold flex items-center justify-center transition-all duration-200 active:scale-95 {{ $txPartyType === 'supplier' ? 'bg-rose-600 border-rose-500 text-white shadow-lg shadow-rose-950/20' : 'bg-slate-100 dark:bg-[#222222] border-slate-200 dark:border-[#222222] text-slate-600 dark:text-gray-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-[#2A2A2A]' }}">
                            <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            Supplier
                        </button>
                        <button wire:click="$set('txPartyType', 'staff')" class="py-2.5 rounded-xl border text-xs font-bold flex items-center justify-center transition-all duration-200 active:scale-95 {{ $txPartyType === 'staff' ? 'bg-rose-600 border-rose-500 text-white shadow-lg shadow-rose-950/20' : 'bg-slate-100 dark:bg-[#222222] border-slate-200 dark:border-[#222222] text-slate-600 dark:text-gray-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-[#2A2A2A]' }}">
                            <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            Staff
                        </button>
                        <button wire:click="$set('txPartyType', 'customer')" class="py-2.5 rounded-xl border text-xs font-bold flex items-center justify-center transition-all duration-200 active:scale-95 {{ $txPartyType === 'customer' ? 'bg-rose-600 border-rose-500 text-white shadow-lg shadow-rose-950/20' : 'bg-slate-100 dark:bg-[#222222] border-slate-200 dark:border-[#222222] text-slate-600 dark:text-gray-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-[#2A2A2A]' }}">
                            <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Customer
                        </button>
                    </div>
                </div>

                <!-- Party Name -->
                <div>
                    <label class="block font-bold text-slate-700 dark:text-gray-300 mb-1.5">Party Name</label>
                    <input wire:model="txPartyName" type="text" placeholder="Assign party to this expense" class="w-full px-4 py-3 bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#222222] text-slate-800 dark:text-white rounded-xl focus:ring-2 focus:ring-rose-500/25 focus:border-rose-500 outline-none font-medium" />
                </div>

                <!-- Payment Status Tabs -->
                <div>
                    <label class="block font-bold text-slate-700 dark:text-gray-300 mb-1.5">Payment Status</label>
                    <div class="grid grid-cols-2 gap-2.5">
                        <button wire:click="$set('txPaymentStatus', 'paid')" class="py-2.5 rounded-xl border text-xs font-bold flex items-center justify-center transition-all duration-200 active:scale-95 {{ $txPaymentStatus === 'paid' ? 'bg-rose-600 border-rose-500 text-white shadow-lg shadow-rose-950/20' : 'bg-slate-100 dark:bg-[#222222] border-slate-200 dark:border-[#222222] text-slate-600 dark:text-gray-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-[#2A2A2A]' }}">
                            <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Paid
                        </button>
                        <button wire:click="$set('txPaymentStatus', 'unpaid')" class="py-2.5 rounded-xl border text-xs font-bold flex items-center justify-center transition-all duration-200 active:scale-95 {{ $txPaymentStatus === 'unpaid' ? 'bg-rose-600 border-rose-500 text-white shadow-lg shadow-rose-950/20' : 'bg-slate-100 dark:bg-[#222222] border-slate-200 dark:border-[#222222] text-slate-600 dark:text-gray-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-[#2A2A2A]' }}">
                            <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Unpaid / Credit
                        </button>
                    </div>
                </div>

                @if($txPaymentStatus === 'paid')
                <!-- Payment Account -->
                <div>
                    <label class="block font-bold text-slate-700 dark:text-gray-300 mb-1.5">Payment Account *</label>
                    <select wire:model="txAccountId" class="w-full px-4 py-3 bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#222222] text-slate-800 dark:text-white rounded-xl focus:ring-2 focus:ring-rose-500/25 focus:border-rose-500 outline-none font-medium">
                        @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Ref Number & Date -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-gray-300 mb-1.5">Reference Number</label>
                        <input wire:model="txReferenceNumber" type="text" placeholder="e.g. Ref-1092" class="w-full px-4 py-3 bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#222222] text-slate-800 dark:text-white rounded-xl focus:ring-2 focus:ring-rose-500/25 focus:border-rose-500 outline-none font-medium" />
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-gray-300 mb-1.5">Transaction Date *</label>
                        <input wire:model="txDate" type="date" class="w-full px-4 py-3 bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#222222] text-slate-800 dark:text-white rounded-xl focus:ring-2 focus:ring-rose-500/25 focus:border-rose-500 outline-none font-medium" />
                    </div>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="pt-4 border-t border-slate-200 dark:border-[#222222] flex justify-end gap-3 bg-white dark:bg-[#1A1A1A]">
                <button wire:click="$set('showTxModal', false)" class="px-5 py-2.5 bg-slate-100 dark:bg-[#222222] border border-slate-200 dark:border-[#222222] hover:bg-slate-200 dark:hover:bg-[#2A2A2A] text-slate-700 dark:text-white font-bold rounded-xl text-xs transition-colors">Reset</button>
                <button wire:click="saveTransaction" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition-colors shadow-md shadow-rose-950/20">Save Expense</button>
            </div>
        </div>
    </div>
    @endif

    <!-- MODAL: SALES INVOICE -->
    @if($showSalesInvoiceModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" wire:click="$set('showSalesInvoiceModal', false)"></div>
        <!-- Modal Card -->
        <div class="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#222222] rounded-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden shadow-2xl flex flex-col relative z-10">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-[#222222] flex justify-between items-center bg-slate-50 dark:bg-[#1A1A1A]">
                <h3 class="font-extrabold text-base text-slate-800 dark:text-white">Create Manual Sales Invoice</h3>
                <button wire:click="$set('showSalesInvoiceModal', false)" class="text-slate-500 dark:text-gray-400 hover:text-slate-800 dark:hover:text-white p-2 bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#222222] rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-6 text-left text-xs">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-gray-300 mb-1.5">Customer</label>
                        <select wire:model="invoiceCustomerId" class="w-full px-4 py-3 bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#222222] text-slate-800 dark:text-white rounded-xl outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="">Select Customer (Walk-in)</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-gray-300 mb-1.5">TXN Date *</label>
                        <input wire:model="invoiceTxnDate" type="date" class="w-full px-4 py-3 bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#222222] text-slate-800 dark:text-white rounded-xl outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-gray-300 mb-1.5">Sales Staff</label>
                        <input wire:model="invoiceSalesStaff" type="text" placeholder="Enter Sales Staff name" class="w-full px-4 py-3 bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#222222] text-slate-800 dark:text-white rounded-xl outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                    </div>
                </div>

                <!-- Items List table -->
                <div class="space-y-3">
                    <h4 class="font-bold text-slate-600 dark:text-gray-400 uppercase tracking-wider">Items List</h4>
                    <div class="border border-slate-200 dark:border-[#222222] rounded-xl overflow-hidden shadow-inner bg-slate-50 dark:bg-[#111111]">
                        <table class="w-full text-left text-slate-700 dark:text-gray-300">
                            <thead>
                                <tr class="bg-slate-100/50 dark:bg-[#1A1A1A] border-b border-slate-200 dark:border-[#222222] text-[10px] uppercase font-bold text-slate-500 dark:text-gray-400">
                                    <th class="px-4 py-3 w-12">SN</th>
                                    <th class="px-4 py-3">Item Name *</th>
                                    <th class="px-4 py-3 w-28">Quantity</th>
                                    <th class="px-4 py-3 w-32">Rate (Rs.) *</th>
                                    <th class="px-4 py-3 w-36 text-right">Amount</th>
                                    <th class="px-4 py-3 w-16 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoiceItems as $index => $item)
                                <tr class="border-b border-slate-100 dark:border-[#222222]">
                                    <td class="px-4 py-3 font-semibold">{{ $index + 1 }}</td>
                                    <td class="px-4 py-2">
                                        <input type="text" placeholder="Enter Item Name" value="{{ $item['name'] }}" wire:input="updateInvoiceRow({{ $index }}, 'name', $event.target.value)" class="w-full px-3 py-2 bg-white dark:bg-[#222222] border border-slate-200 dark:border-[#222222] text-slate-800 dark:text-white rounded-lg focus:ring-1 focus:ring-emerald-500 outline-none" />
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" value="{{ $item['qty'] }}" wire:input="updateInvoiceRow({{ $index }}, 'qty', $event.target.value)" class="w-full px-3 py-2 bg-white dark:bg-[#222222] border border-slate-200 dark:border-[#222222] text-slate-800 dark:text-white rounded-lg focus:ring-1 focus:ring-emerald-500 outline-none" />
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" step="0.01" value="{{ $item['rate'] }}" wire:input="updateInvoiceRow({{ $index }}, 'rate', $event.target.value)" class="w-full px-3 py-2 bg-white dark:bg-[#222222] border border-slate-200 dark:border-[#222222] text-slate-800 dark:text-white rounded-lg focus:ring-1 focus:ring-emerald-500 outline-none" />
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-slate-800 dark:text-white">
                                        Rs. {{ number_format($item['amount'], 2) }}
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        <button wire:click="removeInvoiceRow({{ $index }})" class="p-1.5 text-slate-500 hover:text-red-500 hover:bg-red-500/10 rounded-lg">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="addInvoiceRow" class="px-4 py-2 bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#222222] hover:bg-slate-100 dark:hover:bg-[#2A2A2A] text-slate-800 dark:text-white font-bold rounded-lg transition-colors">+ Add Row</button>
                    </div>
                </div>

                <!-- Remarks & Payout Mode -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-200 dark:border-[#222222]">
                    <div class="space-y-4">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-gray-300 mb-1.5">Remarks</label>
                            <textarea wire:model="invoiceRemarks" rows="2" placeholder="Enter Remarks" class="w-full px-4 py-3 bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#222222] text-slate-800 dark:text-white rounded-xl outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-gray-300 mb-1.5">Payment Mode</label>
                            <div class="flex gap-2.5 flex-wrap">
                                @foreach(['cash' => ['Cash', 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'], 'card' => ['Card', 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'], 'nepal_pay' => ['Nepal Pay', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'], 'fonepay' => ['Fonepay', 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z'], 'bank_transfer' => ['Bank Transfer', 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4']] as $key => $data)
                                <button wire:click="$set('invoicePaymentMode', '{{ $key }}')" class="px-4 py-2.5 rounded-xl border text-xs font-bold flex items-center justify-center transition-all duration-200 active:scale-95 {{ $invoicePaymentMode === $key ? 'bg-emerald-600 border-emerald-500 text-white shadow-lg shadow-emerald-950/20' : 'bg-slate-100 dark:bg-[#222222] border-slate-200 dark:border-[#222222] text-slate-600 dark:text-gray-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-[#2A2A2A]' }}">
                                    <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $data[1] }}"></path></svg>
                                    {{ $data[0] }}
                                </button>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-gray-300 mb-1.5">Payment Status</label>
                            <div class="grid grid-cols-2 gap-2.5 w-60">
                                <button wire:click="$set('invoicePaymentStatus', 'paid')" class="py-2.5 rounded-xl border text-xs font-bold flex items-center justify-center transition-all duration-200 active:scale-95 {{ $invoicePaymentStatus === 'paid' ? 'bg-emerald-600 border-emerald-500 text-white shadow-lg shadow-emerald-950/20' : 'bg-slate-100 dark:bg-[#222222] border-slate-200 dark:border-[#222222] text-slate-600 dark:text-gray-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-[#2A2A2A]' }}">
                                    <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Paid
                                </button>
                                <button wire:click="$set('invoicePaymentStatus', 'unpaid')" class="py-2.5 rounded-xl border text-xs font-bold flex items-center justify-center transition-all duration-200 active:scale-95 {{ $invoicePaymentStatus === 'unpaid' ? 'bg-emerald-600 border-emerald-500 text-white shadow-lg shadow-emerald-950/20' : 'bg-slate-100 dark:bg-[#222222] border-slate-200 dark:border-[#222222] text-slate-600 dark:text-gray-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-[#2A2A2A]' }}">
                                    <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Credit
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Totals -->
                    <div class="bg-slate-50 dark:bg-[#1A1A1A] p-6 rounded-2xl border border-slate-200 dark:border-[#222222] flex flex-col justify-between space-y-4 h-fit shadow-sm dark:shadow-xl">
                        <div class="space-y-2.5 font-semibold text-slate-500 dark:text-gray-400">
                            <div class="flex justify-between"><span>Taxable Amount</span><span class="text-slate-800 dark:text-white">Rs. {{ number_format($invoiceTotal, 2) }}</span></div>
                            <div class="flex justify-between"><span>Total Amount</span><span class="text-slate-800 dark:text-white">Rs. {{ number_format($invoiceTotal, 2) }}</span></div>
                            <div class="flex justify-between border-t border-slate-200 dark:border-[#222222] pt-2 font-black text-slate-800 dark:text-white text-base"><span>Net Amount</span><span class="text-emerald-600 dark:text-emerald-450">Rs. {{ number_format($invoiceTotal, 2) }}</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-200 dark:border-[#222222] flex justify-end gap-3 bg-slate-50 dark:bg-[#1A1A1A]">
                <button wire:click="resetInvoiceForm" class="px-5 py-2.5 bg-slate-100 dark:bg-[#222222] border border-slate-200 dark:border-[#222222] hover:bg-slate-200 dark:hover:bg-[#2A2A2A] text-slate-700 dark:text-white font-bold rounded-xl text-xs transition-colors">Reset</button>
                <button wire:click="saveSalesInvoice" class="px-6 py-2.5 bg-emerald-650 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-colors shadow-md shadow-emerald-950/20">Save Sales Invoice</button>
            </div>
        </div>
    </div>
    @endif

    <!-- MODAL: CREATE ACCOUNT -->
    @if($showAccountModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" wire:click="$set('showAccountModal', false)"></div>
        <!-- Modal Card -->
        <div class="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#222222] rounded-2xl w-full max-w-lg shadow-2xl flex flex-col overflow-hidden relative z-10">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-[#222222] flex justify-between items-center bg-slate-50 dark:bg-[#1A1A1A]">
                <h3 class="font-bold text-base text-slate-800 dark:text-white">Create Accounts</h3>
                <button wire:click="$set('showAccountModal', false)" class="text-slate-500 dark:text-gray-400 hover:text-slate-800 dark:hover:text-white p-2 bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#222222] rounded-lg transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="p-6 space-y-4 text-left text-xs">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-gray-300 mb-1.5">Account Name *</label>
                    <input wire:model="accountName" type="text" placeholder="Enter Account Name" class="w-full px-4 py-3 bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#222222] text-slate-800 dark:text-white rounded-xl focus:ring-2 focus:ring-rose-500/25 focus:border-rose-500 outline-none font-medium" />
                    @error('accountName') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-gray-300 mb-1.5">Type</label>
                    <div class="flex gap-2 flex-wrap">
                        @foreach(['bank' => ['Bank', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'], 'cash' => ['Cash', 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'], 'wallet' => ['Digital Wallet', 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z'], 'personal' => ['Personal Account', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'], 'loan' => ['Loan Account', 'M9 8h6m-6 4h6m-6 4h6m1 5H6a2 2 0 01-2-2V4a2 2 0 012-2h12a2 2 0 012 2v15a2 2 0 01-2 2z']] as $key => $data)
                        <button wire:click="$set('accountType', '{{ $key }}')" class="px-4 py-2.5 rounded-xl border text-xs font-bold flex items-center justify-center transition-all duration-200 active:scale-95 {{ $accountType === $key ? 'bg-rose-600 border-rose-500 text-white shadow-lg shadow-rose-950/20' : 'bg-slate-100 dark:bg-[#222222] border-slate-200 dark:border-[#222222] text-slate-600 dark:text-gray-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-[#2A2A2A]' }}" type="button">
                            <svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $data[1] }}"></path></svg>
                            {{ $data[0] }}
                        </button>
                        @endforeach
                    </div>
                </div>

                @if($accountType === 'bank')
                <div>
                    <label class="block font-bold text-slate-700 dark:text-gray-300 mb-1.5">Bank Account Name *</label>
                    <input wire:model="bankName" type="text" placeholder="Enter Bank Account Name" class="w-full px-4 py-3 bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#222222] text-slate-800 dark:text-white rounded-xl focus:ring-2 focus:ring-rose-500/25 focus:border-rose-500 outline-none font-medium" />
                    @error('bankName') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-gray-300 mb-1.5">Bank Account Number *</label>
                    <input wire:model="accountNumber" type="text" placeholder="Enter Bank Account Number" class="w-full px-4 py-3 bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#222222] text-slate-800 dark:text-white rounded-xl focus:ring-2 focus:ring-rose-500/25 focus:border-rose-500 outline-none font-medium" />
                    @error('accountNumber') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                </div>
                @endif

                <div>
                    <label class="block font-bold text-slate-700 dark:text-gray-300 mb-1.5">Opening Balance</label>
                    <input wire:model="accountBalance" type="number" placeholder="Rs. 0" class="w-full px-4 py-3 bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#222222] text-slate-800 dark:text-white rounded-xl focus:ring-2 focus:ring-rose-500/25 focus:border-rose-500 outline-none font-medium" />
                    @error('accountBalance') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-gray-300 mb-1.5">Description</label>
                    <textarea wire:model="accountDescription" rows="2" placeholder="Enter Description" class="w-full px-4 py-3 bg-slate-50 dark:bg-[#222222] border border-slate-200 dark:border-[#222222] text-slate-800 dark:text-white rounded-xl focus:ring-2 focus:ring-rose-500/25 focus:border-rose-500 outline-none font-medium"></textarea>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-200 dark:border-[#222222] flex justify-end gap-3 bg-slate-50 dark:bg-[#1A1A1A]">
                <button wire:click="$set('showAccountModal', false)" class="px-5 py-2.5 bg-slate-100 dark:bg-[#222222] border border-slate-200 dark:border-[#222222] hover:bg-slate-200 dark:hover:bg-[#2A2A2A] text-slate-700 dark:text-white font-bold rounded-xl text-xs transition-colors" type="button">Reset</button>
                <button wire:click="saveAccount" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition-colors shadow-md shadow-rose-950/20" type="button">Save Accounts</button>
            </div>
        </div>
    </div>
    @endif

    <!-- Print Styles -->
    <style>
        @media print {
            .print-hide, nav, aside, header, [wire\:id] > div:first-child > div:first-child button[wire\:click] {
                display: none !important;
            }
            body, .dark\:bg-slate-900, .dark\:bg-\[\#0D0D0D\] {
                background: white !important;
                color: black !important;
            }
            .dark\:bg-\[\#1A1A1A\], .dark\:bg-\[\#222222\], .dark\:bg-\[\#111111\] {
                background: white !important;
            }
            .dark\:text-white, .dark\:text-gray-300, .dark\:text-gray-400 {
                color: black !important;
            }
            .dark\:border-\[\#222222\] {
                border-color: #ddd !important;
            }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
        }
    </style>
</div>
