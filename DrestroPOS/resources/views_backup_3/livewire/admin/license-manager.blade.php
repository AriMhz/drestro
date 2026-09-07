<div class="p-6 sm:p-8">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
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
                <a href="{{ config('app.env') === 'production' ? 'https://drestro.com/dashboard/billing' : 'https://drestro.com/dashboard/billing' }}?machine_id={{ \App\Services\LicenseManager::getMachineId() }}" target="_blank" 
                   class="w-full md:w-auto flex items-center justify-center gap-2 px-6 py-3 bg-slate-900 dark:bg-[#1A2234] hover:bg-slate-800 dark:hover:bg-[#232D45] text-white text-sm font-bold rounded-xl transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Renew Plan
                </a>
                <a href="{{ config('app.env') === 'production' ? 'https://drestro.com/pricing' : 'https://drestro.com/pricing' }}" target="_blank" 
                   class="w-full md:w-auto flex items-center justify-center gap-2 px-6 py-3 bg-transparent border border-slate-300 dark:border-neutral-700 hover:border-slate-400 dark:hover:border-neutral-500 text-slate-700 dark:text-white text-sm font-bold rounded-xl transition-colors">
                    Change Plan
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </div>
        
    </div>
</div>
