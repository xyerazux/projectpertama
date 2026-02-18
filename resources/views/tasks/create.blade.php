<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-1.5 h-5 bg-indigo-600 rounded-full"></div>
            <h2 class="font-black text-lg text-gray-800 tracking-tight uppercase">Create New Task</h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4">
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-2xl">
                    <ul class="list-disc list-inside text-red-600 text-xs font-bold uppercase tracking-widest">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('tasks.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm space-y-6">
                    {{-- Task Name --}}
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Task Name</label>
                        <input type="text" name="title" value="{{ old('title') }}" required 
                            placeholder="e.g. Finish Project Alpha"
                            class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500/10 font-bold @error('title') ring-2 ring-red-200 @enderror">
                        @error('title') <p class="text-red-500 text-[10px] mt-1 ml-1 font-bold">{{ $message }}</p> @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Description</label>
                        <textarea name="description" rows="3" 
                            placeholder="Provide more details..."
                            class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500/10 font-semibold text-sm">{{ old('description') }}</textarea>
                    </div>

                    {{-- Subtasks Section --}}
                    <div class="pt-4 border-t border-gray-50">
                        <label class="block text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-3 ml-1">To-Do List / Subtasks</label>
                        <div id="subtask-container" class="space-y-3">
                            <div class="flex gap-2 items-center group">
                                <div class="w-1.5 h-1.5 bg-indigo-200 rounded-full shrink-0"></div>
                                <input type="text" name="subtasks[]" 
                                    class="w-full bg-gray-50 border-none text-xs font-bold rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500/10" 
                                    placeholder="Contoh: Baca bab 1">
                                <button type="button" onclick="addSubtaskField()" class="bg-indigo-50 text-indigo-600 w-10 h-10 rounded-xl font-black text-xl hover:bg-indigo-600 hover:text-white transition-all">+</button>
                            </div>
                        </div>
                        <p class="text-[9px] text-gray-400 mt-2 ml-1 italic">*Klik + untuk menambah langkah pengerjaan</p>
                    </div>

                    {{-- Attachment Link --}}
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Attachment Link (URL)</label>
                        <input type="url" name="link_attachment" value="{{ old('link_attachment') }}" 
                            placeholder="https://example.com"
                            class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500/10 font-bold text-indigo-500">
                    </div>

                    {{-- Meta Grid (Category & Due Date) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Category</label>
                            <select name="category_id" required 
                                class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl font-bold text-sm cursor-pointer @error('category_id') ring-2 ring-red-200 @enderror">
                                <option value="" disabled selected>Choose a category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ strtoupper($category->name) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id') <p class="text-red-500 text-[10px] mt-1 ml-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Due Date</label>
                            <input type="date" name="deadline" value="{{ old('deadline') }}" required 
                                class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl font-bold text-sm @error('deadline') ring-2 ring-red-200 @enderror">
                            @error('deadline') <p class="text-red-500 text-[10px] mt-1 ml-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Priority Selection --}}
                    @if(auth()->user()->priority_mode == 'manual')
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 ml-1">Priority</label>
                        <div class="flex gap-4">
                            @foreach(['low', 'medium', 'high'] as $p)
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="priority" value="{{ $p }}" class="hidden peer" {{ (old('priority') ?? 'low') == $p ? 'checked' : '' }}>
                                    <div class="text-center py-4 rounded-2xl border border-gray-100 text-gray-400 font-black uppercase text-[10px] tracking-widest peer-checked:border-indigo-500 peer-checked:text-indigo-600 peer-checked:bg-indigo-50 transition-all hover:bg-gray-50">
                                        {{ $p }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @else
                        <input type="hidden" name="priority" value="low">
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-between px-2 pb-10">
                    <a href="{{ route('tasks.index') }}" class="text-[11px] font-black text-gray-400 uppercase tracking-widest hover:text-gray-700 transition-all">
                        ← Cancel
                    </a>
                    <button type="submit" class="px-12 py-4 bg-gray-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-indigo-600 shadow-xl shadow-gray-200 hover:shadow-indigo-100 transition-all active:scale-95">
                        Save Task
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function addSubtaskField() {
            const container = document.getElementById('subtask-container');
            const div = document.createElement('div');
            div.className = 'flex gap-2 items-center animate-slide-down';
            div.innerHTML = `
                <div class="w-1.5 h-1.5 bg-indigo-200 rounded-full shrink-0"></div>
                <input type="text" name="subtasks[]" class="w-full bg-gray-50 border-none text-xs font-bold rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500/10" placeholder="Langkah selanjutnya...">
                <button type="button" onclick="this.parentElement.remove()" class="bg-red-50 text-red-500 w-10 h-10 rounded-xl font-black text-xl hover:bg-red-500 hover:text-white transition-all">×</button>
            `;
            container.appendChild(div);
        }
    </script>

    <style>
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-slide-down {
            animation: slideDown 0.3s ease-out forwards;
        }
    </style>
</x-app-layout>