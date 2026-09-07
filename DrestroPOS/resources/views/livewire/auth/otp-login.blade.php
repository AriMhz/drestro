<div class="min-h-screen flex flex-col items-center justify-center bg-slate-900 p-6">
    <div class="max-w-md w-full">
        <!-- Logo & Title -->
        <div class="text-center mb-10">
            <h1 class="text-4xl font-black text-white tracking-tighter mb-2">DRESTRO <span class="text-emerald-500">POS</span></h1>
            <p class="text-slate-400 font-medium">Sign in with SMS / OTP Code</p>
        </div>

        <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-[2.5rem] p-10 shadow-2xl mb-8">
            
            <!-- Error Alert -->
            @if ($error)
                <div class="bg-red-500/10 text-red-400 p-4 rounded-2xl text-sm font-semibold border border-red-500/20 mb-6 flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span>{{ $error }}</span>
                </div>
            @endif

            <!-- Success Alert -->
            @if ($success)
                <div class="bg-emerald-500/10 text-emerald-400 p-4 rounded-2xl text-sm font-semibold border border-emerald-500/20 mb-6 flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ $success }}</span>
                </div>
            @endif

            <!-- Step 1: Request OTP -->
            @if ($step === 1)
                <form wire:submit.prevent="requestOtp" class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2 uppercase tracking-wide">Mobile Number</label>
                        <input type="text" wire:model="phone" required autofocus 
                               class="w-full px-5 py-3.5 bg-slate-900 border border-slate-700 rounded-2xl text-white outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-semibold placeholder-slate-500" 
                               placeholder="e.g. 9841XXXXXX">
                    </div>

                    <button type="submit" wire:loading.attr="disabled"
                            class="w-full py-4 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white rounded-2xl font-bold shadow-[0_8px_20px_-8px_rgba(16,185,129,0.5)] hover:shadow-[0_8px_30px_-8px_rgba(16,185,129,0.6)] hover:-translate-y-0.5 active:scale-[0.98] transition-all text-md tracking-wide flex items-center justify-center gap-2">
                        <span wire:loading.remove>Send OTP Code</span>
                        <span wire:loading>Sending OTP...</span>
                    </button>
                </form>
            @endif

            <!-- Step 2: Verify OTP -->
            @if ($step === 2)
                <form wire:submit.prevent="verifyOtp" class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-300 mb-2 uppercase tracking-wide">6-Digit OTP Code</label>
                        <input type="text" wire:model="otp" required autofocus maxlength="6"
                               class="w-full px-5 py-3.5 bg-slate-900 border border-slate-700 rounded-2xl text-white text-center text-2xl tracking-widest outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-black placeholder-slate-600" 
                               placeholder="••••••">
                    </div>

                    <button type="submit" wire:loading.attr="disabled"
                            class="w-full py-4 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white rounded-2xl font-bold shadow-[0_8px_20px_-8px_rgba(16,185,129,0.5)] hover:shadow-[0_8px_30px_-8px_rgba(16,185,129,0.6)] hover:-translate-y-0.5 active:scale-[0.98] transition-all text-md tracking-wide flex items-center justify-center gap-2">
                        <span wire:loading.remove>Verify & Login</span>
                        <span wire:loading>Verifying...</span>
                    </button>

                    <button type="button" wire:click="backToPhone" class="w-full text-center text-sm font-bold text-slate-400 hover:text-white transition-all uppercase tracking-wider">
                        ← Change Phone Number
                    </button>
                </form>
            @endif

            <div class="flex items-center my-6">
                <div class="flex-grow border-t border-slate-700"></div>
                <span class="px-3 text-xs text-slate-500 font-bold uppercase tracking-wider bg-transparent">or</span>
                <div class="flex-grow border-t border-slate-700"></div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <a href="/login" class="py-3.5 border border-slate-700 hover:bg-slate-800 text-slate-300 rounded-2xl font-bold transition-all text-sm flex items-center justify-center gap-2">
                    EMAIL LOGIN
                </a>
                <a href="/pin-login" class="py-3.5 border border-slate-700 hover:bg-slate-800 text-slate-300 rounded-2xl font-bold transition-all text-sm flex items-center justify-center gap-2">
                    PIN LOGIN
                </a>
            </div>
        </div>

        <p class="text-center text-slate-500 text-xs font-bold uppercase tracking-widest">Powered by Drestro POS</p>
    </div>
</div>
