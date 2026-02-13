<x-app-layout>
    <x-slot name="header">
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
                <a href="{{ route('tasks.create') }}" class="flex-1 sm:flex-none text-center px-4 md:px-5 py-2.5 bg-indigo-600 text-white text-[10px] md:text-xs font-black uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 active:scale-95">
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
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px]">
                <thead class="bg-gray-50/50 border-b border-gray-100">
                    <tr class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                        <th class="px-8 py-5 text-left">Task Details</th>
                        <th class="px-6 py-5 text-center">Priority</th>
                        <th class="px-6 py-5 text-center">Category</th>
                        <th class="px-6 py-5 text-center">Deadline</th>
                        <th class="px-8 py-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($tasks as $task)
                    <tr class="hover:bg-indigo-50/10 transition-all group">
                        <td class="px-8 py-6">
                            <div class="flex items-start">
                                <div class="w-1.5 h-12 rounded-full mr-5 shrink-0 {{ $task->priority == 'high' ? 'bg-red-500' : ($task->priority == 'medium' ? 'bg-amber-400' : 'bg-emerald-400') }}"></div>
                                <div class="max-w-[400px]">
                                    <div class="flex items-center gap-2 mb-1">
                                        <p class="font-bold text-gray-800 text-sm truncate">{{ $task->title }}</p>
                                        @if($task->link_attachment)
                                            <a href="{{ $task->link_attachment }}" target="_blank" class="text-indigo-400 hover:text-indigo-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                                            </a>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-gray-500 line-clamp-2">{{ $task->description ?? 'No description.' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-6 text-center">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase {{ $task->priority == 'high' ? 'bg-red-50 text-red-500' : ($task->priority == 'medium' ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600') }}">
                                {{ $task->priority }}
                            </span>
                        </td>
                        <td class="px-6 py-6 text-center">
                            <span class="bg-gray-100 text-gray-500 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase">{{ $task->category?->name ?? 'Uncategorized' }}</span>
                        </td>
                        <td class="px-6 py-6 text-center">
                            <span class="text-xs font-bold {{ $task->deadline && \Carbon\Carbon::parse($task->deadline)->isPast() ? 'text-red-500' : 'text-gray-500' }}">
                                {{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('d M Y') : '—' }}
                            </span>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex justify-end items-center gap-1">
                                <a href="{{ route('tasks.edit', $task->id) }}" class="p-2 text-blue-400 hover:bg-blue-50 rounded-xl"><svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></a>
                                
                                <form action="{{ route('tasks.markDone', $task->id) }}" method="POST">@csrf @method('PATCH')
                                    <button type="submit" class="p-2 text-emerald-400 hover:bg-emerald-50 rounded-xl"><svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></button>
                                </form>

                                <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Move to trash?')">@csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-red-300 hover:bg-red-50 rounded-xl"><svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-24 text-center text-gray-400 font-black text-xs uppercase tracking-widest">No tasks found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>