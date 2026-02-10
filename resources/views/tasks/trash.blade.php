<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h2 class="font-black text-3xl text-gray-800 leading-tight tracking-tight">Trash Bin</h2>
                <p class="text-sm text-gray-500 mt-1">Items here are scheduled for permanent deletion.</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('tasks.index') }}" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-gray-50 transition-all active:scale-95 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Tasks
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10 bg-gray-50/30">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50/50 border-b border-gray-100">
                        <tr class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                            <th class="px-8 py-5 text-left">Deleted Task</th>
                            <th class="px-6 py-5 text-center">Category</th>
                            <th class="px-6 py-5 text-center">Deletion Date</th>
                            <th class="px-8 py-5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($tasks as $task)
                        <tr class="hover:bg-red-50/5 transition-all group">
                            {{-- Task Details --}}
                            <td class="px-8 py-6">
                                <div class="flex items-center">
                                    <div class="w-1.5 h-10 rounded-full mr-5 bg-gray-300 group-hover:bg-red-400 transition-colors"></div>
                                    <div>
                                        <p class="font-bold text-gray-600 text-sm mb-1 group-hover:text-gray-800 transition-colors line-through decoration-gray-300">{{ $task->title }}</p>
                                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Original: {{ $task->priority }} priority</span>
                                    </div>
                                </div>
                            </td>

                            {{-- Category --}}
                            <td class="px-6 py-6 text-center">
                                <span class="bg-gray-100 text-gray-400 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-tight">
                                    {{ $task->category?->name ?? 'None' }}
                                </span>
                            </td>

                            {{-- Deleted Date --}}
                            <td class="px-6 py-6 text-center">
                                <span class="text-xs font-bold text-gray-400">
                                    {{ $task->deleted_at->format('d M Y') }}
                                </span>
                            </td>

                            {{-- Action Icons --}}
                            <td class="px-8 py-6 text-right">
                                <div class="flex justify-end items-center gap-1">
                                    
                                    {{-- RESTORE BUTTON --}}
                                    <form action="{{ route('tasks.restore', $task->id) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="p-2.5 text-indigo-400 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl transition-all active:scale-90" title="Restore Task">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                        </button>
                                    </form>

                                    {{-- FORCE DELETE BUTTON --}}
                                    <form action="{{ route('tasks.forceDelete', $task->id) }}" method="POST" class="inline" onsubmit="return confirm('Destroy forever? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2.5 text-red-400 hover:bg-red-600 hover:text-white rounded-xl transition-all active:scale-90" title="Purge Forever">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-24 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="bg-gray-50 p-6 rounded-full mb-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </div>
                                    <p class="text-gray-400 font-black text-xs uppercase tracking-[0.2em]">The trash bin is empty.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>