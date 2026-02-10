<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-extrabold text-xl text-slate-800 tracking-tight">
                    {{ __('Completed Archive') }}
                </h2>
                <div class="flex items-center gap-2 mt-1">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">History Log</p>
                </div>
            </div>
            
            <a href="{{ route('tasks.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-[10px] font-black text-slate-500 uppercase tracking-widest hover:border-indigo-500 hover:text-indigo-600 transition-all shadow-sm">
                ← Return
            </a>
        </div>
    </x-slot>

    <div class="py-8 px-4 max-w-5xl mx-auto">
        <div class="space-y-3">
            @forelse ($tasks as $task)
                <div class="bg-white border border-slate-200 rounded-2xl p-4 hover:border-indigo-100 transition-all flex items-center justify-between group">
                    
                    <div class="flex items-center gap-4 flex-1 min-w-0">
                        <div class="hidden sm:flex flex-col items-center justify-center w-12 h-12 bg-slate-50 rounded-xl border border-slate-100 flex-shrink-0">
                            <span class="text-[9px] font-black text-slate-400 uppercase">{{ $task->updated_at->format('M') }}</span>
                            <span class="text-sm font-bold text-slate-700">{{ $task->updated_at->format('d') }}</span>
                        </div>

                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-slate-700 truncate group-hover:text-indigo-600 transition-colors">
                                {{ $task->title }}
                            </h3>
                            <div class="flex flex-wrap items-center gap-y-1 gap-x-3 mt-1">
                                <span class="flex items-center gap-1 text-[10px] font-bold text-slate-400">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    {{ $task->category->name ?? 'Uncategorized' }}
                                </span>
                                <span class="text-[10px] font-black uppercase tracking-tighter px-2 py-0.5 rounded {{ match($task->priority) { 'high' => 'bg-red-50 text-red-500', 'medium' => 'bg-amber-50 text-amber-500', 'low' => 'bg-emerald-50 text-emerald-500', default => 'bg-slate-50 text-slate-400' } }}">
                                    {{ $task->priority }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-6 ml-4">
                        <div class="hidden md:block text-right">
                            <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest">Deadline</p>
                            <p class="text-[10px] font-bold text-slate-500 italic">{{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('d M y') : '-' }}</p>
                        </div>

                        <div class="flex items-center gap-2 bg-green-50 border border-green-100 px-3 py-1.5 rounded-xl">
                            <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                            <span class="text-[10px] font-black text-green-700 uppercase tracking-[0.1em]">Done</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-24 bg-white border border-dashed border-slate-200 rounded-[2rem] text-center">
                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-200">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">No history recorded yet</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>