<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Productivity') }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #fcfcfd; margin: 0; }
        
        #sidebar { 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
        }
        
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
                
                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}" class="flex items-center px-5 py-4 rounded-2xl text-[15px] font-semibold transition-all {{ request()->routeIs('dashboard') ? 'sidebar-link-active' : 'text-gray-500 hover:bg-indigo-50 hover:text-indigo-600' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>
                
                {{-- My Tasks --}}
                <a href="{{ route('tasks.index') }}" class="flex items-center px-5 py-4 rounded-2xl text-[15px] font-semibold transition-all {{ request()->routeIs('tasks.index') ? 'sidebar-link-active' : 'text-gray-500 hover:bg-indigo-50 hover:text-indigo-600' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    My Tasks
                </a>
                
                {{-- ✅ NEW: Roadmap --}}
                <a href="{{ route('roadmap.index') }}" class="flex items-center px-5 py-4 rounded-2xl text-[15px] font-semibold transition-all {{ request()->routeIs('roadmap.index') ? 'sidebar-link-active' : 'text-gray-500 hover:bg-indigo-50 hover:text-indigo-600' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                    Roadmap
                </a>
                
                {{-- Categories --}}
                <a href="{{ route('categories.index') }}" class="flex items-center px-5 py-4 rounded-2xl text-[15px] font-semibold transition-all {{ request()->routeIs('categories.index') ? 'sidebar-link-active' : 'text-gray-500 hover:bg-indigo-50 hover:text-indigo-600' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Categories
                </a>
                
                {{-- Trash Bin --}}
                <a href="{{ route('tasks.trash') }}" class="flex items-center px-5 py-4 rounded-2xl text-[15px] font-semibold transition-all {{ request()->routeIs('tasks.trash') ? 'bg-red-500 text-white' : 'text-red-400 hover:bg-red-50' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Trash Bin
                </a>
            </nav>

            <div class="pt-8 border-t border-gray-100">
                <a href="{{ route('profile.edit') }}" class="flex items-center px-5 py-4 rounded-2xl text-[14px] font-semibold text-gray-500 hover:bg-gray-50 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Settings
                </a>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="w-full text-left px-5 py-4 rounded-2xl text-[14px] font-semibold text-gray-400 hover:text-red-500 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </button>
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
                if (state === 'closed') {
                    sidebar.classList.add('sidebar-hidden-desktop');
                }
            } else {
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

        document.addEventListener('DOMContentLoaded', initSidebar);
    </script>
</body>
</html>