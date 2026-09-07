import re
import base64
import zipfile

with open('resources/views/components/layouts/app.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

start_tag = '<nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">'
end_tag = '</nav>'

start_idx = content.find(start_tag)
end_idx = content.find(end_tag, start_idx) + len(end_tag)

new_nav = """<nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                        @if($canAccess('dashboard'))
                        <a href="{{ $homeUrl }}" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('admin') || (request()->is('staff/waiter') && $homeUrl == '/staff/waiter') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5 mr-3 {{ request()->is('admin') || (request()->is('staff/waiter') && $homeUrl == '/staff/waiter') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            Dashboard
                        </a>
                        @endif

                        @if($canAccess('menu_manager') && ($isSuperAdmin || in_array('menu', $lf)))
                        <a href="/admin/menus" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('admin/menus') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5 mr-3 {{ request()->is('admin/menus') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                            Menu Manager
                        </a>
                        @endif

                        @if($canAccess('restaurant_tables') && ($isSuperAdmin || in_array('tables', $lf)))
                        <a href="/admin/tables" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('admin/tables') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5 mr-3 {{ request()->is('admin/tables') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            Restaurant Tables
                        </a>
                        @endif

                        @if($canAccess('hotel_rooms') && ($isSuperAdmin || in_array('rooms', $lf)))
                        <a href="/admin/rooms" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('admin/rooms') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5 mr-3 {{ request()->is('admin/rooms') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            Hotel Room Manager
                        </a>
                        @endif

                        @if($canAccess('inventory') && ($isSuperAdmin || in_array('inventory', $lf)))
                        <a href="/admin/inventory" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('admin/inventory') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5 mr-3 {{ request()->is('admin/inventory') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            Inventory
                        </a>
                        @endif

                        <div class="pt-4 pb-2">
                            <p class="px-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Staff Panels</p>
                        </div>

                        @if($canAccess('take_order'))
                        <a href="/staff/take-order" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('staff/take-order') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5 mr-3 {{ request()->is('staff/take-order') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Take Restaurant Order
                        </a>
                        @endif

                        @if($canAccess('room_service') && ($isSuperAdmin || in_array('rooms', $lf)))
                        <a href="/staff/room-service" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('staff/room-service') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5 mr-3 {{ request()->is('staff/room-service') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            Take Room Service
                        </a>
                        @endif

                        @if($canAccess('hotel_cashier') && ($isSuperAdmin || in_array('rooms', $lf)))
                        <a href="/staff/hotel-reception" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('staff/hotel-reception') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5 mr-3 {{ request()->is('staff/hotel-reception') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
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
                        <a href="/staff/waiter" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('staff/waiter') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5 mr-3 {{ request()->is('staff/waiter') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2-2v10a2 2 0 002 2z"></path></svg>
                            Waiter Dashboard
                        </a>
                        @endif

                        @if($canAccess('kitchen'))
                        <a href="/staff/kitchen" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('staff/kitchen') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5 mr-3 {{ request()->is('staff/kitchen') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            Kitchen Display
                        </a>
                        @endif

                        @if($canAccess('bar'))
                        <a href="/staff/bar" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('staff/bar') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5 mr-3 {{ request()->is('staff/bar') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2-2v10a2 2 0 002 2z"></path></svg>
                            Bar Display
                        </a>
                        @endif

                        <div class="pt-4 pb-2">
                            <p class="px-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Management</p>
                        </div>
                        
                        @if($canAccess('staff_roles'))
                        <a href="/admin/staff" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('admin/staff') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5 mr-3 {{ request()->is('admin/staff') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Staff Accounts
                        </a>
                        @endif
                        
                        @if($canAccess('reports'))
                        <a href="/admin/reports" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('admin/reports') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5 mr-3 {{ request()->is('admin/reports') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Reports & Analytics
                        </a>
                        @endif

                        @if($canAccess('support') && ($isSuperAdmin || in_array('support', $lf)))
                        <a href="/admin/support" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('admin/support') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5 mr-3 {{ request()->is('admin/support') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            Priority Support
                        </a>
                        @endif

                        @if($canAccess('settings'))
                        <a href="/admin/settings" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('admin/settings') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5 mr-3 {{ request()->is('admin/settings') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            System Settings
                        </a>
                        @endif
                        
                        @if($canAccess('license'))
                        <a href="/admin/license" class="flex items-center px-3 py-2.5 rounded-xl font-medium transition-colors {{ request()->is('admin/license') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-200' }}">
                            <svg class="w-5 h-5 mr-3 {{ request()->is('admin/license') ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            License Manager
                        </a>
                        @endif
                    </nav>"""

content = content[:start_idx] + new_nav + content[end_idx:]

with open('resources/views/components/layouts/app.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)

with zipfile.ZipFile('deploy.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    zipf.write('resources/views/components/layouts/app.blade.php', arcname='resources/views/components/layouts/app.blade.php')

with open('deploy.zip', 'rb') as f:
    zip_b64 = base64.b64encode(f.read()).decode('utf-8')

php_script = "<?php\n"
php_script += "$zipData = base64_decode('" + zip_b64 + "');\n"
php_script += "file_put_contents('deploy_sidebar_fix.zip', $zipData);\n"
php_script += "$zip = new ZipArchive;\n"
php_script += "if ($zip->open('deploy_sidebar_fix.zip') === TRUE) {\n"
php_script += "    $zip->extractTo('../');\n"
php_script += "    $zip->close();\n"
php_script += "    unlink('deploy_sidebar_fix.zip');\n"
php_script += "    echo '<h1>Dynamic Sidebar Bug Fixed! Waiter can now access all checked pages!</h1>';\n"
php_script += "} else {\n"
php_script += "    echo '<h1>Error extracting zip</h1>';\n"
php_script += "}\n"
php_script += "?>"

with open('deploy_sidebar_fix.php', 'w') as f:
    f.write(php_script)
