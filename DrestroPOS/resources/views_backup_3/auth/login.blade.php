<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - Drestro POS</title>
        <!-- Offline Fonts -->
        <link rel="stylesheet" href="{{ asset('fonts/inter/inter.css') }}">
        @vite(['resources/css/app.css'])
    </head>
    <body class="bg-slate-50 font-[Inter] antialiased text-slate-800 flex items-center justify-center min-h-screen relative overflow-hidden">
        <!-- Abstract Background -->
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-500 rounded-full blur-3xl opacity-10"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-teal-500 rounded-full blur-3xl opacity-10"></div>

        <div class="w-full max-w-md bg-white/80 backdrop-blur-xl p-8 sm:p-10 rounded-3xl shadow-[0_8px_40px_-12px_rgba(0,0,0,0.1)] border border-slate-200/50 z-10 mx-4">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-black bg-gradient-to-r from-emerald-600 to-teal-500 bg-clip-text text-transparent tracking-tight">DRESTRO</h1>
                <p class="text-slate-500 font-medium mt-1">Sign in to manage your restaurant</p>
            </div>

            <form method="POST" action="{{ route('login.post') }}" class="space-y-6">
                @csrf
                @if($errors->any())
                    <div class="bg-red-50 text-red-600 p-4 rounded-2xl text-sm font-semibold border border-red-100 flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-red-50 text-red-600 p-4 rounded-2xl text-sm font-semibold border border-red-100 flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">Email or Username</label>
                    <input type="text" name="login" value="{{ old('login') }}" required autofocus class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white outline-none transition-all font-medium placeholder-slate-400" placeholder="admin@drestro.com or username">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">Password</label>
                    <input type="password" name="password" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white outline-none transition-all font-medium placeholder-slate-400" placeholder="••••••••">
                </div>

                <button type="submit" class="w-full py-4 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white rounded-2xl font-bold shadow-[0_8px_20px_-8px_rgba(16,185,129,0.5)] hover:shadow-[0_8px_30px_-8px_rgba(16,185,129,0.6)] hover:-translate-y-0.5 active:scale-[0.98] transition-all text-lg tracking-wide">
                    Sign In
                </button>

                <div class="pt-4 border-t border-slate-100 text-center">
                    <a href="{{ route('pin-login') }}" class="inline-flex items-center gap-2 text-emerald-600 font-bold hover:text-emerald-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A10.003 10.003 0 0012 3c1.223 0 2.383.219 3.46.619m-3.46 7.412c0 1.258-.334 2.438-.916 3.454m0 0a10.01 10.01 0 01-2.46 3.454M12 11a1 1 0 112 0 22.05 22.05 0 01-1.916 9.144m-2.46-3.454A11.25 11.25 0 013 11c0-2.206.591-4.27 1.625-6.046M12 11v2m0-6V5m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V7m6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V7"></path></svg>
                        Switch to PIN Login
                    </a>
                </div>
            </form>
        </div>
    </body>
</html>
