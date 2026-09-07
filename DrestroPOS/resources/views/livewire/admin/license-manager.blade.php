<div class="p-6 sm:p-8">
    <div class="max-w-6xl mx-auto space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">License & Subscription</h1>
                <p class="text-slate-500 dark:text-neutral-400 text-sm mt-1">Monitor real-time system quota, plan features, and terminal activation.</p>
            </div>
            <div class="flex items-center gap-3">
                <button wire:click="syncLicense" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-slate-700 dark:text-gray-200 text-xs font-bold rounded-xl transition-all shadow-sm">
                    <svg wire:loading.class="animate-spin" class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>Sync with Server</span>
                </button>
                <a href="https://drestro.com/dashboard/billing/plans" target="_blank"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-red-500/20">
                    <span>Manage Subscription</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </div>

        @if (session()->has('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 rounded-xl text-emerald-800 dark:text-emerald-300 text-sm font-medium flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="p-4 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800/50 rounded-xl text-red-800 dark:text-red-300 text-sm font-medium flex items-center gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Main Plan Overview Card -->
        <div class="bg-white dark:bg-[#111111] border border-slate-200 dark:border-neutral-800 rounded-3xl relative overflow-hidden p-6 sm:p-8 shadow-sm">
            <div class="absolute left-0 top-0 bottom-0 w-2 bg-gradient-to-b from-red-500 to-rose-600"></div>

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pl-2">
                <div>
                    <div class="flex items-center gap-3 mb-2 flex-wrap">
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            {{ $licenseData['plan'] ?? 'Basic / Trial' }}
                        </h2>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                            {{ ($licenseData['status'] ?? 'active') === 'active' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-800' : 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-300 dark:border-amber-800' }}">
                            <span class="w-2 h-2 rounded-full {{ ($licenseData['status'] ?? 'active') === 'active' ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500' }}"></span>
                            {{ $licenseData['status'] ?? 'Active' }}
                        </span>
                    </div>

                    <p class="text-slate-500 dark:text-neutral-400 text-sm max-w-xl">
                        @if(isset($licenseData['plan']) && str_contains(strtolower($licenseData['plan']), 'platinum'))
                            Platinum Tier: Enterprise access with unlimited multi-terminal POS, KDS, automated cloud syncing, and priority support.
                        @elseif(isset($licenseData['plan']) && str_contains(strtolower($licenseData['plan']), 'premium'))
                            Premium Tier: Full access to POS Billing, Kitchen Display, Waiter App, and Inventory Analytics.
                        @else
                            Standard Tier: POS operations with offline support and basic inventory management.
                        @endif
                    </p>

                    <div class="mt-4 flex items-center gap-4 flex-wrap text-xs text-slate-600 dark:text-neutral-300 font-semibold">
                        <div class="flex items-center gap-1.5 bg-slate-100 dark:bg-neutral-800/80 px-3 py-1.5 rounded-lg">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span>
                                @if(!empty($licenseData['expires_at']))
                                    Expires: {{ \Carbon\Carbon::parse($licenseData['expires_at'])->format('M d, Y') }} ({{ \Carbon\Carbon::parse($licenseData['expires_at'])->diffForHumans() }})
                                @else
                                    Valid: Lifetime Active
                                @endif
                            </span>
                        </div>

                        <div class="flex items-center gap-1.5 bg-slate-100 dark:bg-neutral-800/80 px-3 py-1.5 rounded-lg">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            <span class="font-mono">{{ substr($machineId, 0, 16) }}...</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row lg:flex-col gap-3 min-w-[200px]">
                    <a href="https://drestro.com/dashboard/billing/plans" target="_blank"
                       class="w-full text-center px-5 py-3 bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-slate-900 text-xs font-bold rounded-xl transition-colors shadow-sm">
                        Upgrade or Renew
                    </a>
                    <a href="https://drestro.com/contact" target="_blank"
                       class="w-full text-center px-5 py-3 bg-transparent border border-slate-300 dark:border-neutral-700 hover:border-slate-400 dark:hover:border-neutral-500 text-slate-700 dark:text-neutral-200 text-xs font-bold rounded-xl transition-colors">
                        Contact Support
                    </a>
                </div>
            </div>
        </div>

        <!-- Real-Time Resource Usage & Capacity Grid -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-900 dark:text-white uppercase tracking-wider text-xs">Plan Resource Usage & Limits</h3>
                <span class="text-xs text-slate-400 dark:text-neutral-500 font-medium">Updated live from local POS store</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Tables -->
                <div class="bg-white dark:bg-[#111111] border border-slate-200 dark:border-neutral-800 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Tables</span>
                        <div class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-950/50 text-red-500 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1.5 mb-2">
                        <span class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $usageStats['tables']['used'] ?? 0 }}</span>
                        <span class="text-xs text-slate-400 font-medium">/ {{ ($usageStats['tables']['is_unlimited'] ?? true) ? '∞' : ($usageStats['tables']['limit'] ?? 'Unlimited') }}</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-neutral-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-red-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ ($usageStats['tables']['is_unlimited'] ?? true) ? '10%' : ($usageStats['tables']['percent'] . '%') }}"></div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2 font-medium">
                        {{ ($usageStats['tables']['is_unlimited'] ?? true) ? 'Unlimited tables permitted' : ($usageStats['tables']['percent'] . '% quota used') }}
                    </p>
                </div>

                <!-- Staff Users -->
                <div class="bg-white dark:bg-[#111111] border border-slate-200 dark:border-neutral-800 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Staff Accounts</span>
                        <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/50 text-blue-500 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1.5 mb-2">
                        <span class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $usageStats['users']['used'] ?? 0 }}</span>
                        <span class="text-xs text-slate-400 font-medium">/ {{ ($usageStats['users']['is_unlimited'] ?? true) ? '∞' : ($usageStats['users']['limit'] ?? 'Unlimited') }}</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-neutral-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-blue-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ ($usageStats['users']['is_unlimited'] ?? true) ? '10%' : ($usageStats['users']['percent'] . '%') }}"></div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2 font-medium">
                        {{ ($usageStats['users']['is_unlimited'] ?? true) ? 'Unlimited staff members' : ($usageStats['users']['percent'] . '% quota used') }}
                    </p>
                </div>

                <!-- Menu Items -->
                <div class="bg-white dark:bg-[#111111] border border-slate-200 dark:border-neutral-800 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Menu Items</span>
                        <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/50 text-amber-500 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1.5 mb-2">
                        <span class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $usageStats['items']['used'] ?? 0 }}</span>
                        <span class="text-xs text-slate-400 font-medium">/ {{ ($usageStats['items']['is_unlimited'] ?? true) ? '∞' : ($usageStats['items']['limit'] ?? 'Unlimited') }}</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-neutral-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-amber-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ ($usageStats['items']['is_unlimited'] ?? true) ? '10%' : ($usageStats['items']['percent'] . '%') }}"></div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2 font-medium">
                        {{ ($usageStats['items']['is_unlimited'] ?? true) ? 'Unlimited food & beverage items' : ($usageStats['items']['percent'] . '% quota used') }}
                    </p>
                </div>

                <!-- Total Orders -->
                <div class="bg-white dark:bg-[#111111] border border-slate-200 dark:border-neutral-800 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Total Invoices</span>
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-500 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1.5 mb-2">
                        <span class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $usageStats['orders']['used'] ?? 0 }}</span>
                        <span class="text-xs text-slate-400 font-medium">/ {{ ($usageStats['orders']['is_unlimited'] ?? true) ? '∞' : ($usageStats['orders']['limit'] ?? 'Unlimited') }}</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-neutral-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ ($usageStats['orders']['is_unlimited'] ?? true) ? '10%' : ($usageStats['orders']['percent'] . '%') }}"></div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-2 font-medium">
                        Processed invoices & orders
                    </p>
                </div>
            </div>
        </div>

        <!-- Manual Activation Box -->
        <div class="bg-white dark:bg-[#111111] border border-slate-200 dark:border-neutral-800 rounded-3xl p-6 sm:p-8 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Activate / Update License Key</h3>
            <p class="text-slate-500 dark:text-neutral-400 text-xs mb-6">Enter the 16 or 24-character license key issued from your Drestro billing portal to bind this POS terminal.</p>

            <form wire:submit.prevent="activateLicense" class="flex flex-col sm:flex-row gap-3 max-w-2xl">
                <input type="text" wire:model="licenseKey" placeholder="e.g. DRESTRO-PREM-XXXX-XXXX"
                       class="flex-1 px-4 py-3 bg-slate-50 dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 rounded-xl text-slate-900 dark:text-white font-mono text-sm uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                <button type="submit" wire:loading.attr="disabled"
                        class="px-6 py-3 bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-slate-900 text-xs font-bold rounded-xl transition-all shadow-sm flex items-center justify-center gap-2">
                    <span wire:loading.remove>Activate Key</span>
                    <span wire:loading>Validating...</span>
                </button>
            </form>
        </div>
    </div>
</div>
