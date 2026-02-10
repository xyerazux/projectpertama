<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl md:text-3xl text-gray-800 leading-tight tracking-tight">Task Management</h2>
                <p class="text-xs md:text-sm text-gray-500 mt-1">Manage your priorities and track your work progress.</p>
            </div>
            <div class="flex items-center gap-2 md:gap-3 w-full sm:w-auto">
                 <a href="{{ route('tasks.completed') }}" class="flex-1 sm:flex-none text-center px-4 md:px-5 py-2.5 bg-white border border-gray-200 text-gray-600 text-[10px] md:text-xs font-black uppercase tracking-widest rounded-xl hover:bg-gray-50 transition-all active:scale-95">
                    History
                </a>
                <a href="{{ route('tasks.create') }}" class="flex-1 sm:flex-none text-center px-4 md:px-5 py-2.5 bg-indigo-600 text-white text-[10px] md:text-xs font-black uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 active:scale-95">
                    + New Task
                </a>
            </div>
        </div>
    </x-slot>

    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 mb-6">
        <form action="{{ route('tasks.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            {{-- Priority --}}
            <div class="relative w-full sm:w-48">
                <select name="priority" onchange="this.form.submit()" style="background-image:none" class="appearance-none w-full bg-gray-50 border-none text-gray-600 text-xs font-bold rounded-xl px-4 py-3 sm:py-2.5 pr-10 focus:ring-2 focus:ring-indigo-100 cursor-pointer">
                    <option value="">All Priorities</option>
                    <option value="low" {{ request('priority')=='low'?'selected':'' }}>Low</option>
                    <option value="medium" {{ request('priority')=='medium'?'selected':'' }}>Medium</option>
                    <option value="high" {{ request('priority')=='high'?'selected':'' }}>High</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </div>
            </div>
            
            {{-- Category --}}
            <div class="relative w-full sm:w-48">
                <select name="category_id" onchange="this.form.submit()" style="background-image:none" class="appearance-none w-full bg-gray-50 border-none text-gray-600 text-xs font-bold rounded-xl px-4 py-3 sm:py-2.5 pr-10 focus:ring-2 focus:ring-indigo-100 cursor-pointer">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id')==$category->id?'selected':'' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </div>
            </div>

            @if(request()->anyFilled(['priority', 'category_id']))
                <a href="{{ route('tasks.index') }}" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 tracking-widest ml-2">Reset Filter</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[800px]">
                <thead class="bg-gray-50/50 border-b border-gray-100">
                    <tr class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                        <th class="px-8 py-5 text-left">Task Details</th>
                        <th class="px-6 py-5 text-center">Category</th>
                        <th class="px-6 py-5 text-center">Deadline</th>
                        <th class="px-8 py-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($tasks as $task)
                    <tr class="hover:bg-indigo-50/10 transition-all group">
                        <td class="px-8 py-6">
                            <div class="flex items-center">
                                <div class="w-1.5 h-10 rounded-full mr-5 {{ $task->priority == 'high' ? 'bg-red-500 shadow-lg shadow-red-100' : ($task->priority == 'medium' ? 'bg-amber-400' : 'bg-emerald-400') }}"></div>
                                <div class="max-w-[250px]">
                                    <p class="font-bold text-gray-800 text-sm mb-1 truncate group-hover:text-indigo-600">{{ $task->title }}</p>
                                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">{{ $task->priority }} Priority</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-6 text-center">
                            <span class="bg-gray-100 text-gray-500 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-tight">{{ $task->category?->name ?? 'Uncategorized' }}</span>
                        </td>
                        <td class="px-6 py-6 text-center">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold {{ $task->deadline && \Carbon\Carbon::parse($task->deadline)->isPast() ? 'text-red-500' : 'text-gray-500' }}">
                                    {{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('d M Y') : '—' }}
                                </span>
                                @if($task->deadline && \Carbon\Carbon::parse($task->deadline)->isPast())
                                    <span class="text-[8px] uppercase tracking-tighter text-red-400 font-black">Overdue</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex justify-end items-center gap-1">
                                <a href="{{ route('tasks.edit', $task->id) }}" class="p-2 text-blue-400 hover:bg-blue-50 rounded-xl transition-all" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></a>
                                <form action="{{ route('tasks.done', $task->id) }}" method="POST">@csrf @method('PATCH')
                                    <button type="submit" class="p-2 text-emerald-400 hover:bg-emerald-50 rounded-xl transition-all" title="Done"><svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></button>
                                </form>
                                <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Move to trash?')">@csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-red-300 hover:bg-red-50 rounded-xl transition-all" title="Delete"><svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-24 text-center text-gray-400 font-black text-xs uppercase tracking-widest">No tasks found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>