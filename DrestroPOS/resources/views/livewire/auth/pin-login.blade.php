<div class="min-h-screen flex flex-col items-center justify-center bg-slate-900 p-6"
     x-data="{
        pin: '',
        error: @entangle('error'),
        append(num) {
            if (this.pin.length < 4) {
                this.pin += num.toString();
                this.error = '';
            }
            if (this.pin.length === 4) {
                $wire.login(this.pin);
            }
        },
        clear() {
            this.pin = '';
            this.error = '';
        }
     }"
     @keyup.window="
        if ($event.key >= '0' && $event.key <= '9') {
            append($event.key);
        } else if ($event.key === 'Backspace') {
            pin = pin.slice(0, -1);
            error = '';
        } else if ($event.key === 'Escape') {
            clear();
        }
     "
     @pin-invalid.window="pin = ''">
    
    <div class="max-w-md w-full">
        <!-- Logo & Title -->
        <div class="text-center mb-10">
            <h1 class="text-4xl font-black text-white tracking-tighter mb-2">DRESTRO <span class="text-emerald-500">POS</span></h1>
            <p class="text-slate-400 font-medium">Enter your 4-digit PIN to start</p>
        </div>

        <!-- PIN Display -->
        <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-[2.5rem] p-10 shadow-2xl mb-8">
            <div class="flex justify-center gap-4 mb-6">
                <template x-for="i in 4" :key="i">
                    <div class="w-12 h-16 rounded-2xl border-2 flex items-center justify-center transition-all duration-300"
                         :class="pin.length >= i ? 'bg-emerald-500 border-emerald-400 shadow-[0_0_20px_rgba(16,185,129,0.3)] scale-110' : 'bg-slate-900/50 border-slate-700'">
                        <div x-show="pin.length >= i" class="w-3 h-3 bg-white rounded-full dark:bg-slate-900" x-transition></div>
                    </div>
                </template>
            </div>

            <div class="h-6">
                <template x-if="error">
                    <p class="text-red-400 text-sm font-bold text-center animate-bounce" x-text="error"></p>
                </template>
            </div>

            <!-- Numpad -->
            <div class="grid grid-cols-3 gap-4 mt-6">
                @foreach([1,2,3,4,5,6,7,8,9] as $num)
                    <button @click="append({{ $num }})" type="button" class="h-20 bg-slate-700/30 hover:bg-slate-700/60 text-white text-2xl font-black rounded-3xl border border-slate-600/30 transition-all active:scale-90 flex items-center justify-center">
                        {{ $num }}
                    </button>
                @endforeach
                <button @click="clear()" type="button" class="h-20 bg-red-500/10 hover:bg-red-500/20 text-red-500 text-sm font-black rounded-3xl border border-red-500/20 transition-all active:scale-90 flex items-center justify-center">
                    CLEAR
                </button>
                <button @click="append(0)" type="button" class="h-20 bg-slate-700/30 hover:bg-slate-700/60 text-white text-2xl font-black rounded-3xl border border-slate-600/30 transition-all active:scale-90 flex items-center justify-center">
                    0
                </button>
                <a href="/login" class="h-20 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 text-sm font-black rounded-3xl border border-blue-500/20 transition-all active:scale-90 flex items-center justify-center">
                    EMAIL
                </a>
            </div>
        </div>

        <p class="text-center text-slate-500 text-xs font-bold uppercase tracking-widest dark:text-slate-400">Powered by Drestro POS</p>
    </div>
</div>
