import os

file_path = 'resources/views/components/layouts/app.blade.php'

with open(file_path, 'r') as f:
    content = f.read()

# Add the JS toggle function
js_function = """</script>
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
        <style>"""
content = content.replace('</script>\n        <style>', js_function)

# Strip Alpine attributes and replace with Vanilla JS attributes
content = content.replace('<div x-data="{ sidebarOpen: false }">', '<div>')
content = content.replace('<div x-show="sidebarOpen" x-cloak class="fixed inset-0 bg-black/50 z-40 lg:hidden" @click="sidebarOpen = false"></div>', '<div id="mobile-overlay" class="fixed inset-0 bg-black/50 z-40 lg:hidden hidden" onclick="toggleMobileSidebar()"></div>')
content = content.replace('<aside :class="sidebarOpen ? \'translate-x-0\' : \'-translate-x-full lg:translate-x-0\'" class="-translate-x-full lg:translate-x-0 fixed lg:relative inset-y-0 left-0 z-50 w-64 bg-white dark:bg-[#0a0a0a] border-r border-slate-200 dark:border-slate-800 flex flex-col transition-transform duration-300 lg:transform-none">', '<aside id="mobile-sidebar" class="-translate-x-full lg:translate-x-0 fixed lg:relative inset-y-0 left-0 z-50 w-64 bg-white dark:bg-[#0a0a0a] border-r border-slate-200 dark:border-slate-800 flex flex-col transition-transform duration-300 lg:transform-none">')
content = content.replace('@click="sidebarOpen = false"', 'onclick="toggleMobileSidebar()"')
content = content.replace('@click="sidebarOpen = true"', 'onclick="toggleMobileSidebar()"')

with open(file_path, 'w') as f:
    f.write(content)
