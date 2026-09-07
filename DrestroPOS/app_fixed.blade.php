<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $title ?? 'Drestro Admin' }}</title>
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#10b981">
        
        <!-- Offline Fonts -->
        <link rel="stylesheet" href="{{ asset('fonts/inter/inter.css') }}">
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        <script>
            if (localStorage.getItem('pos_theme') === 'dark' || (!('pos_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
            function togglePosTheme() {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('pos_theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('pos_theme', 'dark');
                }
            }
        </script>
    </head>

<body class="bg-slate-50 dark:bg-[#111111] dark:text-slate-100 font-[Inter] antialiased text-slate-800 transition-colors">
        <div x-data="{ sidebarOpen: false }">
            <div class="flex h-screen overflow-hidden">
                <!-- Mobile Sidebar Overlay -->
                <div x-show="sidebarOpen" x-cloak class="fixed inset-0 bg-black/50 z-40 lg:hidden" @click="sidebarOpen = false"></div>

                <!-- Sidebar -->
                <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" class="fixed lg:relative inset-y-0 left-0 z-50 w-64 bg-white dark:bg-[#0a0a0a] border-r border-slate-200 dark:border-slate-800 flex flex-col transition-transform duration-300 lg:transform-none">
                    @php $restaurant = current_restaurant(); @endphp
                    <div class="h-14 sm:h-16 flex items-center px-6 border-b border-slate-200 dark:border-slate-800 gap-3">
                        <img src="/images/logo.svg" class="h-6 w-auto object-contain dark:hidden" alt="DRestro Logo">
                        <img src="/images/logo-light.svg" class="h-6 w-auto object-contain hidden dark:block" alt="DRestro Logo">
                        <button type="button" @click="sidebarOpen = false" class="lg:hidden ml-auto p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-white rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    @php 
                        $lf = $activeLicense['features'] ?? []; 
                        $isSuperAdmin = auth()->user()->role === 'super_admin';
                        $hasAllowedPages = !empty(auth()->user()->allowed_pages);
                        
                        $expAt = $activeLicense['expires_at'] ?? null;
                        $isExpired = false;
                        if (!$expAt) {
                            $isExpired = true;
                        } else {
                            $dLeft = (int) now()->diffInDays(\Carbon\Carbon::parse($expAt), false);
                            if ($dLeft < 0) {
                                $isExpired = true;
                            }
                        }
                        
                        // Waiter Role Checks
                        $isWaiter = auth()->user()->role === 'waiter';
                        
                        // Determine default landing page
                        $homeUrl = '/admin';
                        if ($isWaiter) {
                            $homeUrl = '/staff/waiter';
                        }
                    @endphp

                    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                        @if($isWaiter)
                            <a href="/staff/waiter" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('staff/waiter') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('staff/waiter') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                Waiter Dashboard
                            </a>
                            <a href="/staff/waiter/orders" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('staff/waiter/orders') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('staff/waiter/orders') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                Take Order
                            </a>
                        @else
                            @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                            <a href="{{ $homeUrl }}" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('admin') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('admin') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                Dashboard
                            </a>
                            @endif

                            @if($isSuperAdmin || in_array('menu', $lf))
                            <a href="/admin/menu" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('admin/menu') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('admin/menu') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                                Menu Manager
                            </a>
                            @endif

                            @if($isSuperAdmin || in_array('tables', $lf))
                            <a href="/admin/tables" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('admin/tables') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('admin/tables') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                Restaurant Tables
                            </a>
                            @endif

                            @if($isSuperAdmin || in_array('rooms', $lf))
                            <a href="/admin/rooms" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('admin/rooms') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('admin/rooms') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                Hotel Room Manager
                            </a>
                            @endif

                            @if($isSuperAdmin || in_array('inventory', $lf))
                            <a href="/admin/inventory" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('admin/inventory') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('admin/inventory') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                Inventory
                            </a>
                            @endif

                            <div class="pt-4 pb-2">
                                <p class="px-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Staff Panels</p>
                            </div>
                            
                            @php
                                $canAccess = function($permission) use ($hasAllowedPages) {
                                    if (auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin') return true;
                                    if ($hasAllowedPages && in_array($permission, auth()->user()->allowed_pages ?? [])) return true;
                                    return false;
                                };
                            @endphp

                            @if($canAccess('take_order'))
                            <a href="/staff/take-order" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('staff/take-order') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('staff/take-order') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Take Restaurant Order
                            </a>
                            @endif

                            @if($canAccess('hotel_cashier') && ($isSuperAdmin || in_array('rooms', $lf)))
                            <a href="/staff/hotel" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('staff/hotel') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('staff/hotel') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                Take Room Service
                            </a>
                            <a href="/staff/hotel/cashier" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('staff/hotel/cashier') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('staff/hotel/cashier') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Hotel Reception Cashier
                            </a>
                            @endif

                            @if($canAccess('cashier'))
                            <a href="/staff/cashier" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('staff/cashier') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('staff/cashier') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2-2v14a2 2 0 002 2z"></path></svg>
                                Cashier Dashboard
                            </a>
                            @endif

                            @if($canAccess('waiter_panel'))
                            <a href="/staff/waiters" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('staff/waiters') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('staff/waiters') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2-2v10a2 2 0 002 2z"></path></svg>
                                Waiter Dashboard
                            </a>
                            @endif

                            @if($canAccess('kitchen'))
                            <a href="/staff/kitchen" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('staff/kitchen') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('staff/kitchen') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                Kitchen Display
                            </a>
                            @endif

                            <div class="pt-4 pb-2">
                                <p class="px-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Management</p>
                            </div>
                            
                            @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                            <a href="/admin/staff" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('admin/staff') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('admin/staff') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                Staff Accounts
                            </a>
                            
                            <a href="/admin/reports" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('admin/reports') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('admin/reports') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Reports & Analytics
                            </a>
                            @endif

                            @if($isSuperAdmin || in_array('support', $lf))
                            <a href="/admin/support" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('admin/support') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('admin/support') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                Priority Support
                            </a>
                            @endif

                            @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                            <a href="/admin/settings" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('admin/settings') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('admin/settings') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                System Settings
                            </a>
                            <a href="/admin/license" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('admin/license') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('admin/license') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                License Manager
                            </a>
                            @endif
                        @endif
                        
                        <div class="pt-4 mt-4 border-t border-slate-200 dark:border-slate-800">
                            <form action="/logout" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center px-3 py-2.5 rounded-xl font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </nav>

                    <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-lg shrink-0">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="ml-3 overflow-hidden">
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-200 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ ucfirst(auth()->user()->role) }}</p>
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- Main Content -->
                <main class="flex-1 flex flex-col min-w-0 bg-white dark:bg-[#0a0a0a]">
                    <!-- Top Header -->
                    <header class="h-14 sm:h-16 border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-[#0a0a0a]/80 backdrop-blur-md flex items-center justify-between px-4 sm:px-6 z-30 sticky top-0">
                        <div class="flex items-center gap-3">
                            <button type="button" @click="sidebarOpen = true" class="lg:hidden p-1.5 -ml-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-white rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                            </button>
                            <h1 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-slate-100 truncate">{{ $title ?? 'Dashboard' }}</h1>
                            @if(isset($isExpired) && $isExpired)
                                <span class="px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-600 border border-red-200 ml-2 animate-pulse">EXPIRED</span>
                            @endif
                        </div>
                        
                        <div class="flex items-center gap-2 sm:gap-4">
                            @php
                                $now = now();
                                $dateStrForClock = $now->format('D, M j');
                            @endphp
                            
                            <div class="hidden md:flex items-center text-sm font-bold text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-800/50 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 font-mono" id="live-header-clock">
                                {{ strtoupper($dateStrForClock) }} | {{ $now->format('h:i:s A') }}
                            </div>

                            <script>
                                (function() {
                                    function updateClock() {
                                        const clockEl = document.getElementById('live-header-clock');
                                        if (clockEl) {
                                            const now = new Date();
                                            const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
                                            clockEl.textContent = "{{ strtoupper($dateStrForClock) }} | " + timeStr;
                                        }
                                    }
                                    updateClock();
                                    setInterval(updateClock, 1000);
                                })();
                            </script>

                            <button onclick="togglePosTheme()" class="hidden sm:flex p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-white bg-slate-50 hover:bg-slate-100 dark:bg-[#111] dark:hover:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-800 transition-colors shadow-sm" title="Toggle Dark Mode">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                            </button>

                            @if(request()->is('admin/settings'))
                            <button type="submit" form="settings-form" class="h-8 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] uppercase tracking-wider rounded-xl shadow-md shadow-emerald-600/20 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer leading-none">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                <span>Save Settings</span>
                            </button>
                            @endif
                        </div>
                    </header>

                    <!-- Page Content -->
                    <div class="flex-1 overflow-y-auto p-4 pb-24 sm:p-6 sm:pb-6 lg:p-8 lg:pb-8">
                        @if($isLocked)
                            <div class="flex flex-col items-center justify-center h-full text-center">
                                <div class="w-24 h-24 bg-red-100 text-red-500 rounded-full flex items-center justify-center mb-6 shadow-xl shadow-red-500/20">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                </div>
                                <h2 class="text-3xl font-black text-slate-800 mb-2">Subscription Expired</h2>
                                <p class="text-lg text-slate-500 max-w-md mb-8">
                                    Your DRestro POS trial/subscription has expired and the system has been locked. Please renew your subscription online to instantly restore access.
                                </p>
                                <a href="https://drestro.com/dashboard/billing" target="_blank" class="px-8 py-3.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold text-lg transition-all shadow-lg shadow-emerald-600/20 flex items-center gap-2 active:scale-95">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    Renew Subscription Online
                                </a>
                            </div>
                        @else
                            {{ $slot }}
                        @endif
                    </div>
                </main>

                <!-- Mobile Bottom Navigation -->
                <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 flex items-center justify-around h-16 px-1 z-50 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] pb-safe dark:bg-[#1a1a1a] dark:border-[#333]">
                    
                    @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                    <a href="/admin" class="flex flex-col items-center justify-center w-full h-full space-y-1 text-slate-500 hover:text-emerald-600 transition-colors {{ request()->is('admin') ? 'text-emerald-600 dark:text-emerald-500' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Home</span>
                    </a>
                    @endif

                    @if(!$isLocked && $canAccess('take_order'))
                    <a href="/staff/take-order" class="flex flex-col items-center justify-center w-full h-full space-y-1 text-slate-500 hover:text-emerald-600 transition-colors {{ request()->is('staff/take-order') ? 'text-emerald-600 dark:text-emerald-500' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-[10px] font-bold uppercase tracking-wider">POS</span>
                    </a>
                    @endif

                    @if(!$isLocked && $canAccess('restaurant_tables'))
                    <a href="/admin/tables" class="flex flex-col items-center justify-center w-full h-full space-y-1 text-slate-500 hover:text-emerald-600 transition-colors {{ request()->is('admin/tables') ? 'text-emerald-600 dark:text-emerald-500' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Tables</span>
                    </a>
                    @endif
                    
                    <!-- Dark Mode -->
                    <button onclick="togglePosTheme()" class="flex flex-col items-center justify-center w-full h-full space-y-1 text-slate-500 hover:text-emerald-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Theme</span>
                    </button>

                    <!-- Menu / Sidebar Toggle -->
                    <button @click="sidebarOpen = true" class="flex flex-col items-center justify-center w-full h-full space-y-1 text-slate-500 hover:text-emerald-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        <span class="text-[10px] font-bold uppercase tracking-wider">More</span>
                    </button>

                </nav>
            </div>
            
            <style>
                [x-cloak] { display: none !important; }
                @media print {
                    @page { margin: 1cm; size: A4 portrait; }
                    body { background-color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    aside, header, .print-hidden { display: none !important; }
                    main { height: auto !important; overflow: visible !important; }
                    .flex-1.overflow-y-auto { overflow: visible !important; padding: 0 !important; }
                    .grid { gap: 1rem !important; }
                    .shadow-sm { box-shadow: none !important; border: 1px solid #e2e8f0 !important; }
                }
            </style>

            <script>
                function downloadPDF(filename) {
                    alert('To save this report as a PDF:\n\n1. The Print Dialog will now open.\n2. Change your Printer "Destination" to "Save as PDF".\n3. Click Save!');
                    window.print();
                }
            </script>

            @livewireScripts
        </div>
    </body>
