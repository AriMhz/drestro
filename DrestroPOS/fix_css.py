with open('resources/css/app.css', 'w') as f:
    f.write("""@import 'tailwindcss';
@custom-variant dark (&:where(.dark, .dark *));
@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';
@source '../**/*.blade.php';
@source '../**/*.js';

@theme {
    --font-sans: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji',
        'Segoe UI Symbol', 'Noto Color Emoji';
}

/* Global Dark Mode Fixes for Legacy Light Components - Pure Neutral Dark Theme */

/* Only apply dark mode fallbacks if the element doesn't have an explicit dark: override */
.dark .bg-white:not([class*="dark:bg-"]) { background-color: #18181b; border-color: #27272a; color: #f4f4f5; }
.dark .bg-slate-50:not([class*="dark:bg-"]) { background-color: #000000; }
.dark .bg-slate-100:not([class*="dark:bg-"]) { background-color: #09090b; }
.dark .bg-slate-200:not([class*="dark:bg-"]) { background-color: #27272a; }

.dark .text-slate-800:not([class*="dark:text-"]) { color: #ffffff; }
.dark .text-slate-700:not([class*="dark:text-"]) { color: #f4f4f5; }
.dark .text-slate-600:not([class*="dark:text-"]) { color: #e4e4e7; }
.dark .text-slate-500:not([class*="dark:text-"]) { color: #a1a1aa; }

.dark .border-slate-100:not([class*="dark:border-"]) { border-color: #27272a; }
.dark .border-slate-200:not([class*="dark:border-"]) { border-color: #3f3f46; }

/* Active Buttons */
.dark .bg-slate-900:not([class*="dark:bg-"]) { background-color: #059669; color: #ffffff; }

/* Colored Alert/Info Cards */
.dark .bg-blue-50:not([class*="dark:bg-"]) { background-color: #1e3a8a; border-color: #1e40af; color: #dbeafe; }
.dark .border-blue-100:not([class*="dark:border-"]) { border-color: #1e40af; }

.dark .bg-amber-50:not([class*="dark:bg-"]) { background-color: #78350f; border-color: #92400e; color: #fef3c7; }
.dark .border-amber-100:not([class*="dark:border-"]) { border-color: #92400e; }

.dark .bg-emerald-50:not([class*="dark:bg-"]) { background-color: #064e3b; border-color: #065f46; color: #d1fae5; }
.dark .border-emerald-100:not([class*="dark:border-"]) { border-color: #065f46; }

.dark .bg-red-50:not([class*="dark:bg-"]) { background-color: #7f1d1d; border-color: #991b1b; color: #fee2e2; }
.dark .border-red-100:not([class*="dark:border-"]) { border-color: #991b1b; }

/* Inputs */
.dark input:not([class*="dark:bg-"]), .dark select:not([class*="dark:bg-"]), .dark textarea:not([class*="dark:bg-"]) { 
    background-color: #18181b !important; 
    border-color: #3f3f46 !important; 
    color: #fafafa !important; 
}
""")
