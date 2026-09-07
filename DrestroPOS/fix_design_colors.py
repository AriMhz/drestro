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

/* Colored Alert/Info Cards */
.dark .bg-blue-50:not([class*="dark:bg-"]) { background-color: rgba(30, 58, 138, 0.5); border-color: #1e40af; color: #93c5fd; }
.dark .border-blue-100:not([class*="dark:border-"]) { border-color: #1e40af; }

.dark .bg-amber-50:not([class*="dark:bg-"]) { background-color: rgba(120, 53, 15, 0.5); border-color: #92400e; color: #fcd34d; }
.dark .border-amber-100:not([class*="dark:border-"]) { border-color: #92400e; }

.dark .bg-emerald-50:not([class*="dark:bg-"]) { background-color: rgba(6, 78, 59, 0.5); border-color: #065f46; color: #6ee7b7; }
.dark .border-emerald-100:not([class*="dark:border-"]) { border-color: #065f46; }

.dark .bg-red-50:not([class*="dark:bg-"]) { background-color: rgba(127, 29, 29, 0.5); border-color: #991b1b; color: #fca5a5; }
.dark .border-red-100:not([class*="dark:border-"]) { border-color: #991b1b; }

/* Fix Gradient Cards in Dark Mode */
.dark .from-emerald-50:not([class*="dark:from-"]) { --tw-gradient-from: rgba(6, 78, 59, 0.8) var(--tw-gradient-from-position); }
.dark .to-emerald-100\\/50:not([class*="dark:to-"]) { --tw-gradient-to: rgba(2, 44, 34, 0.5) var(--tw-gradient-to-position); }
.dark .to-teal-50:not([class*="dark:to-"]) { --tw-gradient-to: rgba(17, 94, 89, 0.8) var(--tw-gradient-to-position); }

.dark .from-slate-50:not([class*="dark:from-"]) { --tw-gradient-from: rgba(30, 41, 59, 0.8) var(--tw-gradient-from-position); }
.dark .to-slate-100\\/50:not([class*="dark:to-"]) { --tw-gradient-to: rgba(15, 23, 42, 0.5) var(--tw-gradient-to-position); }

.dark .from-indigo-50:not([class*="dark:from-"]) { --tw-gradient-from: rgba(49, 46, 129, 0.8) var(--tw-gradient-from-position); }
.dark .to-indigo-100\\/50:not([class*="dark:to-"]) { --tw-gradient-to: rgba(30, 27, 75, 0.5) var(--tw-gradient-to-position); }

.dark .from-amber-50:not([class*="dark:from-"]) { --tw-gradient-from: rgba(120, 53, 15, 0.8) var(--tw-gradient-from-position); }
.dark .to-amber-100\\/50:not([class*="dark:to-"]) { --tw-gradient-to: rgba(69, 26, 3, 0.5) var(--tw-gradient-to-position); }

/* Inputs */
.dark input:not([class*="dark:bg-"]), .dark select:not([class*="dark:bg-"]), .dark textarea:not([class*="dark:bg-"]) { 
    background-color: #18181b !important; 
    border-color: #3f3f46 !important; 
    color: #fafafa !important; 
}
""")
