<x-app-layout>
    <x-slot name="header">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        @viteReactRefresh
        @vite(['resources/js/app.js'])

        {{-- Notification --}}
        @if(session('success'))
            <div id="notification" class="fixed top-4 right-4 sm:top-5 sm:right-5 z-50 animate-bounce">
                <div class="bg-gray-900 text-white px-4 sm:px-6 py-3 sm:py-4 rounded-xl sm:rounded-2xl shadow-2xl flex items-center gap-2 sm:gap-3 border border-gray-700 max-w-[calc(100%-2rem)]">
                    <div class="w-2 h-2 bg-indigo-400 rounded-full shrink-0"></div>
                    <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest truncate">{{ e(session('success')) }}</span>
                    <button onclick="document.getElementById('notification').remove()" class="ml-auto text-slate-400 hover:text-white shrink-0">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            <script>
                setTimeout(() => {
                    const el = document.getElementById('notification');
                    if(el) {
                        el.style.opacity = '0';
                        el.style.transition = 'opacity 0.5s ease';
                        setTimeout(() => el.remove(), 500);
                    }
                }, 3000);
            </script>
        @endif

        {{-- Header - Responsive --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
            <div>
                <h2 class="font-black text-xl sm:text-2xl md:text-3xl text-gray-800 leading-tight tracking-tight">Task Management</h2>
                <p class="text-[10px] sm:text-xs md:text-sm text-gray-500 mt-0.5 sm:mt-1">Manage your priorities and track your work progress.</p>
            </div>
            <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0">
                <a href="{{ route('tasks.completed') }}" class="flex-none text-center px-3 sm:px-4 md:px-5 py-2 sm:py-2.5 bg-white border border-gray-200 text-gray-600 text-[9px] sm:text-[10px] md:text-xs font-black uppercase tracking-widest rounded-xl hover:bg-gray-50 transition-all active:scale-95 whitespace-nowrap">
                    History
                </a>
                <a href="{{ route('tasks.trash') }}" class="flex-none text-center px-3 sm:px-4 md:px-5 py-2 sm:py-2.5 bg-gray-100 border border-gray-200 text-gray-500 text-[9px] sm:text-[10px] md:text-xs font-black uppercase tracking-widest rounded-xl hover:bg-gray-200 transition-all active:scale-95 whitespace-nowrap">
                    Trash
                </a>
                <a href="{{ route('tasks.create') }}" class="flex-none text-center px-3 sm:px-4 md:px-5 py-2 sm:py-2.5 bg-indigo-600 text-white text-[9px] sm:text-[10px] md:text-xs font-black uppercase tracking-widest rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-100 active:scale-95 whitespace-nowrap">
                    + New
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Filter Form - Responsive --}}
    <div class="bg-white p-3 sm:p-4 rounded-2xl shadow-sm border border-gray-100 mb-4 sm:mb-6">
        <form action="{{ route('tasks.index') }}" method="GET" class="flex flex-wrap items-center gap-2 sm:gap-3">
            <div class="relative w-full sm:w-auto sm:flex-1 min-w-[140px]">
                <select name="priority" onchange="this.form.submit()" class="appearance-none w-full bg-gray-50 border-none text-gray-600 text-[10px] sm:text-xs font-bold rounded-xl px-3 sm:px-4 py-2.5 sm:py-3 focus:ring-2 focus:ring-indigo-100 cursor-pointer">
                    <option value="">All Priorities</option>
                    <option value="low" {{ request('priority')=='low'?'selected':'' }}>Low</option>
                    <option value="medium" {{ request('priority')=='medium'?'selected':'' }}>Medium</option>
                    <option value="high" {{ request('priority')=='high'?'selected':'' }}>High</option>
                </select>
            </div>
            <div class="relative w-full sm:w-auto sm:flex-1 min-w-[140px]">
                <select name="category_id" onchange="this.form.submit()" class="appearance-none w-full bg-gray-50 border-none text-gray-600 text-[10px] sm:text-xs font-bold rounded-xl px-3 sm:px-4 py-2.5 sm:py-3 focus:ring-2 focus:ring-indigo-100 cursor-pointer">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id')==$category->id?'selected':'' }}>{{ e($category->name) }}</option>
                    @endforeach
                </select>
            </div>
            @if(request('priority') || request('category_id'))
                <a href="{{ route('tasks.index') }}" class="text-[9px] sm:text-[10px] font-black uppercase text-red-400 hover:text-red-600 tracking-widest whitespace-nowrap">Reset</a>
            @endif
        </form>
    </div>

    {{-- Task List Container --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div id="react-app" class="min-h-[200px]"></div>
    </div>

    {{-- Pagination - Mobile Friendly --}}
    @if($tasks->hasPages())
    <div class="mt-4 sm:mt-6 flex items-center justify-center gap-1 sm:gap-2 flex-wrap">
        {{-- Previous --}}
        @if($tasks->onFirstPage())
            <span class="px-3 sm:px-4 py-2 bg-gray-100 text-gray-400 rounded-xl text-[9px] sm:text-xs font-bold cursor-not-allowed">←</span>
        @else
            <a href="{{ $tasks->previousPageUrl() }}" class="px-3 sm:px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl text-[9px] sm:text-xs font-bold hover:bg-indigo-100 transition-all">←</a>
        @endif

        {{-- Page Numbers - Show limited on mobile --}}
        @php
            $currentPage = $tasks->currentPage();
            $lastPage = $tasks->lastPage();
            $range = $lastPage <= 5 ? range(1, $lastPage) : [
                1,
                $currentPage > 3 ? '...' : null,
                ...range(max(2, $currentPage - 1), min($lastPage - 1, $currentPage + 1)),
                $currentPage < $lastPage - 2 ? '...' : null,
                $lastPage
            ];
        @endphp
        @foreach(array_filter($range) as $page)
            @if(is_numeric($page))
                @if($page == $currentPage)
                    <span class="px-2.5 sm:px-3 py-2 bg-indigo-600 text-white rounded-xl text-[9px] sm:text-xs font-bold">{{ $page }}</span>
                @else
                    <a href="{{ $tasks->url($page) }}" class="px-2.5 sm:px-3 py-2 bg-gray-100 text-gray-600 rounded-xl text-[9px] sm:text-xs font-bold hover:bg-gray-200 transition-all">{{ $page }}</a>
                @endif
            @else
                <span class="px-2 py-2 text-gray-400 text-[9px] sm:text-xs">{{ $page }}</span>
            @endif
        @endforeach

        {{-- Next --}}
        @if($tasks->hasMorePages())
            <a href="{{ $tasks->nextPageUrl() }}" class="px-3 sm:px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl text-[9px] sm:text-xs font-bold hover:bg-indigo-100 transition-all">→</a>
        @else
            <span class="px-3 sm:px-4 py-2 bg-gray-100 text-gray-400 rounded-xl text-[9px] sm:text-xs font-bold cursor-not-allowed">→</span>
        @endif
    </div>
    
    {{-- Page Info --}}
    <div class="text-center mt-2 text-[9px] sm:text-[10px] text-gray-400">
        Page {{ $tasks->currentPage() }} of {{ $tasks->lastPage() }}
    </div>
    @endif

    {{-- Data for React --}}
    <script>
        @php
            $dataTasks = $tasks->map(function($task) {
                $priorityColor = '#6366f1'; 
                if ($task->priority === 'high') {
                    $priorityColor = '#ef4444'; 
                } elseif ($task->priority === 'medium') {
                    $priorityColor = '#f59e0b'; 
                }

                $totalSubtasks = $task->subtasks->count();
                $completedSubtasks = $task->subtasks->where('is_completed', true)->count();
                $progressPercent = $totalSubtasks > 0 ? round(($completedSubtasks / $totalSubtasks) * 100) : 0;

                return [
                    'id' => $task->id,
                    'title' => e($task->title),
                    'description' => e($task->description),
                    'link' => e($task->link_attachment),
                    'priority' => strtoupper(e($task->priority)),
                    'priority_color' => $priorityColor,
                    'category' => e($task->category?->name ?? 'None'),
                    'deadline' => $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('d M Y') : '—',
                    'is_past' => $task->deadline ? \Carbon\Carbon::parse($task->deadline)->isPast() : false,
                    'edit_url' => route('tasks.edit', $task->id),
                    'delete_url' => route('tasks.destroy', $task->id),
                    'done_url' => route('tasks.complete', $task->id),
                    'progress_percent' => $progressPercent,
                    'completed_count' => $completedSubtasks,
                    'total_count' => $totalSubtasks,
                    'subtasks' => $task->subtasks->map(function($sub) {
                        return [
                            'id' => $sub->id,
                            'task' => e($sub->title),
                            'completed' => (bool)$sub->is_completed
                        ];
                    })->values()
                ];
            })->values();
        @endphp
        window.laravelTasks = @json($dataTasks);
    </script>
</x-app-layout>