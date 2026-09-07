<div class="space-y-6 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8">
    <style>
        /* CSS overrides for settings dark/light contrast correctness */
        .settings-card {
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
            color: #1e293b !important;
        }
        .dark .settings-card {
            background-color: #1a1a1a !important;
            border-color: #2b2b2b !important;
            color: #ffffff !important;
        }

        .settings-card-header {
            background-color: #f8fafc !important;
            border-bottom: 1px solid #e2e8f0 !important;
            color: #1e293b !important;
        }
        .dark .settings-card-header {
            background-color: #141414 !important;
            border-bottom: 1px solid #2b2b2b !important;
            color: #ffffff !important;
        }

        .settings-inner-box {
            background-color: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            color: #1e293b !important;
        }
        .dark .settings-inner-box {
            background-color: #141414 !important;
            border: 1px solid #2b2b2b !important;
            color: #ffffff !important;
        }

        .settings-input {
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            color: #0f172a !important;
        }
        .dark .settings-input {
            background-color: #111111 !important;
            border: 1px solid #333333 !important;
            color: #ffffff !important;
        }
        .settings-input:focus {
            border-color: #E53935 !important;
            outline: none;
        }

        .settings-label {
            color: #334155 !important;
            font-weight: 600;
        }
        .dark .settings-label {
            color: #cbd5e1 !important;
        }

        .settings-caption {
            color: #64748b !important;
        }
        .dark .settings-caption {
            color: #94a3b8 !important;
        }

        .badge-pill {
            padding: 0.25rem 0.625rem;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-radius: 9999px;
        }

        /* Specific badge colors */
        .badge-identity { background-color: #ffe4e6 !important; color: #e11d48 !important; }
        .dark .badge-identity { background-color: rgba(225, 29, 72, 0.2) !important; color: #fda4af !important; }

        .badge-tax { background-color: #d1fae5 !important; color: #059669 !important; }
        .dark .badge-tax { background-color: rgba(5, 150, 105, 0.2) !important; color: #6ee7b7 !important; }

        .badge-qr { background-color: #dbeafe !important; color: #2563eb !important; }
        .dark .badge-qr { background-color: rgba(37, 99, 235, 0.2) !important; color: #93c5fd !important; }

        .badge-gov { background-color: #ccfbf1 !important; color: #0d9488 !important; }
        .dark .badge-gov { background-color: rgba(13, 148, 136, 0.2) !important; color: #5eead4 !important; }

        .badge-peripherals { background-color: #fef3c7 !important; color: #d97706 !important; }
        .dark .badge-peripherals { background-color: rgba(217, 119, 6, 0.2) !important; color: #fcd34d !important; }

        .badge-network { background-color: #cffafe !important; color: #0891b2 !important; }
        .dark .badge-network { background-color: rgba(8, 145, 178, 0.2) !important; color: #67e8f9 !important; }

        .badge-security { background-color: #e0e7ff !important; color: #4f46e5 !important; }
        .dark .badge-security { background-color: rgba(79, 70, 229, 0.2) !important; color: #a5b4fc !important; }

        .badge-help { background-color: #f3e8ff !important; color: #7c3aed !important; }
        .dark .badge-help { background-color: rgba(124, 58, 237, 0.2) !important; color: #c084fc !important; }

        .badge-subscription { background-color: #ffedd5 !important; color: #ea580c !important; }
        .dark .badge-subscription { background-color: rgba(234, 88, 12, 0.2) !important; color: #ffb74d !important; }

        .badge-inventory { background-color: #e2e8f0 !important; color: #475569 !important; }
        .dark .badge-inventory { background-color: rgba(71, 85, 105, 0.2) !important; color: #cbd5e1 !important; }

        .badge-danger { background-color: #fee2e2 !important; color: #dc2626 !important; }
        .dark .badge-danger { background-color: rgba(220, 38, 38, 0.2) !important; color: #fca5a5 !important; }
    </style>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            @if($activeTab !== 'list')
            <button wire:click="$set('activeTab', 'list')" class="print-hide p-2.5 bg-white dark:bg-[#1A1A1A] hover:bg-slate-50 dark:hover:bg-[#222222] border border-slate-200 dark:border-[#222222] text-slate-700 dark:text-gray-300 rounded-xl transition-all shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </button>
            @endif
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800 dark:text-white tracking-tight">
                    @if($activeTab === 'list') Restaurant Settings
                    @elseif($activeTab === 'general') General Profile Settings
                    @elseif($activeTab === 'billing') Billing & Tax Settings
                    @elseif($activeTab === 'payment') Payment QR Codes
                    @elseif($activeTab === 'ebilling') Nepal E-Billing Sync
                    @elseif($activeTab === 'hardware') Hardware & Printer Routing
                    @elseif($activeTab === 'connection') Device Wireless Login
                    @elseif($activeTab === 'backup') System Data Backup
                    @elseif($activeTab === 'support') Priority Help Desk
                    @elseif($activeTab === 'license') License Subscription
                    @elseif($activeTab === 'ordered-goods') Terminal Inventories
                    @elseif($activeTab === 'danger') Danger Zone
                    @endif
                </h1>
                <p class="text-sm text-slate-505 dark:text-neutral-400 mt-1">
                    @if($activeTab === 'list') Select a configuration card below to customize your restaurant setup.
                    @else Active Workspace: {{ ucwords(str_replace('-', ' ', $activeTab)) }}.
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if(in_array($activeTab, ['general', 'billing', 'payment', 'ebilling', 'hardware']))
            <button form="settings-form" type="submit" class="print-hide px-5 py-2.5 bg-[#E53935] hover:bg-red-750 text-white font-extrabold rounded-xl shadow-lg shadow-red-950/20 active:scale-95 transition-all text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                SAVE SETTINGS
            </button>
            @endif
        </div>
    </div>

    <!-- Success Slide-in Toast Notification -->
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
            <p class="text-xs text-slate-505 mt-0.5">Settings updated successfully!</p>
        </div>
        <button @click="show = false" class="text-slate-400 hover:text-slate-600 rounded-lg p-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    <!-- MAIN SETTINGS GRID DIRECTORY -->
    @if($activeTab === 'list')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- General Profile -->
        <div wire:click="$set('activeTab', 'general')" class="settings-card border rounded-3xl p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer flex flex-col justify-between group">
            <div>
                <div class="flex justify-between items-start">
                    <div class="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-555 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <span class="badge-pill badge-identity">Identity</span>
                </div>
                <h3 class="font-extrabold text-slate-800 dark:text-white text-lg mt-5 group-hover:text-rose-600 dark:group-hover:text-rose-400 transition-colors">General Profile</h3>
                <p class="text-xs settings-caption mt-2 leading-relaxed">Customize your brand logo, tagline, address details, contact info and supervisor passwords.</p>
            </div>
            <div class="mt-8 flex items-center gap-1.5 text-xs font-black uppercase text-slate-655 dark:text-slate-400 group-hover:text-rose-650 dark:group-hover:text-rose-400 transition-colors">
                Configure Profile
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
            </div>
        </div>

        <!-- Billing & Tax -->
        <div wire:click="$set('activeTab', 'billing')" class="settings-card border rounded-3xl p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer flex flex-col justify-between group">
            <div>
                <div class="flex justify-between items-start">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-500 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2-2v14a2 2 0 002 2z"></path></svg>
                    </div>
                    <span class="badge-pill badge-tax">Tax & Rates</span>
                </div>
                <h3 class="font-extrabold text-slate-800 dark:text-white text-lg mt-5 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">Billing & Tax</h3>
                <p class="text-xs settings-caption mt-2 leading-relaxed">Configure regional currency, PAN/VAT registration numbers, calendar date systems, and global VAT or service charges.</p>
            </div>
            <div class="mt-8 flex items-center gap-1.5 text-xs font-black uppercase text-slate-655 dark:text-slate-400 group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">
                Configure Tax Rules
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
            </div>
        </div>

        <!-- Payment QRs -->
        <div wire:click="$set('activeTab', 'payment')" class="settings-card border rounded-3xl p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer flex flex-col justify-between group">
            <div>
                <div class="flex justify-between items-start">
                    <div class="w-12 h-12 rounded-2xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-500 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h.01M16 20h2M12 20h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    </div>
                    <span class="badge-pill badge-qr">QR Codes</span>
                </div>
                <h3 class="font-extrabold text-slate-800 dark:text-white text-lg mt-5 group-hover:text-blue-700 dark:group-hover:text-blue-400 transition-colors">Payment QRs</h3>
                <p class="text-xs settings-caption mt-2 leading-relaxed">Link mobile wallets. Upload custom scan codes for eSewa, Khalti, Fonepay, or direct bank transfer receipts.</p>
            </div>
            <div class="mt-8 flex items-center gap-1.5 text-xs font-black uppercase text-slate-655 dark:text-slate-400 group-hover:text-blue-700 dark:group-hover:text-blue-400 transition-colors">
                Setup Wallet Codes
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
            </div>
        </div>

        <!-- Nepal E-Billing (Disabled for now) -->
        {{--
        <div wire:click="$set('activeTab', 'ebilling')" class="settings-card border rounded-3xl p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer flex flex-col justify-between group">
            <div>
                <div class="flex justify-between items-start">
                    <div class="w-12 h-12 rounded-2xl bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-500 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <span class="badge-pill badge-gov">Government API</span>
                </div>
                <h3 class="font-extrabold text-slate-800 dark:text-white text-lg mt-5 group-hover:text-teal-700 dark:group-hover:text-teal-400 transition-colors">Nepal E-Billing</h3>
                <p class="text-xs settings-caption mt-2 leading-relaxed">Sync sales directly to remote governmental servers. Configure API keys, tenant domains, and connection tests.</p>
            </div>
            <div class="mt-8 flex items-center gap-1.5 text-xs font-black uppercase text-slate-655 dark:text-slate-400 group-hover:text-teal-700 dark:group-hover:text-teal-400 transition-colors">
                Configure IRD API
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
            </div>
        </div>
        --}}

        <!-- Hardware & Printers -->
        <div wire:click="$set('activeTab', 'hardware')" class="settings-card border rounded-3xl p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer flex flex-col justify-between group">
            <div>
                <div class="flex justify-between items-start">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-550 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    </div>
                    <span class="badge-pill badge-peripherals">Peripherals</span>
                </div>
                <h3 class="font-extrabold text-slate-800 dark:text-white text-lg mt-5 group-hover:text-amber-700 dark:group-hover:text-amber-450 transition-colors">Hardware & Printers</h3>
                <p class="text-xs settings-caption mt-2 leading-relaxed">Setup local ESC/POS network thermal printer sockets, USB shared drivers, and smart combined or separate KOT routing.</p>
            </div>
            <div class="mt-8 flex items-center gap-1.5 text-xs font-black uppercase text-slate-655 dark:text-slate-400 group-hover:text-amber-700 dark:group-hover:text-amber-450 transition-colors">
                Configure Printers
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
            </div>
        </div>

        <!-- Device Connection -->
        <div wire:click="$set('activeTab', 'connection')" class="settings-card border rounded-3xl p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer flex flex-col justify-between group">
            <div>
                <div class="flex justify-between items-start">
                    <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-555 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    </div>
                    <span class="badge-pill badge-network">Network</span>
                </div>
                <h3 class="font-extrabold text-slate-800 dark:text-white text-lg mt-5 group-hover:text-cyan-700 dark:group-hover:text-cyan-400 transition-colors">Device Connection</h3>
                <p class="text-xs settings-caption mt-2 leading-relaxed">Map secondary tablet logins. View your router local IP address and generate staff dashboard pairing codes.</p>
            </div>
            <div class="mt-8 flex items-center gap-1.5 text-xs font-black uppercase text-slate-655 dark:text-slate-400 group-hover:text-cyan-700 dark:group-hover:text-cyan-400 transition-colors">
                Connect Terminals
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
            </div>
        </div>

        <!-- Backup & Restore -->
        <div wire:click="$set('activeTab', 'backup')" class="settings-card border rounded-3xl p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer flex flex-col justify-between group">
            <div>
                <div class="flex justify-between items-start">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-555 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    </div>
                    <span class="badge-pill badge-security">Security</span>
                </div>
                <h3 class="font-extrabold text-slate-800 dark:text-white text-lg mt-5 group-hover:text-indigo-700 dark:group-hover:text-indigo-400 transition-colors">Backup & Restore</h3>
                <p class="text-xs settings-caption mt-2 leading-relaxed">Save database safety states. Download local `.sqlite` logs, restore backups, or export database states to USB pendrives.</p>
            </div>
            <div class="mt-8 flex items-center gap-1.5 text-xs font-black uppercase text-slate-655 dark:text-slate-400 group-hover:text-indigo-700 dark:group-hover:text-indigo-400 transition-colors">
                Manage Backups
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
            </div>
        </div>

        <!-- Priority Support -->
        <div wire:click="$set('activeTab', 'support')" class="settings-card border rounded-3xl p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer flex flex-col justify-between group">
            <div>
                <div class="flex justify-between items-start">
                    <div class="w-12 h-12 rounded-2xl bg-violet-500/10 border border-violet-500/20 flex items-center justify-center text-violet-500 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <span class="badge-pill badge-help">Help</span>
                </div>
                <h3 class="font-extrabold text-slate-800 dark:text-white text-lg mt-5 group-hover:text-violet-755 dark:group-hover:text-violet-400 transition-colors">Priority Support</h3>
                <p class="text-xs settings-caption mt-2 leading-relaxed">Need technical help? Directly submit priority ticket requests, request features, or contact developers directly.</p>
            </div>
            <div class="mt-8 flex items-center gap-1.5 text-xs font-black uppercase text-slate-655 dark:text-slate-400 group-hover:text-violet-755 dark:group-hover:text-violet-400 transition-colors">
                Contact Developer
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
            </div>
        </div>

        <!-- License Manager -->
        <div wire:click="$set('activeTab', 'license')" class="settings-card border rounded-3xl p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer flex flex-col justify-between group">
            <div>
                <div class="flex justify-between items-start">
                    <div class="w-12 h-12 rounded-2xl bg-orange-500/10 border border-orange-500/20 flex items-center justify-center text-orange-500 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <span class="badge-pill badge-subscription">Subscription</span>
                </div>
                <h3 class="font-extrabold text-slate-800 dark:text-white text-lg mt-5 group-hover:text-orange-700 dark:group-hover:text-orange-400 transition-colors">License Manager</h3>
                <p class="text-xs settings-caption mt-2 leading-relaxed">Manage system activations. Paste product verification keys, validate limits, and view your current active plans.</p>
            </div>
            <div class="mt-8 flex items-center gap-1.5 text-xs font-black uppercase text-slate-655 dark:text-slate-400 group-hover:text-orange-700 dark:group-hover:text-orange-400 transition-colors">
                Validate License
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
            </div>
        </div>

        <!-- Ordered Goods -->
        <div wire:click="$set('activeTab', 'ordered-goods')" class="settings-card border rounded-3xl p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer flex flex-col justify-between group">
            <div>
                <div class="flex justify-between items-start">
                    <div class="w-12 h-12 rounded-2xl bg-sky-500/10 border border-sky-500/20 flex items-center justify-center text-sky-500 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                    </div>
                    <span class="badge-pill badge-inventory">Inventory</span>
                </div>
                <h3 class="font-extrabold text-slate-800 dark:text-white text-lg mt-5 group-hover:text-sky-700 dark:group-hover:text-sky-400 transition-colors">Ordered Goods</h3>
                <p class="text-xs settings-caption mt-2 leading-relaxed">View hardware inventories delivered to your location. Monitor POS terminal supplies and accessories arrival.</p>
            </div>
            <div class="mt-8 flex items-center gap-1.5 text-xs font-black uppercase text-slate-655 dark:text-slate-400 group-hover:text-sky-700 dark:group-hover:text-sky-400 transition-colors">
                View Shipments
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
            </div>
        </div>

        @if(request()->query('show_destructive_tools') === 'yes_support_only')
        <!-- Danger Zone -->
        <div wire:click="$set('activeTab', 'danger')" class="settings-card border rounded-3xl p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer flex flex-col justify-between group">
            <div>
                <div class="flex justify-between items-start">
                    <div class="w-12 h-12 rounded-2xl bg-red-500/10 border border-red-500/20 flex items-center justify-center text-red-500 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <span class="badge-pill badge-danger">Danger</span>
                </div>
                <h3 class="font-extrabold text-red-655 dark:text-red-500 text-lg mt-5 group-hover:text-red-755 dark:group-hover:text-red-450 transition-colors">Danger Zone</h3>
                <p class="text-xs settings-caption mt-2 leading-relaxed">Perform destructive database sweeps. Reset transaction histories or delete your restaurant account permanently.</p>
            </div>
            <div class="mt-8 flex items-center gap-1.5 text-xs font-black uppercase text-red-600 dark:text-red-500 group-hover:text-red-755 dark:group-hover:text-red-450 transition-colors">
                Destructive Tools
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- TAB SUB-VIEWS -->

    <!-- GENERAL TAB -->
    @if($activeTab === 'general')
    <form id="settings-form" wire:submit="saveSettings" class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start animate-in fade-in duration-300">
        <!-- Restaurant Identity -->
        <div class="settings-card border rounded-2xl overflow-hidden shadow-sm">
            <div class="px-6 py-4 settings-card-header flex items-center justify-between gap-4">
                <h2 class="font-bold text-base">Restaurant Identity</h2>
                <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-slate-500 dark:text-neutral-400 bg-slate-100 dark:bg-neutral-800 px-3 py-1 rounded-full border border-slate-200 dark:border-neutral-700">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    Synced via DRestro Admin
                </span>
            </div>
            <div class="p-6 space-y-5">
                <div>
                    <label class="block text-sm settings-label mb-1">Restaurant Name *</label>
                    <input wire:model="name" type="text" readonly disabled class="w-full px-4 py-2.5 settings-input rounded-xl focus:ring-2 outline-none text-sm bg-slate-100 dark:bg-[#111111] opacity-75 cursor-not-allowed border-slate-200 dark:border-neutral-800" placeholder="e.g. Himalayan Kitchen">
                    @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm settings-label mb-1">Tagline</label>
                    <input wire:model="tagline" type="text" readonly disabled class="w-full px-4 py-2.5 settings-input rounded-xl focus:ring-2 outline-none text-sm bg-slate-100 dark:bg-[#111111] opacity-75 cursor-not-allowed border-slate-200 dark:border-neutral-800" placeholder="e.g. Authentic Nepali Flavors">
                </div>

                <div>
                    <label class="block text-sm settings-label mb-1">Address / Street</label>
                    <input wire:model="address" type="text" readonly disabled class="w-full px-4 py-2.5 settings-input rounded-xl focus:ring-2 outline-none text-sm bg-slate-100 dark:bg-[#111111] opacity-75 cursor-not-allowed border-slate-200 dark:border-neutral-800" placeholder="e.g. Lakeside Road-6">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm settings-label mb-1">Ward No.</label>
                        <input wire:model="ward" type="text" readonly disabled class="w-full px-4 py-2.5 settings-input rounded-xl focus:ring-2 outline-none text-sm bg-slate-100 dark:bg-[#111111] opacity-75 cursor-not-allowed border-slate-200 dark:border-neutral-800" placeholder="e.g. Ward 6">
                    </div>
                    <div>
                        <label class="block text-sm settings-label mb-1">City / Municipality</label>
                        <input wire:model="city" type="text" readonly disabled class="w-full px-4 py-2.5 settings-input rounded-xl focus:ring-2 outline-none text-sm bg-slate-100 dark:bg-[#111111] opacity-75 cursor-not-allowed border-slate-200 dark:border-neutral-800" placeholder="e.g. Pokhara">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm settings-label mb-1">Phone</label>
                        <input wire:model.live="phone" type="tel" readonly disabled class="w-full px-4 py-2.5 settings-input rounded-xl focus:ring-2 outline-none text-sm bg-slate-100 dark:bg-[#111111] opacity-75 cursor-not-allowed border-slate-200 dark:border-neutral-800" placeholder="e.g. 9800000000">
                        @error('phone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm settings-label mb-1">Email</label>
                        <input wire:model="email" type="email" readonly disabled class="w-full px-4 py-2.5 settings-input rounded-xl focus:ring-2 outline-none text-sm bg-slate-100 dark:bg-[#111111] opacity-75 cursor-not-allowed border-slate-200 dark:border-neutral-800" placeholder="e.g. info@restaurant.com">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-205 dark:border-[#222222]">
                    <label class="block text-sm settings-label mb-1.5">Restaurant Logo</label>
                    <p class="text-xs settings-caption mb-3">Upload your restaurant's logo. This will be displayed on receipts and invoices.</p>
                    
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                        @if ($newLogo)
                            <div class="relative w-20 h-20 border border-slate-200 dark:border-[#333333] rounded-xl overflow-hidden shrink-0 bg-slate-50 dark:bg-neutral-900 flex items-center justify-center">
                                <img src="{{ $newLogo->temporaryUrl() }}" class="object-contain w-full h-full p-1" alt="Logo Preview">
                            </div>
                        @elseif ($restaurant && $restaurant->logo)
                            <div class="relative w-20 h-20 border border-slate-200 dark:border-[#333333] rounded-xl overflow-hidden shrink-0 bg-slate-50 dark:bg-neutral-900 flex items-center justify-center">
                                <img src="{{ asset('storage/' . $restaurant->logo) }}" class="object-contain w-full h-full p-1" alt="Restaurant Logo">
                            </div>
                        @else
                            <div class="relative w-20 h-20 border border-dashed border-slate-300 dark:border-[#333333] rounded-xl shrink-0 bg-slate-50 dark:bg-neutral-900 flex flex-col items-center justify-center text-slate-400">
                                <span class="text-2xl">🍽️</span>
                                <span class="text-[10px] mt-1 text-slate-400">No Logo</span>
                            </div>
                        @endif
                        
                        <div class="flex-1 space-y-2 w-full">
                            <input type="file" wire:model="newLogo" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 dark:file:bg-[#222] dark:file:text-white file:text-slate-705 hover:file:bg-slate-200 cursor-pointer">
                            @error('newLogo') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                            <div wire:loading wire:target="newLogo" class="text-[10px] text-emerald-655 font-bold animate-pulse">Uploading logo...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Password Column -->
        <div class="settings-card border rounded-2xl overflow-hidden shadow-sm">
            <div class="px-6 py-4 settings-card-header">
                <h2 class="font-bold text-base">Change Password</h2>
            </div>
            <div class="p-6 space-y-5">
                @if (session()->has('password_success'))
                    <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs rounded-xl font-bold">
                        ✅ {{ session('password_success') }}
                    </div>
                @endif

                <div>
                    <label class="block text-sm settings-label mb-1">Current Password</label>
                    <input wire:model="current_password" type="password" class="w-full px-4 py-2.5 settings-input rounded-xl focus:ring-2 outline-none text-sm" placeholder="••••••••">
                    @error('current_password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm settings-label mb-1">New Password</label>
                    <input wire:model="new_password" type="password" class="w-full px-4 py-2.5 settings-input rounded-xl focus:ring-2 outline-none text-sm" placeholder="••••••••">
                    @error('new_password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm settings-label mb-1">Confirm New Password</label>
                    <input wire:model="new_password_confirmation" type="password" class="w-full px-4 py-2.5 settings-input rounded-xl focus:ring-2 outline-none text-sm" placeholder="••••••••">
                    @error('new_password_confirmation') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end pt-2">
                    <button type="button" wire:click="changePassword" wire:loading.attr="disabled" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition-all shadow-md">
                        <span wire:loading.remove wire:target="changePassword">Update Password</span>
                        <span wire:loading wire:target="changePassword" class="flex items-center gap-2">
                            <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Updating...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </form>
    @endif

    <!-- BILLING TAB -->
    @if($activeTab === 'billing')
    <form id="settings-form" wire:submit="saveSettings" class="max-w-2xl animate-in fade-in duration-300">
        <div class="settings-card border rounded-2xl overflow-hidden shadow-sm">
            <div class="px-6 py-4 settings-card-header">
                <h2 class="font-bold text-base">Billing & Tax</h2>
            </div>
            <div class="p-6 space-y-5">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm settings-label mb-1">Currency Symbol *</label>
                        <select wire:model="currency" class="w-full px-4 py-2.5 settings-input rounded-xl focus:ring-2 outline-none text-sm">
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
                        <label class="block text-sm settings-label mb-1">PAN / VAT Number</label>
                        <input wire:model="panNumber" type="text" class="w-full px-4 py-2.5 settings-input rounded-xl focus:ring-2 outline-none text-sm" placeholder="e.g. 123456789">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm settings-label mb-1">Tax / VAT (%)</label>
                        <input wire:model="taxPercent" type="number" step="0.01" min="0" max="100" class="w-full px-4 py-2.5 settings-input rounded-xl focus:ring-2 outline-none text-sm" placeholder="13">
                        <p class="text-xs settings-caption mt-1">Nepal VAT is typically 13%</p>
                    </div>
                    <div>
                        <label class="block text-sm settings-label mb-1">Service Charge (%)</label>
                        <input wire:model="serviceChargePercent" type="number" step="0.01" min="0" max="100" class="w-full px-4 py-2.5 settings-input rounded-xl focus:ring-2 outline-none text-sm" placeholder="10">
                        <p class="text-xs settings-caption mt-1">Usually 10% in Nepal</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm settings-label mb-1">Calendar/Date System</label>
                        <select wire:model="dateCalendarType" class="w-full px-4 py-2.5 settings-input rounded-xl focus:ring-2 outline-none text-sm">
                            <option value="AD">English Date (AD)</option>
                            <option value="BS">Nepali Date (BS)</option>
                        </select>
                        <p class="text-xs settings-caption mt-1">Select whether to display Gregorian (AD) or Bikram Sambat (BS) dates system-wide.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @endif

    <!-- PAYMENT QR CODES TAB -->
    @if($activeTab === 'payment')
    <form id="settings-form" wire:submit="saveSettings" class="max-w-2xl animate-in fade-in duration-300">
        <div class="settings-card border rounded-2xl overflow-hidden shadow-sm">
            <div class="px-6 py-4 settings-card-header flex justify-between items-center">
                <h2 class="font-bold text-base">Payment QR Codes</h2>
                <span class="text-xs font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-950/20 px-2 py-1 rounded">Multi-Provider</span>
            </div>
            <div class="p-6 space-y-6">
                <!-- Default QR Code -->
                <div class="p-4 settings-inner-box rounded-xl">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-sm font-bold settings-label">General / Default QR Code</span>
                        @if ($restaurant && $restaurant->payment_qr_code)
                            <button type="button" wire:click="removeQr('default')" class="text-xs font-bold text-rose-600 hover:text-rose-800">Remove</button>
                        @endif
                    </div>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        @if ($newQrCode)
                            <div class="relative w-20 h-20 border border-slate-200 rounded-xl overflow-hidden shrink-0 bg-slate-50 flex items-center justify-center">
                                <img src="{{ $newQrCode->temporaryUrl() }}" class="object-contain w-full h-full p-1" alt="Preview">
                            </div>
                        @elseif ($restaurant && $restaurant->payment_qr_code)
                            <div class="relative w-20 h-20 border border-slate-200 rounded-xl overflow-hidden shrink-0 bg-slate-50 flex items-center justify-center">
                                <img src="{{ asset('storage/' . $restaurant->payment_qr_code) }}" class="object-contain w-full h-full p-1" alt="Default QR">
                            </div>
                        @else
                            <div class="relative w-20 h-20 border border-dashed border-slate-300 rounded-xl shrink-0 bg-slate-50 flex flex-col items-center justify-center text-slate-400">
                                <span class="text-xl">🖨️</span>
                                <span class="text-[9px] mt-1">No QR</span>
                            </div>
                        @endif
                        <div class="flex-1 w-full space-y-2">
                            <input type="file" wire:model="newQrCode" class="block w-full text-xs text-slate-550 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 dark:file:bg-[#222] dark:file:text-white file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                            @error('newQrCode') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                            <div wire:loading wire:target="newQrCode" class="text-[10px] text-emerald-650 font-bold animate-pulse">Uploading QR...</div>
                        </div>
                    </div>
                </div>

                <!-- eSewa QR Code -->
                <div class="p-4 settings-inner-box rounded-xl">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-sm font-bold text-emerald-800 dark:text-emerald-450">eSewa QR Code</span>
                        @if ($restaurant && $restaurant->esewa_qr)
                            <button type="button" wire:click="removeQr('esewa')" class="text-xs font-bold text-rose-650 hover:text-rose-850">Remove</button>
                        @endif
                    </div>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        @if ($newEsewaQr)
                            <div class="relative w-20 h-20 border border-slate-200 rounded-xl overflow-hidden shrink-0 bg-slate-50 flex items-center justify-center">
                                <img src="{{ $newEsewaQr->temporaryUrl() }}" class="object-contain w-full h-full p-1" alt="Preview">
                            </div>
                        @elseif ($restaurant && $restaurant->esewa_qr)
                            <div class="relative w-20 h-20 border border-slate-200 rounded-xl overflow-hidden shrink-0 bg-slate-50 flex items-center justify-center">
                                <img src="{{ asset('storage/' . $restaurant->esewa_qr) }}" class="object-contain w-full h-full p-1" alt="eSewa QR">
                            </div>
                        @else
                            <div class="relative w-20 h-20 border border-dashed border-slate-300 rounded-xl shrink-0 bg-slate-50 flex flex-col items-center justify-center text-slate-400">
                                <span class="text-xl">🟢</span>
                                <span class="text-[9px] mt-1">No eSewa QR</span>
                            </div>
                        @endif
                        <div class="flex-1 w-full space-y-2">
                            <input type="file" wire:model="newEsewaQr" class="block w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 dark:file:bg-[#222] dark:file:text-white file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                            @error('newEsewaQr') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                            <div wire:loading wire:target="newEsewaQr" class="text-[10px] text-emerald-650 font-bold animate-pulse">Uploading QR...</div>
                        </div>
                    </div>
                </div>

                <!-- Khalti QR Code -->
                <div class="p-4 settings-inner-box rounded-xl">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-sm font-bold text-purple-800 dark:text-purple-400">Khalti QR Code</span>
                        @if ($restaurant && $restaurant->khalti_qr)
                            <button type="button" wire:click="removeQr('khalti')" class="text-xs font-bold text-rose-650 hover:text-rose-850">Remove</button>
                        @endif
                    </div>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        @if ($newKhaltiQr)
                            <div class="relative w-20 h-20 border border-slate-200 rounded-xl overflow-hidden shrink-0 bg-slate-50 flex items-center justify-center">
                                <img src="{{ $newKhaltiQr->temporaryUrl() }}" class="object-contain w-full h-full p-1" alt="Preview">
                            </div>
                        @elseif ($restaurant && $restaurant->khalti_qr)
                            <div class="relative w-20 h-20 border border-slate-200 rounded-xl overflow-hidden shrink-0 bg-slate-50 flex items-center justify-center">
                                <img src="{{ asset('storage/' . $restaurant->khalti_qr) }}" class="object-contain w-full h-full p-1" alt="Khalti QR">
                            </div>
                        @else
                            <div class="relative w-20 h-20 border border-dashed border-slate-300 rounded-xl shrink-0 bg-slate-50 flex flex-col items-center justify-center text-slate-400">
                                <span class="text-xl">🟣</span>
                                <span class="text-[9px] mt-1">No Khalti QR</span>
                            </div>
                        @endif
                        <div class="flex-1 w-full space-y-2">
                            <input type="file" wire:model="newKhaltiQr" class="block w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 dark:file:bg-[#222] dark:file:text-white file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                            @error('newKhaltiQr') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                            <div wire:loading wire:target="newKhaltiQr" class="text-[10px] text-emerald-650 font-bold animate-pulse">Uploading QR...</div>
                        </div>
                    </div>
                </div>

                <!-- Fonepay QR Code -->
                <div class="p-4 settings-inner-box rounded-xl">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-sm font-bold text-red-800 dark:text-red-400">Fonepay QR Code</span>
                        @if ($restaurant && $restaurant->fonepay_qr)
                            <button type="button" wire:click="removeQr('fonepay')" class="text-xs font-bold text-rose-650 hover:text-rose-855">Remove</button>
                        @endif
                    </div>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        @if ($newFonepayQr)
                            <div class="relative w-20 h-20 border border-slate-200 rounded-xl overflow-hidden shrink-0 bg-slate-50 flex items-center justify-center">
                                <img src="{{ $newFonepayQr->temporaryUrl() }}" class="object-contain w-full h-full p-1" alt="Preview">
                            </div>
                        @elseif ($restaurant && $restaurant->fonepay_qr)
                            <div class="relative w-20 h-20 border border-slate-200 rounded-xl overflow-hidden shrink-0 bg-slate-50 flex items-center justify-center">
                                <img src="{{ asset('storage/' . $restaurant->fonepay_qr) }}" class="object-contain w-full h-full p-1" alt="Fonepay QR">
                            </div>
                        @else
                            <div class="relative w-20 h-20 border border-dashed border-slate-300 rounded-xl shrink-0 bg-slate-50 flex flex-col items-center justify-center text-slate-400">
                                <span class="text-xl">🔴</span>
                                <span class="text-[9px] mt-1">No Fonepay QR</span>
                            </div>
                        @endif
                        <div class="flex-1 w-full space-y-2">
                            <input type="file" wire:model="newFonepayQr" class="block w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 dark:file:bg-[#222] dark:file:text-white file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                            @error('newFonepayQr') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                            <div wire:loading wire:target="newFonepayQr" class="text-[10px] text-emerald-650 font-bold animate-pulse">Uploading QR...</div>
                        </div>
                    </div>
                </div>

                <!-- Bank Transfer QR Code -->
                <div class="p-4 settings-inner-box rounded-xl">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-sm font-bold text-slate-800 dark:text-white">Bank Transfer / FonePay Direct QR</span>
                        @if ($restaurant && $restaurant->bank_transfer_qr)
                            <button type="button" wire:click="removeQr('bank_transfer')" class="text-xs font-bold text-rose-650 hover:text-rose-850">Remove</button>
                        @endif
                    </div>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        @if ($newBankTransferQr)
                            <div class="relative w-20 h-20 border border-slate-200 rounded-xl overflow-hidden shrink-0 bg-slate-50 flex items-center justify-center">
                                <img src="{{ $newBankTransferQr->temporaryUrl() }}" class="object-contain w-full h-full p-1" alt="Preview">
                            </div>
                        @elseif ($restaurant && $restaurant->bank_transfer_qr)
                            <div class="relative w-20 h-20 border border-slate-200 rounded-xl overflow-hidden shrink-0 bg-slate-50 flex items-center justify-center">
                                <img src="{{ asset('storage/' . $restaurant->bank_transfer_qr) }}" class="object-contain w-full h-full p-1" alt="Bank Transfer QR">
                            </div>
                        @else
                            <div class="relative w-20 h-20 border border-dashed border-slate-300 rounded-xl shrink-0 bg-slate-50 flex flex-col items-center justify-center text-slate-400">
                                <span class="text-xl">🏦</span>
                                <span class="text-[9px] mt-1">No Bank QR</span>
                            </div>
                        @endif
                        <div class="flex-1 w-full space-y-2">
                            <input type="file" wire:model="newBankTransferQr" class="block w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 dark:file:bg-[#222] dark:file:text-white file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                            @error('newBankTransferQr') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                            <div wire:loading wire:target="newBankTransferQr" class="text-[10px] text-emerald-650 font-bold animate-pulse">Uploading QR...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @endif

    <!-- NEPAL E-BILLING TAB (Disabled for now) -->
    {{--
    @if($activeTab === 'ebilling')
    <form id="settings-form" wire:submit="saveSettings" class="max-w-2xl animate-in fade-in duration-300">
        <div class="settings-card border rounded-2xl overflow-hidden shadow-sm">
            <div class="px-6 py-4 settings-card-header flex justify-between items-center">
                <h2 class="font-bold text-base">Nepal E-Billing Integration</h2>
                <span class="text-xs font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-950/20 px-2 py-1 rounded">VAT Billing Sync</span>
            </div>
            <div class="p-6 space-y-5">
                <div class="flex items-center justify-between p-4 settings-inner-box rounded-xl">
                    <div>
                        <span class="block text-sm font-semibold settings-label">Enable Automated Sync</span>
                        <span class="block text-xs settings-caption mt-0.5">Automatically sync invoice to E-billing portal on checkout.</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="nepalEbillingEnabled" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-600"></div>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm settings-label mb-1">Tenant Subdomain</label>
                        <input wire:model="nepalEbillingSubdomain" type="text" class="w-full px-4 py-2.5 settings-input rounded-xl focus:ring-2 outline-none text-sm" placeholder="e.g. sky">
                        <span class="text-[10px] settings-caption mt-1 block">Your E-Billing tenant name.</span>
                    </div>
                    <div>
                        <label class="block text-sm settings-label mb-1">Environment</label>
                        <select wire:model="nepalEbillingEnvironment" class="w-full px-4 py-2.5 settings-input rounded-xl focus:ring-2 outline-none text-sm">
                            <option value="staging">Staging (Testing)</option>
                            <option value="production">Production (Live)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm settings-label mb-1">API Key</label>
                        <input wire:model="nepalEbillingApiKey" type="password" class="w-full px-4 py-2.5 settings-input rounded-xl focus:ring-2 outline-none text-sm" placeholder="Enter API Key">
                        <span class="text-[10px] settings-caption mt-1 block">Prefix: bbk_bRZ2nTnL.S7y4QlaPxf5...</span>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-205 dark:border-[#222222] flex items-center justify-between gap-4">
                    <button type="button" wire:click="testEbillingConnection" wire:loading.attr="disabled"
                            class="px-4 py-2 bg-slate-705 dark:bg-[#222222] dark:hover:bg-[#2b2b2b] dark:text-white border border-slate-200 dark:border-[#333] text-slate-800 text-xs font-bold rounded-lg transition-colors shadow-sm flex items-center gap-2">
                        <span wire:loading.remove wire:target="testEbillingConnection">⚡ Test Connection</span>
                        <span wire:loading wire:target="testEbillingConnection" class="flex items-center gap-1">
                            <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Testing...
                        </span>
                    </button>
                    
                    @if($ebillingTestResult === 'ok')
                        <span class="text-xs font-bold text-emerald-600">✅ Connection successful!</span>
                    @elseif($ebillingTestResult === 'fail')
                        <span class="text-xs font-bold text-rose-600">❌ {{ $ebillingTestError }}</span>
                    @endif
                </div>
            </div>
        </div>
    </form>
    @endif
    --}}

    <!-- HARDWARE & PRINTERS TAB -->
    @if($activeTab === 'hardware')
    <form id="settings-form" wire:submit="saveSettings" class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start animate-in fade-in duration-300">
        <!-- Printers configuration -->
        <div class="space-y-6">
            <div class="settings-card border rounded-2xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 settings-card-header flex justify-between items-center gap-4">
                    <h2 class="font-bold text-base">Hardware & Printers</h2>
                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-950/20 px-2 py-1 rounded">ESC/POS Supported</span>
                </div>
                
                @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'admin' || auth()->user()->role === 'manager')
                @php
                    $isCloud = !in_array(request()->getHost(), ['localhost', '127.0.0.1']) && !str_starts_with(request()->getHost(), '192.168.');
                @endphp
                <div class="p-6 space-y-6">

                    <!-- Network Printer Scanner -->
                    <div class="p-4 settings-inner-box rounded-xl">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                            <div>
                                <h3 class="font-bold text-sm settings-label">Network Printer Scanner</h3>
                                <p class="text-xs settings-caption mt-1">Automatically scan your local Wi-Fi router for active network printers (Port 9100).</p>
                            </div>
                            <div class="shrink-0 self-start">
                                <button type="button" onclick="runClientSideNetworkScan()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm text-xs font-bold transition-colors flex items-center gap-2 cursor-pointer">
                                    <span id="scan-btn-text">🔍 Auto-Scan Wi-Fi Network</span>
                                </button>
                            </div>
                        </div>

                        <!-- Scan Status & Results -->
                        <div id="js-scan-status" class="hidden mt-3 p-3 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-800 dark:text-emerald-300 text-xs font-bold rounded-xl border border-emerald-200 flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span id="js-scan-progress-msg">Scanning local Wi-Fi router subnets...</span>
                        </div>

                        <div id="js-scanned-printers-list" class="mt-3"></div>

                        @if(session('scan_error'))
                            <div class="mt-3 p-2 bg-red-50 text-red-650 text-xs font-semibold rounded border border-red-100">
                                {{ session('scan_error') }}
                            </div>
                        @endif

                        @if(!empty($scannedPrinters))
                            <div class="mt-4 pt-4 border-t border-slate-200 dark:border-[#222222]">
                                <p class="text-xs font-bold settings-label mb-2">Found Printers (Click IP to copy):</p>
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

                    <!-- USB Printer Scanner & Auto-Detect -->
                    <div class="p-4 settings-inner-box rounded-xl">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                            <div>
                                <h3 class="font-bold text-sm settings-label">USB Printer Auto-Detect & Driver Presets</h3>
                                <p class="text-xs settings-caption mt-1">Select your Windows Shared Printer driver name or pick from common thermal presets.</p>
                            </div>
                            <div class="shrink-0 self-start">
                                <button type="button" onclick="showUsbPresetsModal()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm text-xs font-bold transition-colors flex items-center gap-2 cursor-pointer">
                                    🔌 Select USB Driver
                                </button>
                            </div>
                        </div>

                        @if(session('usb_scan_error'))
                            <div class="mt-3 p-2 bg-red-50 text-red-650 text-xs font-semibold rounded border border-red-100">
                                {{ session('usb_scan_error') }}
                            </div>
                        @endif

                        @if(!empty($usbPrinters))
                            <div class="mt-4 pt-4 border-t border-blue-200 dark:border-[#222222] space-y-2">
                                <p class="text-xs font-bold text-blue-800 dark:text-blue-300">Installed Printers ({{ count($usbPrinters) }} found):</p>
                                <div class="space-y-3">
                                    @foreach($usbPrinters as $printer)
                                        <div class="p-3 bg-white dark:bg-[#1E1E1E] border {{ $printer['is_shared'] ? 'border-emerald-200' : 'border-slate-200' }} rounded-lg shadow-sm">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="text-sm font-bold text-slate-800 dark:text-white">🖨️ {{ $printer['name'] }}</p>
                                                    @if($printer['is_shared'])
                                                        <p class="text-xs text-emerald-600 font-semibold flex items-center gap-1 mt-1">
                                                            Shared as: {{ $printer['share_name'] }}
                                                        </p>
                                                    @else
                                                        <p class="text-xs text-amber-600 font-semibold mt-1">⚠️ Not shared — enable sharing in Windows first</p>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($printer['is_shared'])
                                                @php $path = 'smb://127.0.0.1/' . $printer['share_name']; @endphp
                                                <div class="mt-2 pt-2 border-t border-slate-100 dark:border-[#222222] flex flex-wrap items-center gap-2">
                                                    <span class="text-[10px] text-slate-400 font-bold uppercase">Assign to:</span>
                                                    <button type="button" wire:click="assignPrinterConfig('kitchen', 'usb', '{{ $path }}')" 
                                                        class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-colors cursor-pointer {{ ($kitchenPrinterPath === $path && $kitchenPrinterType === 'usb') ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-[#222] text-slate-650 hover:bg-emerald-100 hover:text-emerald-700' }}">
                                                        🔪 Kitchen
                                                    </button>
                                                    <button type="button" wire:click="assignPrinterConfig('cashier', 'usb', '{{ $path }}')"
                                                        class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-colors cursor-pointer {{ ($cashierPrinterPath === $path && $cashierPrinterType === 'usb') ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-[#222] text-slate-655 hover:bg-blue-100 hover:text-blue-700' }}">
                                                        💰 Cashier
                                                    </button>
                                                    <button type="button" wire:click="assignPrinterConfig('hotel', 'usb', '{{ $path }}')"
                                                        class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-colors cursor-pointer {{ ($hotelPrinterPath === $path && $hotelPrinterType === 'usb') ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-[#222] text-slate-655 hover:bg-indigo-100 hover:text-indigo-700' }}">
                                                        🏨 Hotel
                                                    </button>
                                                    <button type="button" wire:click="assignPrinterConfig('bar', 'usb', '{{ $path }}')"
                                                        class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-colors cursor-pointer {{ ($botPrinterPath === $path && $botPrinterType === 'usb') ? 'bg-amber-600 text-white' : 'bg-slate-100 dark:bg-[#222] text-slate-655 hover:bg-amber-100 hover:text-amber-700' }}">
                                                        🍸 Bar
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Printers assign list column -->
        <div class="space-y-6">
            <!-- Printing routing -->
            <div class="settings-card border rounded-2xl overflow-hidden p-6 space-y-6">
                <div class="p-5 bg-gradient-to-br from-rose-50 to-red-50 dark:from-red-950/20 dark:to-rose-950/20 rounded-2xl border border-rose-200 dark:border-rose-900/30 shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-black text-rose-900 dark:text-red-450 text-sm tracking-tight">Printing Logic (KOT/BOT)</h3>
                            </div>
                            <p class="text-[11px] text-rose-705 dark:text-neutral-450 mt-1 font-bold">Smart routing for food and bar tickets</p>
                        </div>
                        <div class="inline-flex p-1 bg-white dark:bg-[#111] rounded-2xl border border-rose-250/50 shadow-inner shrink-0">
                            <button type="button" wire:click="$set('separateKotBot', false)" 
                                class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-black uppercase transition-all duration-300 {{ !$separateKotBot ? 'bg-[#E53935] text-white shadow-lg' : 'text-slate-450 hover:text-rose-650' }}">
                                Combined
                            </button>
                            <button type="button" wire:click="$set('separateKotBot', true)" 
                                class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-black uppercase transition-all duration-300 {{ $separateKotBot ? 'bg-[#E53935] text-white shadow-lg' : 'text-slate-455 hover:text-rose-650' }}">
                                Separate
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Kitchen Printer -->
                <div class="p-4 settings-inner-box rounded-xl">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-sm settings-label">
                            {{ !$separateKotBot ? 'Kitchen Printer (KOT/BOT)' : 'Kitchen Printer (KOT Only)' }}
                        </h3>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="autoPrintKot" class="w-4 h-4 text-rose-605 rounded border-slate-350 focus:ring-rose-500">
                            <span class="text-xs font-semibold settings-label">Auto-print KOT</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[10px] settings-caption mb-1 uppercase font-bold">Type</label>
                            <select wire:model.live="kitchenPrinterType" class="w-full px-3 py-2 settings-input rounded-lg text-xs focus:ring-2 outline-none">
                                <option value="network">Network (LAN/Wi-Fi)</option>
                                <option value="usb">Windows USB Share</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[10px] settings-caption mb-1 uppercase font-bold">Printer IP / Share Name</label>
                            <input wire:model="kitchenPrinterPath" type="text" class="w-full px-3 py-2 settings-input rounded-lg text-xs focus:ring-2 outline-none" placeholder="{{ $kitchenPrinterType == 'network' ? '192.168.1.100:9100' : 'smb://127.0.0.1/KitchenPrinter' }}">
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mt-3">
                        <button type="button" wire:click="testKitchenPrinter" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white dark:bg-[#2A2A2A] dark:hover:bg-[#383838] dark:text-white border border-slate-300 dark:border-neutral-700 text-xs font-bold rounded-xl transition-all shadow-sm cursor-pointer">
                            Direct Socket Test
                        </button>
                        <button type="button" onclick="window.triggerClientSideTestPrint('kitchen')" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white dark:bg-emerald-600 dark:hover:bg-emerald-500 border border-emerald-500 text-xs font-bold rounded-xl transition-all shadow-sm cursor-pointer flex items-center gap-1.5">
                            🖨️ Browser Test Receipt
                        </button>
                    </div>
                    @if($printerTestResult === 'kitchen_ok')
                    <span class="text-xs font-bold text-emerald-650 mt-1.5 block">✅ Test print sent via socket!</span>
                    @elseif($printerTestResult === 'kitchen_fail')
                    <span class="text-xs font-bold text-rose-500 mt-1.5 block">❌ Socket Test Note: {{ $printerTestError }} (For Cloud POS, use "Browser Test Receipt" button above!)</span>
                    @endif
                </div>

                @if($separateKotBot)
                <!-- BOT Printer -->
                <div class="p-4 settings-inner-box rounded-xl">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-sm settings-label">Bar Printer (BOT)</h3>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[10px] settings-caption mb-1 uppercase font-bold">Type</label>
                            <select wire:model.live="botPrinterType" class="w-full px-3 py-2 settings-input rounded-lg text-xs focus:ring-2 outline-none">
                                <option value="network">Network (LAN/Wi-Fi)</option>
                                <option value="usb">Windows USB Share</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[10px] settings-caption mb-1 uppercase font-bold">Printer IP / Share Name</label>
                            <input wire:model="botPrinterPath" type="text" class="w-full px-3 py-2 settings-input rounded-lg text-xs focus:ring-2 outline-none" placeholder="{{ $botPrinterType == 'network' ? '192.168.1.103:9100' : 'smb://127.0.0.1/BarPrinter' }}">
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mt-3">
                        <button type="button" wire:click="testBotPrinter" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white dark:bg-[#2A2A2A] dark:hover:bg-[#383838] dark:text-white border border-slate-300 dark:border-neutral-700 text-xs font-bold rounded-xl transition-all shadow-sm cursor-pointer">
                            Direct Socket Test
                        </button>
                        <button type="button" onclick="window.triggerClientSideTestPrint('bar')" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white dark:bg-emerald-600 dark:hover:bg-emerald-500 border border-emerald-500 text-xs font-bold rounded-xl transition-all shadow-sm cursor-pointer flex items-center gap-1.5">
                            🖨️ Browser Test Receipt
                        </button>
                    </div>
                    @if($printerTestResult === 'bot_ok')
                    <span class="text-xs font-bold text-emerald-650 mt-1.5 block">✅ Test print sent via socket!</span>
                    @elseif($printerTestResult === 'bot_fail')
                    <span class="text-xs font-bold text-rose-500 mt-1.5 block">❌ Socket Test Note: {{ $printerTestError }} (For Cloud POS, use "Browser Test Receipt" button above!)</span>
                    @endif
                </div>
                @endif

                <!-- Cashier Printer -->
                <div class="p-4 settings-inner-box rounded-xl">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-sm settings-label">Cashier Printer (Receipts)</h3>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="autoPrintReceipt" class="w-4 h-4 text-rose-605 rounded border-slate-350 focus:ring-rose-500">
                            <span class="text-xs font-semibold settings-label">Auto-print Receipt</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[10px] settings-caption mb-1 uppercase font-bold">Type</label>
                            <select wire:model.live="cashierPrinterType" class="w-full px-3 py-2 settings-input rounded-lg text-xs focus:ring-2 outline-none">
                                <option value="network">Network (LAN/Wi-Fi)</option>
                                <option value="usb">Windows USB Share</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[10px] settings-caption mb-1 uppercase font-bold">Printer IP / Share Name</label>
                            <input wire:model="cashierPrinterPath" type="text" class="w-full px-3 py-2 settings-input rounded-lg text-xs focus:ring-2 outline-none" placeholder="{{ $cashierPrinterType == 'network' ? '192.168.1.101:9100' : 'smb://127.0.0.1/CashierPrinter' }}">
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mt-3">
                        <button type="button" wire:click="testCashierPrinter" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white dark:bg-[#2A2A2A] dark:hover:bg-[#383838] dark:text-white border border-slate-300 dark:border-neutral-700 text-xs font-bold rounded-xl transition-all shadow-sm cursor-pointer">
                            Direct Socket Test
                        </button>
                        <button type="button" onclick="window.triggerClientSideTestPrint('cashier')" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white dark:bg-emerald-600 dark:hover:bg-emerald-500 border border-emerald-500 text-xs font-bold rounded-xl transition-all shadow-sm cursor-pointer flex items-center gap-1.5">
                            🖨️ Browser Test Receipt
                        </button>
                    </div>
                    @if($printerTestResult === 'cashier_ok')
                    <span class="text-xs font-bold text-emerald-650 mt-1.5 block">✅ Test print sent via socket!</span>
                    @elseif($printerTestResult === 'cashier_fail')
                    <span class="text-xs font-bold text-rose-500 mt-1.5 block">❌ Socket Test Note: {{ $printerTestError }} (For Cloud POS, use "Browser Test Receipt" button above!)</span>
                    @endif
                </div>

                <!-- Hotel Printer -->
                <div class="p-4 settings-inner-box rounded-xl">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-sm settings-label">Hotel Printer (Reception)</h3>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="hotelAutoPrint" class="w-4 h-4 text-rose-605 rounded border-slate-350 focus:ring-rose-500">
                            <span class="text-xs font-semibold settings-label">Auto-print Bills</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[10px] settings-caption mb-1 uppercase font-bold">Type</label>
                            <select wire:model.live="hotelPrinterType" class="w-full px-3 py-2 settings-input rounded-lg text-xs focus:ring-2 outline-none">
                                <option value="network">Network (LAN/Wi-Fi)</option>
                                <option value="usb">Windows USB Share</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[10px] settings-caption mb-1 uppercase font-bold">Printer IP / Share Name</label>
                            <input wire:model="hotelPrinterPath" type="text" class="w-full px-3 py-2 settings-input rounded-lg text-xs focus:ring-2 outline-none" placeholder="{{ $hotelPrinterType == 'network' ? '192.168.1.102:9100' : 'smb://127.0.0.1/HotelPrinter' }}">
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mt-3">
                        <button type="button" wire:click="testHotelPrinter" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white dark:bg-[#2A2A2A] dark:hover:bg-[#383838] dark:text-white border border-slate-300 dark:border-neutral-700 text-xs font-bold rounded-xl transition-all shadow-sm cursor-pointer">
                            Direct Socket Test
                        </button>
                        <button type="button" onclick="window.triggerClientSideTestPrint('hotel')" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white dark:bg-emerald-600 dark:hover:bg-emerald-500 border border-emerald-500 text-xs font-bold rounded-xl transition-all shadow-sm cursor-pointer flex items-center gap-1.5">
                            🖨️ Browser Test Receipt
                        </button>
                    </div>
                    @if($printerTestResult === 'hotel_ok')
                    <span class="text-xs font-bold text-emerald-655 mt-1.5 block">✅ Test print sent via socket!</span>
                    @elseif($printerTestResult === 'hotel_fail')
                    <span class="text-xs font-bold text-rose-500 mt-1.5 block">❌ Socket Test Note: {{ $printerTestError }} (For Cloud POS, use "Browser Test Receipt" button above!)</span>
                    @endif
                </div>
            </div>
        </div>
    </form>
    @endif

    <!-- DEVICE WIRELESS CONNECTION TAB -->
    @if($activeTab === 'connection')
    <div class="max-w-2xl settings-card border rounded-2xl overflow-hidden p-6 space-y-6 animate-in fade-in duration-300">
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30 rounded-xl flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
            </div>
            <div>
                <p class="font-bold text-emerald-850 dark:text-emerald-300 text-sm">Local IP Server Address: <code class="bg-emerald-200/50 px-2 py-0.5 rounded text-emerald-900">{{ request()->getHost() }}</code></p>
                <p class="text-xs text-emerald-700 dark:text-emerald-400 mt-1">Connect other waiter/reception tablets and mobile devices to the <strong>same Wi-Fi network</strong>.</p>
            </div>
        </div>

        <div class="flex flex-col items-center justify-center border border-slate-200 dark:border-slate-800 rounded-2xl bg-slate-50/50 dark:bg-[#141414] hover:shadow-md transition-shadow py-6 w-full">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(url('') . '/login') }}" class="w-44 h-44 rounded-xl shadow-sm border border-white dark:border-slate-700" alt="Staff Login QR">
            <p class="mt-4 font-bold text-slate-700 dark:text-white text-sm">🧑‍💼 Wireless Staff & Admin Login</p>
            <a href="{{ url('') }}/login" target="_blank" class="text-[11px] text-blue-500 mt-1.5 font-mono hover:underline text-center break-all dark:text-blue-400">{{ url('') }}/login</a>
        </div>
    </div>
    @endif

    <!-- BACKUP & RESTORE TAB -->
    @if($activeTab === 'backup')
    <div class="max-w-2xl settings-card border rounded-2xl p-6 space-y-6 animate-in fade-in duration-300">
        <!-- Download Backup -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-blue-50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/30 rounded-xl gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                </div>
                <div>
                    <p class="font-bold text-blue-800 dark:text-blue-300 text-sm">Download Database Backup</p>
                    <p class="text-xs text-blue-650 dark:text-neutral-400 mt-1">Save a copy of your menu items, orders and sales locally.</p>
                </div>
            </div>
            <button type="button" wire:click="downloadBackup" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition-colors shrink-0">
                Download .sqlite
            </button>
        </div>

        <!-- Upload Backup -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-amber-50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/30 rounded-xl gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                </div>
                <div>
                    <p class="font-bold text-amber-800 dark:text-amber-300 text-sm">Restore Database Backup</p>
                    <p class="text-xs text-amber-650 dark:text-neutral-400 mt-1">Restore database from a saved .sqlite file. <strong class="text-rose-600">WARNING: Replaces all data!</strong></p>
                </div>
            </div>
            <div class="flex flex-col items-end gap-2 shrink-0">
                <label class="px-4 py-2 bg-amber-600 text-white text-xs font-bold rounded-lg hover:bg-amber-700 cursor-pointer">
                    Upload & Restore .sqlite
                    <input type="file" wire:model="backupFile" class="hidden" accept=".sqlite">
                </label>
                <div wire:loading wire:target="backupFile" class="text-[10px] text-amber-600 font-semibold animate-pulse">
                    Restoring database...
                </div>
            </div>
        </div>

        @if(auth()->user()->role === 'super_admin')
        <!-- USB Backup -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30 rounded-xl gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center shrink-0 text-xl">
                    💾
                </div>
                <div>
                    <p class="font-bold text-emerald-800 dark:text-emerald-300 text-sm">Backup to USB Pendrive</p>
                    <p class="text-xs text-emerald-650 dark:text-neutral-400 mt-1">Instantly back up your database to any connected USB drive (D: to Z:).</p>
                </div>
            </div>
            <button type="button" wire:click="backupToUsbManual" wire:loading.attr="disabled"
                class="px-4 py-2 bg-emerald-650 text-white text-xs font-bold rounded-lg hover:bg-emerald-700 transition-colors shrink-0">
                <span wire:loading.remove wire:target="backupToUsbManual">Scan & Backup USB</span>
                <span wire:loading wire:target="backupToUsbManual" class="flex items-center gap-2">Scanning...</span>
            </button>
        </div>
        @endif

        @if (session()->has('usb_success'))
            <div class="p-4 bg-emerald-55 border border-emerald-100 text-emerald-850 text-xs rounded-xl font-bold">
                ✅ {{ session('usb_success') }}
            </div>
        @endif
        @if (session()->has('usb_error'))
            <div class="p-4 bg-rose-55 border border-rose-100 text-rose-850 text-xs rounded-xl font-bold">
                ⚠️ {{ session('usb_error') }}
            </div>
        @endif
        @if (session()->has('backup_success'))
            <div class="p-4 bg-emerald-55 border border-emerald-100 text-emerald-850 text-xs rounded-xl font-bold">
                {{ session('backup_success') }}
            </div>
        @endif
        @if (session()->has('backup_error'))
            <div class="p-4 bg-rose-55 border border-rose-100 text-rose-850 text-xs rounded-xl font-bold">
                {{ session('backup_error') }}
            </div>
        @endif
    </div>
    @endif

    <!-- TICKETS, LICENSE, GOODS TABS -->
    @if($activeTab === 'support')
        @livewire('admin.support-tickets')
    @elseif($activeTab === 'license')
        @livewire('admin.license-manager')
    @elseif($activeTab === 'ordered-goods')
        @livewire('admin.ordered-goods')
    @endif

    <!-- DANGER ZONE TAB -->
    @if($activeTab === 'danger')
    @if(request()->query('show_destructive_tools') === 'yes_support_only')
    <div class="max-w-2xl settings-card border rounded-2xl p-6 space-y-6 animate-in fade-in duration-300">
        @if (session()->has('danger_success'))
            <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-850 text-xs rounded-xl font-bold">
                ✅ {{ session('danger_success') }}
            </div>
        @endif
        @if (session()->has('danger_error'))
            <div class="p-4 bg-red-50 border border-red-100 text-red-800 text-xs rounded-xl font-bold">
                ❌ {{ session('danger_error') }}
            </div>
        @endif

        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-5 bg-amber-50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/30 rounded-xl gap-4">
            <div>
                <h3 class="font-bold text-amber-850 dark:text-amber-300 text-sm">Reset Restaurant Data</h3>
                <p class="text-xs text-amber-650 dark:text-neutral-400 mt-1">Permanently delete all Orders, Items, Invoices, and Inventory Logs. Settings and Menu are kept.</p>
            </div>
            <button type="button" wire:click="resetRestaurantData" onclick="confirm('Are you absolutely sure you want to RESET all transaction data? This cannot be undone!') || event.stopImmediatePropagation()" class="px-5 py-2.5 bg-white dark:bg-[#222] border border-amber-350 text-amber-700 hover:bg-amber-100 shrink-0 rounded-xl font-bold text-xs shadow-sm dark:text-amber-300">
                Reset Data
            </button>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-5 bg-red-50 dark:bg-red-950/20 border border-red-100 dark:border-red-900/30 rounded-xl gap-4">
            <div>
                <h3 class="font-bold text-red-800 dark:text-red-300 text-sm">Delete Restaurant Account</h3>
                <p class="text-xs text-red-650 dark:text-neutral-400 mt-1">This will permanently delete the entire restaurant from the database, including all staff and data.</p>
            </div>
            <button type="button" wire:click="deleteRestaurant" onclick="confirm('Are you absolutely sure you want to DELETE the entire restaurant? This action is PERMANENT and irreversible!') || event.stopImmediatePropagation()" class="px-5 py-2.5 bg-red-650 hover:bg-red-750 text-white text-xs font-bold rounded-xl transition-colors shadow-md">
                Delete Restaurant
            </button>
        </div>
    </div>
    @else
    <div class="max-w-2xl settings-card p-8 border rounded-2xl flex flex-col items-center justify-center text-center space-y-4">
        <span class="text-4xl">⚠️</span>
        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Super Admin Access Required</h3>
        <p class="text-xs text-slate-500 max-w-sm mx-auto">Dangerous actions are restricted to prevent operational disruption. Please contact system administrator.</p>
    </div>
    @endif
    @endif

    <!-- Printer Setup Wizard Modal -->
    <div id="printer-wizard-modal" class="hidden fixed inset-0 z-[1000] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-3xl p-6 shadow-2xl max-w-md w-full space-y-6 animate-in zoom-in-95 duration-200">
            <div class="flex justify-between items-center">
                <h3 class="text-sm font-black text-slate-800 dark:text-white flex items-center gap-2">
                    🖨️ <span id="wizard-title-text">Printer Configuration Guide</span>
                </h3>
                <button type="button" onclick="closePrinterWizard()" class="text-slate-400 dark:text-gray-500 hover:text-[#111] dark:hover:text-white transition-colors font-bold text-sm">
                    ✕
                </button>
            </div>

            <!-- Progress Steps -->
            <div class="grid grid-cols-3 gap-2 text-center text-[9px] uppercase font-black text-slate-400 dark:text-neutral-500">
                <div class="p-2 bg-slate-50 dark:bg-[#222]/40 rounded-xl border border-slate-200 dark:border-[#333]">
                    <div class="text-xs mb-1">1️⃣</div>
                    Turn Off
                </div>
                <div class="p-2 bg-slate-50 dark:bg-[#222]/40 rounded-xl border border-slate-200 dark:border-[#333]">
                    <div class="text-xs mb-1">2️⃣</div>
                    Hold FEED + ON
                </div>
                <div class="p-2 bg-slate-50 dark:bg-[#222]/40 rounded-xl border border-slate-200 dark:border-[#333]">
                    <div class="text-xs mb-1">3️⃣</div>
                    Get Details
                </div>
            </div>

            <div class="space-y-4">
                <div class="bg-slate-900 dark:bg-slate-950 p-5 rounded-2xl border border-slate-800 dark:border-slate-800 text-xs space-y-3">
                    <div class="font-bold text-emerald-400 dark:text-emerald-400 uppercase tracking-wider text-[10px] select-none">Step-by-Step Instructions:</div>
                    <ul id="wizard-instructions" class="list-decimal list-inside space-y-2.5 text-slate-200 dark:text-slate-200 leading-relaxed text-xs">
                        <!-- Dynamic instructions here -->
                    </ul>
                </div>

                <input type="hidden" id="wizard-type-val" value="network" />
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[10px] text-slate-400 dark:text-neutral-500 uppercase font-black">Apply Setting To</label>
                        <select id="wizard-target-select" class="w-full px-3 py-2 bg-slate-50 dark:bg-[#222] border border-slate-200 dark:border-[#333] text-slate-800 dark:text-white rounded-xl text-xs focus:outline-none cursor-pointer">
                            <option value="kitchen">Kitchen Printer</option>
                            <option value="cashier">Cashier Printer</option>
                            <option value="bar">Bar Printer (BOT)</option>
                            <option value="hotel">Hotel Printer</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label id="wizard-input-label" class="block text-[10px] text-slate-400 dark:text-neutral-500 uppercase font-black">Printer IP Address</label>
                        <input type="text" id="wizard-input-field" placeholder="e.g. 192.168.1.220" class="w-full px-3 py-2 bg-slate-50 dark:bg-[#222] border border-slate-200 dark:border-[#333] text-slate-850 dark:text-white rounded-xl text-xs focus:outline-none" />
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="window.closePrinterWizard()" class="w-1/2 py-2.5 bg-slate-100 dark:bg-[#222] hover:bg-slate-200 dark:hover:bg-[#333] text-slate-800 dark:text-white text-xs font-bold rounded-xl transition-all">
                    Cancel
                </button>
                <button type="button" onclick="window.applyPrinterWizard()" class="w-1/2 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-emerald-500/10">
                    Apply Setting
                </button>
            </div>
        </div>
    </div>

    <script>
    window.openPrinterWizard = function(type) {
        const modal = document.getElementById('printer-wizard-modal');
        const titleText = document.getElementById('wizard-title-text');
        const instructions = document.getElementById('wizard-instructions');
        const inputLabel = document.getElementById('wizard-input-label');
        const inputField = document.getElementById('wizard-input-field');
        const typeVal = document.getElementById('wizard-type-val');

        if (!modal) return;
        if (typeVal) typeVal.value = type;
        modal.classList.remove('hidden');

        if (type === 'network') {
            if (titleText) titleText.innerText = 'Network (LAN/Wi-Fi) Printer Guide';
            if (inputLabel) inputLabel.innerText = 'Printer IP Address';
            if (inputField) inputField.placeholder = 'e.g. 192.168.1.220';
            
            if (instructions) {
                instructions.innerHTML = `
                    <li class="mb-1.5 text-slate-200">Turn <strong class="text-white">OFF</strong> the printer power switch.</li>
                    <li class="mb-1.5 text-slate-200">Press and hold the <strong class="text-white">FEED</strong> button.</li>
                    <li class="mb-1.5 text-slate-200">While holding <strong class="text-white">FEED</strong>, turn the power switch <strong class="text-white">ON</strong>.</li>
                    <li class="mb-1.5 text-slate-200">Keep holding <strong class="text-white">FEED</strong> for 3 seconds until it prints, then let go.</li>
                    <li class="mb-1.5 text-slate-200">Find the <strong class="text-white">IP Address</strong> on the printed paper, type it below, and click Apply.</li>
                `;
            }
        } else {
            if (titleText) titleText.innerText = 'USB / Shared Printer Guide';
            if (inputLabel) inputLabel.innerText = 'Windows Share Name';
            if (inputField) inputField.placeholder = 'e.g. ZKP8016';

            if (instructions) {
                instructions.innerHTML = `
                    <li class="mb-1.5 text-slate-200">Plug the printer into the PC using the USB cable.</li>
                    <li class="mb-1.5 text-slate-200">Open Windows <strong class="text-white">Control Panel > Devices and Printers</strong>.</li>
                    <li class="mb-1.5 text-slate-200">Note the exact printer driver name (e.g. <code class="bg-[#222] dark:bg-black/50 px-1.5 py-0.5 rounded text-emerald-400 font-bold border border-slate-800">ZKP8016</code>).</li>
                    <li class="mb-1.5 text-slate-200">Right-click printer ➜ properties ➜ <strong class="text-white">Sharing</strong> ➜ share it with a name.</li>
                    <li class="mb-1.5 text-slate-200">Enter the name or path below (e.g. <code class="bg-[#222] dark:bg-black/50 px-1.5 py-0.5 rounded text-emerald-400 font-bold border border-slate-800">ZKP8016</code> or <code class="bg-[#222] dark:bg-black/50 px-1.5 py-0.5 rounded text-emerald-400 font-bold border border-slate-800">smb://192.168.1.15/CashierPrinter</code>) and click Apply.</li>
                `;
            }
        }
    };

    window.closePrinterWizard = function() {
        const modal = document.getElementById('printer-wizard-modal');
        if (modal) modal.classList.add('hidden');
    };

    window.applyPrinterWizard = function() {
        const typeEl = document.getElementById('wizard-type-val');
        const targetEl = document.getElementById('wizard-target-select');
        const fieldEl = document.getElementById('wizard-input-field');

        if (!fieldEl) return;
        const type = typeEl ? typeEl.value : 'network';
        const target = targetEl ? targetEl.value : 'kitchen';
        const value = fieldEl.value.trim();

        if (!value) {
            alert('Please enter a printer IP address or Windows share name.');
            return;
        }

        const pathSelect = target === 'bar' ? 'bot' : target;

        // 1. Direct DOM Updates with events to trigger Livewire model synchronization
        try {
            const pathInput = document.querySelector(`input[wire\\:model="${pathSelect}PrinterPath"]`);
            if (pathInput) {
                pathInput.value = value;
                pathInput.dispatchEvent(new Event('input'));
                pathInput.dispatchEvent(new Event('change'));
            }

            const typeSelect = document.querySelector(`select[wire\\:model.live="${pathSelect}PrinterType"]`);
            if (typeSelect) {
                typeSelect.value = type;
                typeSelect.dispatchEvent(new Event('change'));
            }
        } catch (domErr) {
            console.error('DOM manual update failed:', domErr);
        }

        // 2. Fallback to direct Livewire set method
        try {
            if (typeof @this !== 'undefined') {
                if (target === 'kitchen') {
                    @this.set('kitchenPrinterType', type);
                    @this.set('kitchenPrinterPath', value);
                } else if (target === 'cashier') {
                    @this.set('cashierPrinterType', type);
                    @this.set('cashierPrinterPath', value);
                } else if (target === 'bar') {
                    @this.set('botPrinterType', type);
                    @this.set('botPrinterPath', value);
                } else if (target === 'hotel') {
                    @this.set('hotelPrinterType', type);
                    @this.set('hotelPrinterPath', value);
                }
            }
        } catch (e) {
            console.warn('Livewire @this fallback failed:', e);
        }

        window.closePrinterWizard();
    };

    window.runClientSideNetworkScan = async function() {
        const statusBox = document.getElementById('js-scan-status');
        const progressMsg = document.getElementById('js-scan-progress-msg');
        const resultsList = document.getElementById('js-scanned-printers-list');
        const scanBtnText = document.getElementById('scan-btn-text');

        if (!statusBox || !resultsList) return;

        statusBox.classList.remove('hidden');
        resultsList.innerHTML = '';
        if (scanBtnText) scanBtnText.innerText = 'Scanning...';

        const subnets = ['192.168.1', '192.168.0', '192.168.2', '10.0.0', '192.168.100'];
        const foundIPs = [];

        for (let s = 0; s < subnets.length; s++) {
            const subnet = subnets[s];
            if (progressMsg) progressMsg.innerText = `Scanning Wi-Fi subnet ${subnet}.1 to ${subnet}.254...`;
            
            const batchSize = 30;
            for (let i = 1; i <= 254; i += batchSize) {
                const promises = [];
                for (let j = i; j < i + batchSize && j <= 254; j++) {
                    const ip = `${subnet}.${j}`;
                    promises.push(new Promise((resolve) => {
                        const controller = new AbortController();
                        const timer = setTimeout(() => controller.abort(), 250);

                        fetch(`http://${ip}:9100`, { mode: 'no-cors', signal: controller.signal })
                            .then(() => { clearTimeout(timer); resolve(ip); })
                            .catch((err) => {
                                clearTimeout(timer);
                                if (err.name !== 'AbortError') {
                                    resolve(ip);
                                } else {
                                    resolve(null);
                                }
                            });
                    }));
                }
                const res = await Promise.all(promises);
                res.filter(Boolean).forEach(ip => {
                    if (!foundIPs.includes(ip)) foundIPs.push(ip);
                });
            }
        }

        statusBox.classList.add('hidden');
        if (scanBtnText) scanBtnText.innerText = '🔍 Auto-Scan Wi-Fi Network';

        if (foundIPs.length === 0) {
            resultsList.innerHTML = `
                <div class="p-3 bg-amber-50 dark:bg-amber-950/20 text-amber-800 dark:text-amber-300 text-xs rounded-xl border border-amber-200">
                    ℹ️ <strong>Auto-scan finished:</strong> No open TCP 9100 printer ports responded automatically. You can directly enter your printer IP (e.g. <code>192.168.1.100:9100</code>) in the input fields below or click <strong>🔌 Select USB Driver</strong>.
                </div>
            `;
        } else {
            const firstIp = `${foundIPs[0]}:9100`;
            window.assignPrinterIp(firstIp, 'network', 'kitchen', false);
            window.assignPrinterIp(firstIp, 'network', 'cashier', false);

            let html = `
                <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-300 dark:border-emerald-800/50 rounded-xl space-y-2.5">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold text-emerald-800 dark:text-emerald-300 flex items-center gap-1.5">
                            ✅ <span>Auto-Discovered Printer: <strong>${firstIp}</strong> (Auto-Filled into Kitchen & Cashier fields below!)</span>
                        </p>
                    </div>
                    <p class="text-[11px] text-emerald-700 dark:text-emerald-400">Click <strong>"Save Settings"</strong> at the bottom of the page to apply.</p>
                    <div class="space-y-2 pt-1 border-t border-emerald-200 dark:border-emerald-800/40">
            `;
            foundIPs.forEach(ip => {
                const fullPath = `${ip}:9100`;
                html += `
                    <div class="p-2.5 bg-white dark:bg-[#1E1E1E] border border-emerald-200 dark:border-neutral-800 rounded-lg flex flex-wrap items-center justify-between gap-2 shadow-sm">
                        <span class="text-xs font-mono font-bold text-slate-800 dark:text-white">🖨️ ${fullPath}</span>
                        <div class="flex items-center gap-1.5">
                            <button type="button" onclick="window.assignPrinterIp('${fullPath}', 'network', 'kitchen')" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold rounded-md transition-colors cursor-pointer">🔪 Assign Kitchen</button>
                            <button type="button" onclick="window.assignPrinterIp('${fullPath}', 'network', 'cashier')" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold rounded-md transition-colors cursor-pointer">💰 Assign Cashier</button>
                            <button type="button" onclick="window.assignPrinterIp('${fullPath}', 'network', 'hotel')" class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold rounded-md transition-colors cursor-pointer">🏨 Assign Hotel</button>
                            <button type="button" onclick="window.assignPrinterIp('${fullPath}', 'network', 'bot')" class="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white text-[11px] font-bold rounded-md transition-colors cursor-pointer">🍸 Assign Bar</button>
                        </div>
                    </div>
                `;
            });
            html += `</div></div>`;
            resultsList.innerHTML = html;
        }
    };

    window.assignPrinterIp = function(value, type, target, showAlert = true) {
        const pathSelect = target === 'bar' ? 'bot' : target;
        
        try {
            let lw = document.querySelector('[wire\\:id]');
            if (lw && window.Livewire) {
                let component = window.Livewire.find(lw.getAttribute('wire:id'));
                if (component) {
                    if (typeof component.$call === 'function') {
                        component.$call('assignPrinterConfig', target, type, value);
                    } else if (typeof component.call === 'function') {
                        component.call('assignPrinterConfig', target, type, value);
                    } else if (typeof component.assignPrinterConfig === 'function') {
                        component.assignPrinterConfig(target, type, value);
                    }
                }
            }
        } catch (e) {
            console.warn('Livewire component call failed:', e);
        }

        if (showAlert) {
            const inputEl = document.querySelector(`input[wire\\:model="${pathSelect}PrinterPath"]`);
            if (inputEl) {
                inputEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                inputEl.classList.add('ring-2', 'ring-emerald-500');
                setTimeout(() => inputEl.classList.remove('ring-2', 'ring-emerald-500'), 2500);
            }
        }
    };

    window.showUsbPresetsModal = function() {
        // Unused
    };

    window.assignUsbPreset = function(shareName) {
        const path = 'smb://127.0.0.1/' + shareName;
        window.assignPrinterIp(path, 'usb', 'kitchen', false);
        window.assignPrinterIp(path, 'usb', 'cashier', true);
    };

    window.triggerClientSideTestPrint = function(target) {
        let iframe = document.getElementById('test-print-iframe');
        if (iframe) {
            iframe.remove();
        }
        iframe = document.createElement('iframe');
        iframe.id = 'test-print-iframe';
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '10px';
        iframe.style.height = '10px';
        iframe.style.border = '0';
        iframe.style.opacity = '0';
        document.body.appendChild(iframe);

        const doc = iframe.contentWindow.document;
        doc.open();
        doc.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Printer Test - DRestro POS</title>
                <style>
                    @page { size: 80mm auto; margin: 0; }
                    body { font-family: 'Courier New', monospace; width: 72mm; margin: 0 auto; padding: 12px 6px; text-align: center; color: #000; }
                    h2 { margin: 4px 0; font-size: 18px; font-weight: bold; }
                    p { margin: 2px 0; font-size: 12px; }
                    .dash { border-bottom: 1px dashed #000; margin: 6px 0; }
                    .row { display: flex; justify-content: space-between; font-size: 12px; text-align: left; }
                </style>
            </head>
            <body>
                <h2>DRESTRO POS</h2>
                <p style="font-weight:bold;">*** TEST PRINT ***</p>
                <p>Target: ${target.toUpperCase()} PRINTER</p>
                <div class="dash"></div>
                <p>Date: ${new Date().toLocaleDateString()} ${new Date().toLocaleTimeString()}</p>
                <div class="dash"></div>
                <div class="row"><span>1x Test KOT/Bill Item</span><span>Rs. 100</span></div>
                <div class="row"><span>1x Sample Order Item</span><span>Rs. 250</span></div>
                <div class="dash"></div>
                <p style="font-weight:bold; font-size:14px;">PRINTER WORKING OK ✅</p>
            </body>
            </html>
        `);
        doc.close();

        setTimeout(function() {
            try {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            } catch (err) {
                console.warn('Iframe print fallback triggered:', err);
                window.print();
            }
        }, 300);
    };
    </script>
</div>
