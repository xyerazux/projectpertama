<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>{{ config('app.name', 'Productivity') }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #fcfcfd; margin: 0; }
        
        #sidebar { 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
        }
        
        /* Desktop: Sidebar mendorong konten */
        @media (min-width: 1024px) {
            .sidebar-hidden-desktop {
                width: 0 !important;
                min-width: 0 !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                overflow: hidden;
                border: none !important;
                opacity: 0;
            }
        }

        /* Mobile: Sidebar melayang (Overlay) */
        @media (max-width: 1023px) {
            .sidebar-hidden-mobile {
                transform: translateX(-100%);
            }
        }
        
        .custom-scrollbar::-webkit-scrollbar { height: 4px; width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .sidebar-link-active { background-color: #4f46e5; color: white !important; box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2); }
    </style>

    <script>
        // Jalankan di head untuk cek status terakhir di LocalStorage
        (function() {
            const state = localStorage.getItem('sidebarStatus');
            if (state === 'closed' && window.innerWidth >= 1024) {
                document.documentElement.classList.add('sidebar-logic-closed');
            }
        })();
    </script>
</head>
<body class="antialiased text-gray-800">

    <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[60] hidden lg:hidden"></div>

    <div class="flex min-h-screen relative">
        <aside id="sidebar" class="fixed lg:sticky top-0 left-0 z-[70] w-72 h-screen bg-white border-r border-gray-100 p-8 flex flex-col flex-shrink-0 overflow-y-auto overflow-x-hidden">
            <div class="mb-12 px-4 flex justify-between items-center">
                <h1 class="font-extrabold text-2xl text-indigo-600 tracking-tighter">Productivity.</h1>
                <button onclick="toggleSidebar()" class="lg:hidden p-2 text-gray-400 hover:text-red-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <nav class="space-y-4 flex-1">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] px-5 mb-2">Main Menu</p>
                <a href="{{ route('dashboard') }}" class="flex items-center px-5 py-4 rounded-2xl text-[15px] font-semibold transition-all {{ request()->routeIs('dashboard') ? 'sidebar-link-active' : 'text-gray-500 hover:bg-indigo-50 hover:text-indigo-600' }}">Dashboard</a>
                <a href="{{ route('tasks.index') }}" class="flex items-center px-5 py-4 rounded-2xl text-[15px] font-semibold transition-all {{ request()->routeIs('tasks.index') ? 'sidebar-link-active' : 'text-gray-500 hover:bg-indigo-50 hover:text-indigo-600' }}">My Tasks</a>
                <a href="{{ route('categories.index') }}" class="flex items-center px-5 py-4 rounded-2xl text-[15px] font-semibold transition-all {{ request()->routeIs('categories.index') ? 'sidebar-link-active' : 'text-gray-500 hover:bg-indigo-50 hover:text-indigo-600' }}">Categories</a>
                <a href="{{ route('tasks.trash') }}" class="flex items-center px-5 py-4 rounded-2xl text-[15px] font-semibold transition-all {{ request()->routeIs('tasks.trash') ? 'bg-red-500 text-white' : 'text-red-400 hover:bg-red-50' }}">Trash Bin</a>
            </nav>

            <div class="pt-8 border-t border-gray-100">
                <a href="{{ route('profile.edit') }}" class="flex items-center px-5 py-4 rounded-2xl text-[14px] font-semibold text-gray-500 hover:bg-gray-50 transition-all">Settings</a>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="w-full text-left px-5 py-4 rounded-2xl text-[14px] font-semibold text-gray-400 hover:text-red-500">Logout</button>
                </form>
            </div>
        </aside>

        <div id="mainContent" class="flex-1 flex flex-col min-w-0 min-h-screen">
            <header class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-gray-100 px-6 py-4 flex items-center justify-between">
                <button id="sidebarBtn" onclick="toggleSidebar()" class="p-2.5 bg-gray-50 text-gray-500 hover:text-indigo-600 rounded-xl transition-all active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" /></svg>
                </button>
                @include('layouts.navigation')
            </header>

            <main class="flex-1">
                @if (isset($header))
                    <div class="bg-white border-b border-gray-50 py-8 px-6 md:px-10">
                        <div class="max-w-7xl mx-auto">{{ $header }}</div>
                    </div>
                @endif
                <div class="py-10 px-6 md:px-10">
                    <div class="max-w-7xl mx-auto">{{ $slot }}</div>
                </div>
            </main>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function initSidebar() {
            const state = localStorage.getItem('sidebarStatus');
            if (window.innerWidth >= 1024) {
                // Desktop logic
                if (state === 'closed') {
                    sidebar.classList.add('sidebar-hidden-desktop');
                }
            } else {
                // Mobile logic
                sidebar.classList.add('sidebar-hidden-mobile');
            }
        }

        function toggleSidebar() {
            if (window.innerWidth >= 1024) {
                sidebar.classList.toggle('sidebar-hidden-desktop');
                localStorage.setItem('sidebarStatus', sidebar.classList.contains('sidebar-hidden-desktop') ? 'closed' : 'open');
            } else {
                sidebar.classList.toggle('sidebar-hidden-mobile');
                overlay.classList.toggle('hidden');
            }
        }

        // Jalankan init
        document.addEventListener('DOMContentLoaded', initSidebar);
    </script>
</body>
</html>