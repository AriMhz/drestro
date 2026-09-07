<div 
    x-data="{
        pin: @entangle('pin'),
        loading: false,
        appendNumber(num) {
            if (this.pin.length < 10) {
                this.pin += num.toString();
            }
        },
        removeNumber() {
            this.pin = this.pin.slice(0, -1);
        },
        submitPin() {
            if (this.pin.length > 0) {
                this.loading = true;
                $wire.unlock(this.pin);
            }
        }
    }"
    @screen-unlocked.window="isLocked = false; loading = false;"
    @pin-invalid.window="loading = false; setTimeout(() => { $refs.shakeBox.classList.add('animate-shake'); setTimeout(() => $refs.shakeBox.classList.remove('animate-shake'), 500); }, 50)"
    class="w-full max-w-sm mx-auto"
    x-ref="shakeBox"
>
    <div class="text-center mb-8">
        <div class="w-20 h-20 bg-slate-800/50 rounded-full mx-auto mb-4 flex items-center justify-center border border-slate-700/50 shadow-inner">
            <svg class="w-10 h-10 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
        </div>
        <h2 class="text-2xl font-black text-white tracking-tight">Screen Locked</h2>
        <p class="text-sm font-bold text-slate-400 mt-2">Enter PIN for {{ auth()->user()->name }} to resume.</p>
    </div>

    @if ($error)
        <div class="bg-red-500/10 border border-red-500/30 text-red-400 p-3 rounded-xl mb-6 text-sm font-bold text-center">
            {{ $error }}
        </div>
    @endif

    <!-- PIN Display -->
    <div class="bg-slate-900/50 border-2 border-slate-700/50 h-16 rounded-2xl mb-6 flex items-center justify-center space-x-3 overflow-hidden shadow-inner">
        <template x-for="(digit, index) in Array.from({length: Math.max(4, pin.length)})" :key="index">
            <div class="w-4 h-4 rounded-full transition-all duration-200" 
                 :class="index < pin.length ? 'bg-emerald-400 scale-100 shadow-[0_0_10px_rgba(52,211,153,0.5)]' : 'bg-slate-700 scale-75'"></div>
        </template>
    </div>

    <!-- Number Pad -->
    <div class="grid grid-cols-3 gap-3 mb-6">
        <template x-for="n in 9">
            <button @click="appendNumber(n)" type="button" class="h-16 bg-slate-800/80 hover:bg-slate-700 border border-slate-700/50 rounded-2xl text-2xl font-black text-white transition-all active:scale-95 shadow-lg">
                <span x-text="n"></span>
            </button>
        </template>
        
        <button @click="removeNumber()" type="button" class="h-16 bg-slate-800/50 hover:bg-slate-700 border border-slate-700/50 rounded-2xl text-xl font-bold text-slate-400 transition-all active:scale-95 flex items-center justify-center">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z"></path></svg>
        </button>
        
        <button @click="appendNumber(0)" type="button" class="h-16 bg-slate-800/80 hover:bg-slate-700 border border-slate-700/50 rounded-2xl text-2xl font-black text-white transition-all active:scale-95 shadow-lg">
            0
        </button>
        
        <button @click="submitPin()" type="button" class="h-16 bg-emerald-600 hover:bg-emerald-500 border border-emerald-500/50 rounded-2xl text-xl font-black text-white transition-all active:scale-95 flex items-center justify-center shadow-[0_0_15px_rgba(5,150,105,0.4)] relative">
            <span x-show="!loading">OK</span>
            <svg x-show="loading" class="animate-spin h-6 w-6 text-white absolute" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </button>
    </div>
    
    <!-- Logout / Switch User -->
    <div class="text-center mt-8">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="text-sm font-bold text-slate-400 hover:text-white transition-colors bg-slate-800/50 px-6 py-2.5 rounded-full border border-slate-700/50 inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Switch User / Logout
            </button>
        </form>
    </div>

    <!-- Shake Animation Styles -->
    <style>
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        .animate-shake {
            animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
        }
    </style>
</div>
