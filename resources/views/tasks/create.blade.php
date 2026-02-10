<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-1.5 h-5 bg-indigo-600 rounded-full"></div>
            <h2 class="font-black text-lg text-gray-800 tracking-tight uppercase">
                {{ isset($task) ? 'Modify Task' : 'Create New Task' }}
            </h2>
        </div>
    </x-slot>

    <div class="py-10 bg-gray-50/50">
        <div class="max-w-3xl mx-auto px-4">
            <form action="{{ isset($task) ? route('tasks.update', $task->id) : route('tasks.store') }}" method="POST" class="space-y-6">
                @csrf
                @if(isset($task)) @method('PUT') @endif

                {{-- CARD UTAMA --}}
                <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm space-y-8">
                    
                    {{-- Input Nama Task --}}
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1">Task Name</label>
                        <input type="text" name="title" value="{{ $task->title ?? '' }}" required 
                            placeholder="What needs to be done?" 
                            class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500/10 focus:bg-white transition-all text-base font-bold text-gray-700 placeholder:text-gray-300">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        {{-- Dropdown Kategori --}}
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1">Category</label>
                            <select name="category_id" class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500/10 focus:bg-white transition-all font-bold text-gray-600 text-sm">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ (isset($task) && $task->category_id == $category->id) ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Input Tanggal (Perbaikan Error format string) --}}
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1">Due Date</label>
                            <input type="date" name="deadline" 
                                class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500/10 transition-all font-bold text-gray-600 text-sm" 
                                value="{{ isset($task) && $task->deadline ? (\Illuminate\Support\Carbon::parse($task->deadline)->format('Y-m-d')) : '' }}">
                        </div>
                    </div>

                    {{-- Pilihan Priority (Hanya jika Mode Manual) --}}
                    @if(auth()->user()->priority_mode == 'manual')
                    <div class="pt-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 ml-1">Priority Level</label>
                        <div class="flex gap-4">
                            @foreach(['low', 'medium', 'high'] as $p)
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="priority" value="{{ $p }}" class="hidden peer" {{ (isset($task) && $task->priority == $p) ? 'checked' : ($p == 'low' ? 'checked' : '') }}>
                                    <div class="text-center py-4 rounded-2xl border border-gray-100 text-gray-400 font-black uppercase tracking-widest text-[10px] peer-checked:border-indigo-500 peer-checked:text-indigo-600 peer-checked:bg-indigo-50/50 transition-all">
                                        {{ $p }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                {{-- TOMBOL ACTION (Lebih Simple & Profesional) --}}
                <div class="flex items-center justify-between px-2">
                    <a href="{{ route('tasks.index') }}" class="text-[11px] font-black text-gray-400 uppercase tracking-widest hover:text-gray-700 transition-all">
                        ← Cancel
                    </a>
                    
                    <button type="submit" class="px-10 py-4 bg-gray-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-indigo-600 shadow-xl shadow-gray-200 hover:shadow-indigo-100 transition-all active:scale-95">
                        {{ isset($task) ? 'Update Task' : 'Save Task' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>