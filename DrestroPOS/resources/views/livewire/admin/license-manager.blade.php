<div class="p-6 sm:p-8">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="mb-8 hidden lg:block">
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white mb-2">System Status</h1>
            <p class="text-slate-500 dark:text-neutral-400">Manage your subscription, view system status, and upgrade your plan.</p>
        </div>

        <!-- License Card -->
        <div class="bg-white dark:bg-[#111111] border border-slate-200 dark:border-neutral-800 rounded-2xl relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between p-6 sm:p-8 gap-6 shadow-sm">
            <!-- Left Red Accent -->
            <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-red-500"></div>

            <div class="pl-2">
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white mb-3">{{ $activeLicense['plan'] ?? 'Activation Required' }}</h2>
                <p class="text-slate-500 dark:text-neutral-400 text-sm mb-6 max-w-xl">
                    @if(isset($activeLicense['plan']) && $activeLicense['plan'] === 'Premium')
                        Includes full access to Online Web POS, Waiter Sync, and Kitchen Display System.
                    @else
                        Your current Drestro POS system license and offline machine status.
                    @endif
                </p>
                
                <div class="inline-flex items-center gap-2 bg-slate-50 dark:bg-white text-slate-700 dark:text-slate-900 px-4 py-2.5 rounded-lg text-sm font-semibold border border-slate-200 dark:border-white shadow-sm">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    @if(isset($activeLicense['expires_at']))
                        Active until {{ \Carbon\Carbon::parse($activeLicense['expires_at'])->format('n/j/Y') }}
                    @elseif(isset($activeLicense['plan']) && $activeLicense['plan'] !== 'Activation Required')
                        Lifetime License Active
                    @else
                        Activation Needed
                    @endif
                </div>
            </div>

            <div class="flex flex-col gap-3 w-full md:w-auto">
                <a href="https://drestro.com/dashboard/billing/plans" target="_blank" 
                   class="w-full md:w-auto flex items-center justify-center gap-2 px-6 py-3 bg-slate-900 dark:bg-[#1A2234] hover:bg-slate-800 dark:hover:bg-[#232D45] text-white text-sm font-bold rounded-xl transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Renew Plan
                </a>
                <a href="https://drestro.com/dashboard/billing/plans" target="_blank" 
                   class="w-full md:w-auto flex items-center justify-center gap-2 px-6 py-3 bg-transparent border border-slate-300 dark:border-neutral-700 hover:border-slate-400 dark:hover:border-neutral-500 text-slate-700 dark:text-white text-sm font-bold rounded-xl transition-colors">
                    Change Plan
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
                
                <div class="mt-2 text-center text-xs text-slate-500 dark:text-neutral-400 border-t border-slate-100 dark:border-neutral-800 pt-3">
                    Already renewed?<br>
                    <a href="https://drestro.com/dashboard/billing/plans" target="_blank" class="text-emerald-600 dark:text-emerald-500 font-bold hover:underline">Launch POS from DRestro.com</a><br>
                    to sync your active license.
                </div>
            </div>
        </div>
        
        <!-- License Details Section -->
        <div class="mt-8 bg-white dark:bg-[#111111] border border-slate-200 dark:border-neutral-800 rounded-2xl p-6 sm:p-8 shadow-sm space-y-6">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-neutral-800 pb-3">License & Limit Parameters</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Expiry Date -->
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-slate-400 dark:text-neutral-500 uppercase tracking-wider">Subscription End Date</span>
                    <p class="text-sm font-bold text-slate-800 dark:text-gray-200">
                        @if(isset($activeLicense['expires_at']))
                            {{ \Carbon\Carbon::parse($activeLicense['expires_at'])->format('F j, Y') }} 
                            <span class="text-xs font-semibold text-slate-500 dark:text-neutral-400">
                                ({{ \Carbon\Carbon::parse($activeLicense['expires_at'])->diffForHumans() }})
                            </span>
                        @else
                            Lifetime Access (No Expiry Date)
                        @endif
                    </p>
                </div>

                <!-- Activation Date -->
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-slate-400 dark:text-neutral-500 uppercase tracking-wider">Activation / Purchase Date</span>
                    <p class="text-sm font-bold text-slate-800 dark:text-gray-200">
                        @if(isset($activeLicense['activated_at']))
                            {{ \Carbon\Carbon::parse($activeLicense['activated_at'])->format('F j, Y') }}
                        @else
                            N/A
                        @endif
                    </p>
                </div>

                <!-- License Key -->
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-slate-400 dark:text-neutral-500 uppercase tracking-wider">License Key</span>
                    <p class="text-sm font-mono font-bold text-slate-800 dark:text-gray-200 tracking-wider">
                        {{ current_restaurant()->license_key ?? 'N/A' }}
                    </p>
                </div>

                <!-- Terminal limits -->
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-slate-400 dark:text-neutral-500 uppercase tracking-wider">Max Registered Tables</span>
                    <p class="text-sm font-bold text-slate-800 dark:text-gray-200">
                        {{ isset($activeLicense['limits']['tables']) && $activeLicense['limits']['tables'] > 0 ? $activeLicense['limits']['tables'] . ' Tables' : 'Unlimited' }}
                    </p>
                </div>

                <div class="space-y-1">
                    <span class="text-xs font-semibold text-slate-400 dark:text-neutral-500 uppercase tracking-wider">Max Active Staff</span>
                    <p class="text-sm font-bold text-slate-800 dark:text-gray-200">
                        {{ isset($activeLicense['limits']['users']) && $activeLicense['limits']['users'] > 0 ? $activeLicense['limits']['users'] . ' Members' : 'Unlimited' }}
                    </p>
                </div>

                <div class="space-y-1">
                    <span class="text-xs font-semibold text-slate-400 dark:text-neutral-500 uppercase tracking-wider">Max Menu Items</span>
                    <p class="text-sm font-bold text-slate-800 dark:text-gray-200">
                        {{ isset($activeLicense['limits']['items']) && $activeLicense['limits']['items'] > 0 ? $activeLicense['limits']['items'] . ' Items' : 'Unlimited' }}
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>
