<x-app-layout>
    <x-slot name="header">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        @viteReactRefresh
        @vite(['resources/js/app.js'])

        @if(session('success'))
            <div id="notification" class="fixed top-5 right-5 z-50 animate-bounce">
                <div class="bg-gray-900 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 border border-gray-700">
                    <div class="w-2 h-2 bg-indigo-400 rounded-full"></div>
                    <span class="text-[10px] font-black uppercase tracking-widest">{{ session('success') }}</span>
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

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl md:text-3xl text-gray-800 leading-tight tracking-tight">Task Management</h2>
                <p class="text-xs md:text-sm text-gray-500 mt-1">Manage your priorities and track your work progress.</p>
            </div>
            <div class="flex items-center gap-2 md:gap-3 w-full sm:w-auto">
                 <a href="{{ route('tasks.completed') }}" class="flex-1 sm:flex-none text-center px-4 md:px-5 py-2.5 bg-white border border-gray-200 text-gray-600 text-[10px] md:text-xs font-black uppercase tracking-widest rounded-xl hover:bg-gray-50 transition-all active:scale-95">
                    History
                </a>
                <a href="{{ route('tasks.trash') }}" class="flex-1 sm:flex-none text-center px-4 md:px-5 py-2.5 bg-gray-100 border border-gray-200 text-gray-500 text-[10px] md:text-xs font-black uppercase tracking-widest rounded-xl hover:bg-gray-200 transition-all active:scale-95">
                    Trash Bin
                </a>
                <a href="{{ route('tasks.create') }}" class="flex-1 sm:flex-none text-center px-4 md:px-5 py-2.5 bg-indigo-600 text-white text-[10px] md:text-xs font-black uppercase tracking-widest rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-100 active:scale-95">
                    + New Task
                </a>
            </div>
        </div>
    </x-slot>

    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 mb-6">
        <form action="{{ route('tasks.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <div class="relative w-full sm:w-48">
                <select name="priority" onchange="this.form.submit()" class="appearance-none w-full bg-gray-50 border-none text-gray-600 text-xs font-bold rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-100 cursor-pointer">
                    <option value="">All Priorities</option>
                    <option value="low" {{ request('priority')=='low'?'selected':'' }}>Low</option>
                    <option value="medium" {{ request('priority')=='medium'?'selected':'' }}>Medium</option>
                    <option value="high" {{ request('priority')=='high'?'selected':'' }}>High</option>
                </select>
            </div>
            <div class="relative w-full sm:w-48">
                <select name="category_id" onchange="this.form.submit()" class="appearance-none w-full bg-gray-50 border-none text-gray-600 text-xs font-bold rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-100 cursor-pointer">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id')==$category->id?'selected':'' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            @if(request('priority') || request('category_id'))
                <a href="{{ route('tasks.index') }}" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 tracking-widest ml-2">Reset Filter</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div id="react-app"></div>
    </div>

    <script>
        @php
            $dataTasks = $tasks->map(function($task) {
                // WARNA BAR BERDASARKAN PRIORITY
                $priorityColor = '#6366f1'; 
                if ($task->priority === 'high') {
                    $priorityColor = '#ef4444'; 
                } elseif ($task->priority === 'medium') {
                    $priorityColor = '#f59e0b'; 
                }

                // HITUNG PROGRESS SUBTASKS
                $totalSubtasks = $task->subtasks->count();
                $completedSubtasks = $task->subtasks->where('is_completed', true)->count();
                $progressPercent = $totalSubtasks > 0 ? round(($completedSubtasks / $totalSubtasks) * 100) : 0;

                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'link' => $task->link_attachment, // DIPERBAIKI: Menggunakan link_attachment
                    'priority' => strtoupper($task->priority),
                    'priority_color' => $priorityColor,
                    'category' => $task->category?->name ?? 'None',
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
                            'task' => $sub->title,
                            'completed' => (bool)$sub->is_completed
                        ];
                    })->values()
                ];
            })->values();
        @endphp
        window.laravelTasks = @json($dataTasks);
    </script>
</x-app-layout>