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
    <body class="bg-slate-50 font-[Inter] antialiased text-slate-800 flex items-center justify-center min-h-screen relative overflow-hidden dark:bg-slate-900 dark:text-slate-200">
        <!-- Abstract Background -->
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-500 rounded-full blur-3xl opacity-10"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-teal-500 rounded-full blur-3xl opacity-10"></div>

        <div class="w-full max-w-md bg-white/80 backdrop-blur-xl p-8 sm:p-10 rounded-3xl shadow-[0_8px_40px_-12px_rgba(0,0,0,0.1)] border border-slate-200/50 z-10 mx-4 dark:bg-slate-900/80">
            <div class="text-center mb-8">
                    <img src="{{ asset('images/logo.svg') }}" class="h-10 w-auto mx-auto mb-3 object-contain dark:hidden" alt="DRestro Logo">
                    <img src="{{ asset('images/logo-light.svg') }}" class="h-10 w-auto mx-auto mb-3 object-contain hidden dark:block" alt="DRestro Logo">
                <p class="text-slate-500 font-medium mt-1 dark:text-slate-400">Sign in to manage your restaurant</p>
            </div>

            <form method="POST" action="{{ route('login.post') }}" class="space-y-6">
                @csrf
                @if($errors->any())
                    <div class="bg-red-50 text-red-600 p-4 rounded-2xl text-sm font-semibold border border-red-100 flex items-start gap-3 dark:border-red-800/50 dark:text-red-400 dark:bg-red-900/20">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-red-50 text-red-600 p-4 rounded-2xl text-sm font-semibold border border-red-100 flex items-start gap-3 dark:border-red-800/50 dark:text-red-400 dark:bg-red-900/20">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide dark:text-slate-300">Email or Username</label>
                    <input type="text" name="login" value="{{ old('login') }}" required autofocus class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white outline-none transition-all font-medium placeholder-slate-400 dark:bg-slate-900 dark:border-slate-700" placeholder="admin@drestro.com or username">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide dark:text-slate-300">Password</label>
                    <input type="password" name="password" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white outline-none transition-all font-medium placeholder-slate-400 dark:bg-slate-900 dark:border-slate-700" placeholder="••••••••">
                </div>

                <button type="submit" class="w-full py-4 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white rounded-2xl font-bold shadow-[0_8px_20px_-8px_rgba(16,185,129,0.5)] hover:shadow-[0_8px_30px_-8px_rgba(16,185,129,0.6)] hover:-translate-y-0.5 active:scale-[0.98] transition-all text-lg tracking-wide">
                    Sign In
                </button>

                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('pin-login') }}" class="py-3.5 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-2xl font-bold transition-all flex items-center justify-center gap-2 text-sm">
                        PIN Login
                    </a>
                    <a href="{{ route('otp-login') }}" class="py-3.5 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-2xl font-bold transition-all flex items-center justify-center gap-2 text-sm">
                        SMS / OTP Login
                    </a>
                </div>

                <div class="flex items-center my-4">
                    <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
                    <span class="px-3 text-xs text-slate-400 font-bold uppercase tracking-wider bg-transparent">or</span>
                    <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
                </div>

                <a href="https://drestro.com/login" class="w-full py-3.5 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-2xl font-bold transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Continue with Google
                </a>


            </form>
        </div>
    </body>
</html>
