<aside class="w-64 bg-white border-r min-h-screen p-6 shadow-sm">
    <h1 class="font-black text-2xl mb-10 text-indigo-600 tracking-tighter">
        Productivity
    </h1>

    {{-- Main Navigation --}}
    <nav class="space-y-1 mb-10">
        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-4 mb-2">Main Menu</p>
        
        <a href="{{ route('dashboard') }}" 
           class="flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">
            Dashboard
        </a>
        
        <a href="{{ route('tasks.index') }}" 
           class="flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('tasks.index') && !request('category_id') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">
            All Tasks
        </a>

        <a href="{{ route('categories.index') }}" 
           class="flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('categories.index') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">
            Manage Categories
        </a>

        <a href="{{ route('tasks.trash') }}" 
           class="flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('tasks.trash') ? 'bg-red-50 text-red-600' : 'text-red-500 hover:bg-red-50' }}">
            Trash Bin
        </a>
    </nav>

    {{-- Categories List --}}
    <div>
        <div class="flex items-center justify-between px-4 mb-3">
            <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                Categories
            </h2>
        </div>

        <ul class="space-y-1">
            @foreach ($categories as $category)
                <li>
                    <a href="{{ route('tasks.index', ['category_id' => $category->id]) }}" 
                       class="flex justify-between items-center px-4 py-2 rounded-xl text-sm font-medium transition {{ request('category_id') == $category->id ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">
                        <span class="truncate mr-2">{{ $category->name }}</span>
                        <span class="text-[10px] bg-gray-100 px-2 py-1 rounded-md text-gray-500 group-hover:bg-white transition">
                            {{ $category->tasks_count }}
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</aside>