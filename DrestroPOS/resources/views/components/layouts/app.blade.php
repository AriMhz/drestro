<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $title ?? 'Drestro Admin' }}</title>
        <link rel="icon" type="image/svg+xml" href="/icon.svg">
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#10b981">
        
        <!-- Offline Fonts -->
        <link rel="stylesheet" href="{{ asset('fonts/inter/inter.css') }}">
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        <script>
            try {
                if (localStorage.getItem('pos_theme') === 'dark' || (!('pos_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {
                console.warn('localStorage access denied or unavailable', e);
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
        <script>
            function toggleMobileSidebar() {
                const sidebar = document.getElementById('mobile-sidebar');
                const overlay = document.getElementById('mobile-overlay');
                if (sidebar.classList.contains('-translate-x-full')) {
                    sidebar.classList.remove('-translate-x-full');
                    sidebar.classList.add('translate-x-0');
                    overlay.classList.remove('hidden');
                } else {
                    sidebar.classList.remove('translate-x-0');
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                }
            }
        </script>
        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>


<body class="bg-slate-50 dark:bg-[#111111] dark:text-slate-100 font-[Inter] antialiased text-slate-800 transition-colors">
    <div x-data="{
          isLocked: false,
          idleTimer: null,
          role: '{{ auth()->user()->role ?? '' }}',
          idleTimeLimit: 900000, // 15 minutes in ms
          resetTimer() {
              if (this.role === 'super_admin' || this.isLocked) return;
              clearTimeout(this.idleTimer);
              this.idleTimer = setTimeout(() => {
                  this.isLocked = true;
              }, this.idleTimeLimit);
          },
          init() {
              if (this.role !== 'super_admin') {
                  this.resetTimer();
                  window.addEventListener('mousemove', () => this.resetTimer());
                  window.addEventListener('keydown', () => this.resetTimer());
                  window.addEventListener('click', () => this.resetTimer());
                  window.addEventListener('scroll', () => this.resetTimer(), true);
              }
          }
      }">
        <div>
            <div class="flex h-screen overflow-hidden">
                <!-- Mobile Sidebar Overlay -->
                <div id="mobile-overlay" class="fixed inset-0 bg-black/50 lg:hidden hidden" onclick="toggleMobileSidebar()" style="z-index: 55;"></div>

                <!-- Sidebar -->
                <aside id="mobile-sidebar" class="-translate-x-full lg:translate-x-0 fixed lg:relative inset-y-0 left-0 w-64 bg-white dark:bg-[#1A1A1A] border-r border-slate-200 dark:border-[#333333] flex flex-col transition-transform duration-300 lg:transform-none" style="z-index: 60;">
                    @php $restaurant = current_restaurant(); @endphp
                    <div class="h-14 sm:h-16 flex items-center px-6 border-b border-slate-200 dark:border-[#333333] gap-3">
                        <img src="/images/logo.svg" class="h-6 w-auto object-contain dark:hidden" alt="DRestro Logo">
                        <img src="/images/logo-light.svg" class="h-6 w-auto object-contain hidden dark:block" alt="DRestro Logo">
                        <button type="button" onclick="toggleMobileSidebar()" class="lg:hidden ml-auto p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-white rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Restaurant Info & Active Plan Badge -->
                    @if($restaurant)
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-[#222222] bg-slate-50 dark:bg-[#111111] flex items-center gap-3">
                        @if($restaurant->logo)
                            <div class="w-12 h-12 rounded-full overflow-hidden border border-slate-200 dark:border-[#333333] shadow-sm shrink-0 bg-white flex items-center justify-center">
                                <img src="{{ asset('storage/' . $restaurant->logo) }}" class="w-full h-full object-contain p-1" alt="Logo">
                            </div>
                        @else
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#E53935] to-red-700 text-white flex items-center justify-center font-black text-sm shadow-sm shrink-0 uppercase border border-red-650/10">
                                {{ substr($restaurant->name, 0, 2) }}
                            </div>
                        @endif
                        <div class="overflow-hidden flex-1">
                            <h4 class="font-black text-slate-800 dark:text-white text-xs truncate" title="{{ $restaurant->name }}">
                                {{ $restaurant->name }}
                            </h4>
                            @if($restaurant->address)
                            <div class="flex items-center gap-1 mt-0.5 text-slate-500 dark:text-slate-400 text-[10px] truncate" title="{{ $restaurant->address }}">
                                <svg class="w-3 h-3 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span class="truncate">{{ $restaurant->address }}</span>
                            </div>
                            @else
                            <div class="flex items-center gap-1 mt-0.5 text-slate-400 dark:text-slate-500 text-[10px] italic">
                                <svg class="w-3 h-3 text-slate-400 dark:text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span>No Location Set</span>
                            </div>
                            @endif
                            @php 
                                $rawPlan = $activeLicense['plan'] ?? 'Free Plan';
                                if ($rawPlan === 'Free' || strtolower($rawPlan) === 'free' || str_contains(strtolower($rawPlan), 'trial')) {
                                    $planName = 'Free Plan';
                                } else {
                                    $planName = $rawPlan;
                                }
                                $isHotelOrResort = str_contains(strtolower($planName ?? ''), 'hotel') || 
                                                   str_contains(strtolower($restaurant->type ?? ''), 'hotel') || 
                                                   str_contains(strtolower($restaurant->type ?? ''), 'resort');
                            @endphp
                            <span class="inline-flex items-center gap-1 mt-1.5 px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider {{ str_contains(strtolower($planName), 'free') ? 'bg-emerald-600' : 'bg-red-600' }} text-white border border-transparent">
                                👑 {{ $planName }}
                            </span>
                        </div>
                    </div>
                    @endif
                    @php 
                        $lf = $activeLicense['features'] ?? []; 
                        $isSuperAdmin = auth()->user()->role === 'super_admin';
                        $hasAllowedPages = !empty(auth()->user()->allowed_pages);
                        
                        $expAt = $activeLicense['expires_at'] ?? null;
                        $isExpired = false;
                        $dLeft = null;
                        if ($expAt) {
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
                        
                        $canAccess = function($permission) use ($hasAllowedPages) {
                            if (auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin') return true;
                            if ($hasAllowedPages && in_array($permission, auth()->user()->allowed_pages ?? [])) return true;
                            return false;
                        };

                        $getLinkClass = function($isActive) {
                            return 'group flex items-center px-4 py-3 rounded-xl font-medium transition-all ' . 
                                ($isActive 
                                    ? 'bg-[#E53935] text-white shadow-[0_4px_12px_rgba(229,57,53,0.2)]' 
                                    : 'text-slate-600 dark:text-gray-400 hover:text-[#111111] dark:hover:text-white hover:bg-slate-100 dark:hover:bg-[#2A2A2A] dark:bg-[#222222]');
                        };
                        
                        $getIconClass = function($isActive) {
                            return 'w-5 h-5 mr-3 shrink-0 transition-colors ' . 
                                ($isActive 
                                    ? 'text-white' 
                                    : 'text-slate-400 dark:text-gray-400 group-hover:text-[#111111] dark:group-hover:text-white');
                        };
                    @endphp

                    <nav class="flex-1 min-h-0 overflow-y-auto py-6 px-4 space-y-2">
                        @if($canAccess('dashboard'))
                        @php $active = request()->is('admin') || (request()->is('staff/waiter') && $homeUrl == '/staff/waiter'); @endphp
                        <a href="{{ $homeUrl }}" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            Dashboard
                        </a>
                        @endif

                        @if($canAccess('menu_manager'))
                        @php $active = request()->is('admin/menus') || request()->is('admin/menus*'); @endphp
                        <a href="/admin/menus" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                            Menu Manager
                        </a>
                        @endif

                        @if($canAccess('restaurant_tables'))
                        @php $active = request()->is('admin/tables') || request()->is('admin/tables*'); @endphp
                        <a href="/admin/tables" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            Restaurant Tables
                        </a>
                        @endif

                        @if($canAccess('hotel_room_manager') && $isHotelOrResort)
                        @php $active = request()->is('admin/rooms') || request()->is('admin/rooms*'); @endphp
                        <a href="/admin/rooms" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            Hotel Room Manager
                        </a>
                        @endif

                        @if($canAccess('inventory') && !in_array(strtolower($planName ?? 'free'), ['basic', 'free']))
                        @php $active = request()->is('admin/inventory') || request()->is('admin/inventory*'); @endphp
                        <a href="/admin/inventory" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            Inventory
                        </a>
                        @endif

                        @php $active = request()->is('admin/finance') || request()->is('admin/finance*'); @endphp
                        <a href="/admin/finance" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Finance
                        </a>

                        @php $active = request()->is('admin/customers') || request()->is('admin/customers*'); @endphp
                        <a href="/admin/customers" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Customers
                        </a>

                        @if((auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin') && str_contains(strtolower($activeLicense['plan'] ?? 'Free'), 'platinum'))
                        <a href="https://drestro.com/dashboard" target="_blank" rel="noopener noreferrer" class="group flex items-center px-4 py-3 rounded-xl font-medium transition-all text-slate-600 dark:text-gray-400 hover:text-[#111111] dark:hover:text-white hover:bg-slate-100 dark:hover:bg-[#2A2A2A] dark:bg-[#222222]">
                            <svg class="w-5 h-5 mr-3 shrink-0 text-slate-400 dark:text-gray-400 group-hover:text-[#111111] dark:group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Manage Outlets
                        </a>
                        @endif

                        <div class="pt-4 pb-2">
                            <p class="px-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Staff Panels</p>
                        </div>

                        @if($canAccess('take_order'))
                        @php $active = request()->is('staff/take-order') || request()->is('staff/take-order*'); @endphp
                        <a href="/staff/take-order" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Take Restaurant Order
                        </a>
                        @endif

                        @if($canAccess('take_room_service') && $isHotelOrResort)
                        @php $active = request()->is('staff/room-service') || request()->is('staff/room-service*'); @endphp
                        <a href="/staff/room-service" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            Take Room Service
                        </a>
                        @endif

                        @if($canAccess('hotel_reception') && $isHotelOrResort)
                        @php $active = request()->is('staff/hotel-reception') || request()->is('staff/hotel-reception*'); @endphp
                        <a href="/staff/hotel-reception" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Hotel Reception Cashier
                        </a>
                        @endif

                        @if($canAccess('cashier_panel'))
                        @php $active = request()->is('staff/cashier') || request()->is('staff/cashier*'); @endphp
                        <a href="/staff/cashier" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2-2v14a2 2 0 002 2z"></path></svg>
                            Cashier Dashboard
                        </a>
                        @endif

                        @if($canAccess('waiter_dashboard'))
                        @php $active = request()->is('staff/waiter') || request()->is('staff/waiter*'); @endphp
                        <a href="/staff/waiter" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2-2v10a2 2 0 002 2z"></path></svg>
                            Waiter Dashboard
                        </a>
                        @endif

                        @if($canAccess('kitchen_display'))
                        @php $active = request()->is('staff/kitchen') || request()->is('staff/kitchen*'); @endphp
                        <a href="/staff/kitchen" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            Kitchen Display
                        </a>
                        @endif

                        @if($canAccess('bar_display'))
                        @php $active = request()->is('staff/bar') || request()->is('staff/bar*'); @endphp
                        <a href="/staff/bar" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2-2v10a2 2 0 002 2z"></path></svg>
                            Bar Display
                        </a>
                        @endif

                        <div class="pt-4 pb-2">
                            <p class="px-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Management</p>
                        </div>
                        
                        @if($canAccess('staff_roles'))
                        @php $active = request()->is('admin/staff') || request()->is('admin/staff*'); @endphp
                        <a href="/admin/staff" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Staff Accounts
                        </a>
                        @endif
                        
                        @if($canAccess('reports'))
                        @php $active = request()->is('admin/reports') || request()->is('admin/reports*'); @endphp
                        <a href="/admin/reports" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Reports & Analytics
                        </a>
                        @endif

                        @if($canAccess('settings'))
                        @php $active = request()->is('admin/settings') || request()->is('admin/settings*'); @endphp
                        <a href="/admin/settings" class="{{ $getLinkClass($active) }}">
                            <svg class="{{ $getIconClass($active) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            System Settings
                        </a>
                        @endif
                    </nav>

                    <div class="p-4 pb-6 border-t border-slate-200 dark:border-[#333333] shrink-0" style="padding-bottom: max(1.5rem, env(safe-area-inset-bottom));">
                        <a href="{{ route('logout') }}" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-50 dark:bg-red-950/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-rose-900/30 rounded-xl font-bold transition-all" title="Logout">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            <span>Logout</span>
                        </a>
                    </div>
                </aside>

                <!-- Main Content -->
                <main class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-slate-900">
                    <!-- Top Header -->
                    <header class="h-14 sm:h-16 border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-[#0a0a0a]/80 backdrop-blur-md flex items-center justify-between px-4 sm:px-6 z-30 sticky top-0">
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="toggleMobileSidebar()" class="lg:hidden p-1.5 -ml-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-white rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                            </button>
                            <h1 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-slate-100 truncate lg:hidden">{{ $title ?? 'Dashboard' }}</h1>
                            @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                                @php
                                    $isFreePlan = str_contains(strtolower($planName ?? ''), 'free') || empty($activeLicense['expires_at']);
                                @endphp
                                @if(isset($isExpired) && $isExpired && !$isFreePlan)
                                    <span class="px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-600 border border-red-200 ml-2 animate-pulse dark:border-red-800 dark:text-red-400">EXPIRED</span>
                                @elseif(!$isFreePlan && isset($dLeft) && $dLeft !== null && $dLeft <= 30)
                                    <a href="https://drestro.com/dashboard/billing" target="_blank" class="px-3 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-700 border border-amber-300 ml-2 hover:bg-amber-200 transition-colors dark:bg-amber-500/20 dark:border-amber-500/40 dark:text-amber-400 flex items-center gap-1 shadow-sm cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        RENEW SOON ({{ $dLeft }} Days)
                                    </a>
                                @elseif($isFreePlan)
                                    <span class="px-3 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-300 ml-2 dark:bg-emerald-500/20 dark:border-emerald-500/40 dark:text-emerald-400 flex items-center gap-1 shadow-sm">
                                        🟢 LIFETIME FREE ACTIVE
                                    </span>
                                @endif
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
                        </div>
                    </header>

                    <!-- Page Content -->
                    <div id="main-content-viewport" class="flex-1 overflow-y-auto p-4 sm:p-6 sm:pb-6 lg:p-8 lg:pb-8" style="padding-bottom: {{ request()->is('staff/take-order') ? '0px' : 'calc(5rem + env(safe-area-inset-bottom, 0px))' }};">
                        @if($isLocked)
                            <div class="flex flex-col items-center justify-center min-h-[70vh] text-center p-6">
                                <div class="w-24 h-24 bg-gradient-to-tr from-rose-600 to-red-500 text-white rounded-3xl flex items-center justify-center mb-6 shadow-2xl shadow-rose-600/30 animate-pulse">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                </div>
                                <span class="px-3 py-1 bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 font-extrabold text-xs rounded-full uppercase tracking-wider mb-3">Subscription Expired</span>
                                <h2 class="text-3xl font-black text-slate-900 mb-3 dark:text-white">Subscription Currently Inactive</h2>
                                <p class="text-sm text-slate-500 max-w-lg mb-8 leading-relaxed dark:text-slate-400">
                                    Your subscription plan has expired. Please renew or upgrade your subscription plan to instantly restore access.
                                </p>
                                <div class="flex flex-col sm:flex-row items-center gap-3.5 w-full max-w-md">
                                    <a href="https://drestro.com/dashboard/billing/plans" target="_blank" class="w-full sm:flex-1 py-3.5 px-6 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl font-bold text-sm transition-all shadow-lg shadow-rose-600/25 flex items-center justify-center gap-2 active:scale-95">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                        Renew / Upgrade Online
                                    </a>
                                    <a href="/admin/license" class="w-full sm:w-auto py-3.5 px-5 bg-white dark:bg-[#1A1A1A] hover:bg-slate-50 dark:hover:bg-[#222] text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-[#333] rounded-2xl font-bold text-sm transition-all shadow-sm flex items-center justify-center gap-2">
                                        🔑 License Key
                                    </a>
                                </div>
                                <div class="mt-6">
                                    <a href="https://wa.me/9779865029558?text=Hello%20DRestro%20Support,%20my%2014-day%20trial%20has%20expired.%20Please%20help%20me%20renew." target="_blank" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5">
                                        <span>💬 Need Help? Chat with DRestro Support on WhatsApp</span>
                                    </a>
                                </div>
                            </div>
                        @else
                            {{ $slot }}
                        @endif
                    </div>
                </main>

                <!-- Mobile Bottom Navigation -->
                <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-lg border-t border-slate-200 flex items-center justify-around px-1 z-50 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] dark:bg-slate-900/95 dark:border-slate-700/50" style="height: calc(4rem + env(safe-area-inset-bottom, 0px)); padding-bottom: env(safe-area-inset-bottom, 0px);">
                    
                    @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                    <a href="/admin" class="flex flex-col items-center justify-center w-full h-full space-y-1 text-slate-500 hover:text-rose-600 transition-colors {{ request()->is('admin') ? 'text-rose-600 dark:text-rose-500' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path></svg>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Dashboard</span>
                    </a>
                    @endif

                    @if(!$isLocked && $canAccess('take_order'))
                    <a href="/staff/take-order" class="flex flex-col items-center justify-center w-full h-full space-y-1 text-slate-500 hover:text-rose-600 transition-colors {{ request()->is('staff/take-order') ? 'text-rose-600 dark:text-rose-500' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-[10px] font-bold uppercase tracking-wider">POS</span>
                    </a>
                    @endif

                    @if(!$isLocked && $canAccess('restaurant_tables'))
                    <a href="/admin/tables" class="flex flex-col items-center justify-center w-full h-full space-y-1 text-slate-500 hover:text-rose-600 transition-colors {{ request()->is('admin/tables') ? 'text-rose-600 dark:text-rose-500' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Tables</span>
                    </a>
                    @endif
                    
                    <!-- Dark Mode -->
                    <button onclick="togglePosTheme()" class="flex flex-col items-center justify-center w-full h-full space-y-1 text-slate-500 hover:text-rose-600 transition-colors dark:text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Theme</span>
                    </button>

                    <!-- Menu / Sidebar Toggle -->
                    <button onclick="toggleMobileSidebar()" class="flex flex-col items-center justify-center w-full h-full space-y-1 text-slate-500 hover:text-rose-600 transition-colors dark:text-slate-400">
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

                window.triggerClientSideTestPrint = function(target) {
                    var old = document.getElementById('test-print-iframe');
                    if (old) old.remove();

                    var f = document.createElement('iframe');
                    f.id = 'test-print-iframe';
                    f.style.cssText = 'position:fixed;right:0;bottom:0;width:1px;height:1px;border:0;opacity:0';
                    document.body.appendChild(f);

                    var d = f.contentDocument || f.contentWindow.document;
                    d.open();
                    d.write('<!DOCTYPE html><html><head><title>Test Print</title><style>@page{size:80mm auto;margin:0}body{font-family:Courier New,monospace;width:72mm;margin:0 auto;padding:12px 6px;text-align:center;color:#000}h2{margin:4px 0;font-size:18px;font-weight:bold}p{margin:2px 0;font-size:12px}.dash{border-bottom:1px dashed #000;margin:6px 0}.row{display:flex;justify-content:space-between;font-size:12px}</style></head><body><h2>DRESTRO POS</h2><p style="font-weight:bold">*** TEST PRINT ***</p><p>Target: ' + target.toUpperCase() + ' PRINTER</p><div class="dash"></div><p>' + new Date().toLocaleString() + '</p><div class="dash"></div><div class="row"><span>1x Test KOT Item</span><span>Rs. 100</span></div><div class="row"><span>1x Sample Order</span><span>Rs. 250</span></div><div class="dash"></div><p style="font-weight:bold;font-size:14px">PRINTER WORKING OK</p></body></html>');
                    d.close();

                    setTimeout(function() {
                        try {
                            f.contentWindow.focus();
                            f.contentWindow.print();
                        } catch(e) {
                            window.print();
                        }
                    }, 400);
                };
            </script>

            <!-- Upgrade Required Pop-up Modal -->
            <div 
                x-data="{ showUpgradeModal: false, upgradeFeature: 'this feature' }" 
                @open-upgrade-modal.window="showUpgradeModal = true; upgradeFeature = $event.detail.feature || 'this feature'"
                x-show="showUpgradeModal" 
                x-cloak 
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in duration-200">
                <div class="bg-white dark:bg-[#1A1A1A] border border-slate-200 dark:border-[#333333] rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl text-center space-y-5 animate-in zoom-in-95 duration-200">
                    <div class="w-16 h-16 bg-rose-500/10 border border-rose-500/20 text-rose-500 rounded-2xl flex items-center justify-center mx-auto text-3xl">
                        🔒
                    </div>
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-800 dark:text-white">Upgrade Required</h3>
                        <p class="text-xs text-slate-500 dark:text-neutral-400 mt-2 leading-relaxed">
                            You don't have access to <strong class="text-rose-500" x-text="upgradeFeature"></strong> in your current plan. Please upgrade your package to unlock full access.
                        </p>
                    </div>
                    <div class="pt-2 flex flex-col sm:flex-row gap-2">
                        <a href="https://drestro.com/pricing" target="_blank" class="flex-1 px-5 py-3 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl shadow-lg shadow-rose-950/20 active:scale-95 transition-all text-xs flex items-center justify-center gap-2">
                            <span>🚀 UPGRADE PACKAGE</span>
                        </a>
                        <button @click="showUpgradeModal = false" class="px-5 py-3 bg-slate-100 dark:bg-[#222222] hover:bg-slate-200 text-slate-700 dark:text-gray-300 font-bold rounded-xl text-xs transition-colors">
                            Close
                        </button>
                    </div>
                </div>
            </div>

            @livewireScripts
        </div>
    </div>
    </body>
</html>
