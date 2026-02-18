<x-app-layout>
    <x-slot name="header">
        {{-- Penambahan z-50 dan relative agar header selalu di depan --}}
        <div class="relative z-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 leading-tight">
                    {{ __('Dashboard Overview') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 font-medium italic">Welcome back! Here is your productivity summary.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-full shadow-sm">
                    {{ now()->format('M d, Y') }}
                </span>
            </div>
        </div>
    </x-slot>

    {{-- Main Content --}}
    <div class="py-10 bg-gray-50/50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            {{-- Stat Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Total Tasks --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group">
                    <div class="flex items-center justify-between relative z-10">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total Tasks</p>
                            <p class="text-4xl font-black text-gray-800 mt-1">{{ $totalTasks }}</p>
                        </div>
                        <div class="p-4 bg-blue-50 text-blue-500 rounded-2xl group-hover:scale-110 transition-transform">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-blue-500"></div>
                </div>

                {{-- Completed Tasks --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group">
                    <div class="flex items-center justify-between relative z-10">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Completed</p>
                            <p class="text-4xl font-black text-green-600 mt-1">{{ $doneTasks }}</p>
                        </div>
                        <div class="p-4 bg-green-50 text-green-500 rounded-2xl group-hover:scale-110 transition-transform">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-green-500"></div>
                </div>

                {{-- Pending Tasks --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group">
                    <div class="flex items-center justify-between relative z-10">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Pending</p>
                            <p class="text-4xl font-black text-orange-500 mt-1">{{ $pendingTasks }}</p>
                        </div>
                        <div class="p-4 bg-orange-50 text-orange-500 rounded-2xl group-hover:scale-110 transition-transform">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-orange-500"></div>
                </div>
            </div>

            {{-- Progress & Categories --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-center transition hover:border-indigo-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-gray-700 uppercase tracking-widest text-xs italic text-center">Efficiency Score</h3>
                        <span class="text-2xl font-black text-indigo-600">{{ $progress }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-4 overflow-hidden border border-gray-50">
                        <div class="h-4 rounded-full bg-gradient-to-r from-indigo-500 to-blue-500 transition-all duration-1000" style="width: {{ $progress }}%"></div>
                    </div>
                    <p class="mt-4 text-sm text-gray-400 italic">{{ $doneTasks }} out of {{ $totalTasks }} tasks are archived.</p>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 transition hover:border-indigo-100">
                    <h3 class="font-bold text-gray-800 text-lg mb-4 tracking-tight">Tasks by Category</h3>
                    <div class="space-y-3">
                        @foreach ($taskPerCategory as $category)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition border border-transparent hover:border-gray-200">
                            <span class="text-sm font-semibold text-gray-600">{{ $category->name }}</span>
                            <span class="bg-white px-3 py-1 rounded-lg shadow-sm text-xs font-black text-indigo-600 border border-gray-100">{{ $category->tasks_count }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Recent Tasks Table --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 flex justify-between items-center border-b border-gray-50 bg-white">
                    <h3 class="text-lg font-bold text-gray-800 tracking-tight">Recent Tasks</h3>
                    <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-widest rounded-xl transition shadow-lg shadow-indigo-100 active:scale-95">
                        + Add New
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50/50 text-gray-400 font-bold uppercase text-[10px] tracking-[0.2em]">
                            <tr>
                                <th class="py-4 px-6 text-center">Status</th>
                                <th class="py-4 px-6">Task Title</th>
                                <th class="py-4 px-6 text-center">Priority</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse ($recentTasks as $task)
                            <tr class="hover:bg-gray-50/50 transition group">
                                <td class="py-4 px-6 text-center">
                                    <div class="flex justify-center">
                                        {{-- Perbaikan: Cek status 'completed' bukan 'done' --}}
                                        <span class="w-2.5 h-2.5 rounded-full {{ $task->status === 'completed' ? 'bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.4)]' : 'bg-orange-400 shadow-[0_0_8px_rgba(251,146,60,0.4)]' }}"></span>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-bold text-gray-700 group-hover:text-indigo-600 transition-colors leading-tight">{{ $task->title }}</span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border {{ $task->priority == 'high' ? 'bg-red-50 text-red-600 border-red-100' : ($task->priority == 'medium' ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100') }}">
                                        {{ $task->priority }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="py-16 text-center text-gray-400 italic font-medium uppercase tracking-widest text-xs">No tasks recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>