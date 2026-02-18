<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-1.5 h-5 bg-indigo-600 rounded-full"></div>
            <h2 class="font-black text-lg text-gray-800 tracking-tight uppercase">Edit Task</h2>
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

            <form action="{{ route('tasks.update', $task->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm space-y-6">
                    {{-- Status Header --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-6 bg-gray-50 rounded-[1.5rem] gap-4">
                        <div>
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Current Status</span>
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full {{ $task->status == 'completed' ? 'bg-emerald-500' : 'bg-amber-500' }}"></div>
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-700">{{ strtoupper($task->status) }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 bg-white p-2 rounded-xl border border-gray-100 shadow-sm">
                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest ml-2">Change To</span>
                            <select name="status" class="bg-transparent border-none font-black text-[10px] uppercase text-indigo-600 focus:ring-0 cursor-pointer">
                                <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                    </div>

                    {{-- Title, Description & Link --}}
                    <div class="grid gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Task Title</label>
                            <input type="text" name="title" value="{{ old('title', $task->title) }}" required 
                                class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500/10 font-bold transition-all" placeholder="Enter task name...">
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Task Description</label>
                            <textarea name="description" rows="3" 
                                class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500/10 font-semibold text-sm transition-all" placeholder="Describe the task details...">{{ old('description', $task->description) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Attachment Link</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.828a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                    </svg>
                                </div>
                                <input type="url" name="link_attachment" value="{{ old('link_attachment', $task->link_attachment) }}" 
                                    class="w-full pl-14 pr-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500/10 font-bold text-sm transition-all text-indigo-600 placeholder:text-gray-300" placeholder="https://example.com">
                            </div>
                        </div>
                    </div>

                    {{-- Priority Settings --}}
                    @if(Auth::user()->priority_mode === 'manual')
                    <div class="pt-6 border-t border-gray-50">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Task Priority</label>
                        <select name="priority" class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl font-bold text-xs uppercase tracking-widest cursor-pointer focus:ring-2 focus:ring-indigo-500/10 transition-all">
                            <option value="low" {{ $task->priority == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ $task->priority == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ $task->priority == 'high' ? 'selected' : '' }}>High</option>
                        </select>
                    </div>
                    @else
                    <div class="pt-6 border-t border-gray-50">
                        <div class="p-5 bg-indigo-50/50 rounded-[1.5rem] border border-indigo-100 flex items-center gap-4">
                            <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-indigo-900 uppercase tracking-widest">Smart Priority Active</p>
                                <p class="text-[9px] font-bold text-indigo-600/70 leading-relaxed">Priority is managed automatically based on your deadline.</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Subtasks Section --}}
                    <div class="pt-6 border-t border-gray-50">
                        <label class="block text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-4 ml-1">Task Roadmap / Checklist</label>
                        <div id="subtask-container" class="space-y-3">
                            @foreach($task->subtasks as $sub)
                                <div class="flex items-center gap-3 group bg-gray-50/50 p-3 rounded-2xl border border-transparent hover:border-gray-100 transition-all">
                                    <input type="hidden" name="subtasks_status[{{ $sub->id }}]" value="0">
                                    <input type="checkbox" 
                                           name="subtasks_status[{{ $sub->id }}]" 
                                           value="1" 
                                           {{ $sub->is_completed ? 'checked' : '' }}
                                           class="w-5 h-5 rounded-lg border-gray-300 text-indigo-600 focus:ring-indigo-500/10 transition-all cursor-pointer">
                                    <input type="text" name="existing_subtasks[{{ $sub->id }}]" value="{{ $sub->title }}" 
                                        class="flex-1 bg-transparent border-none p-0 text-sm font-bold text-gray-700 focus:ring-0">
                                </div>
                            @endforeach
                        </div>
                        
                        <button type="button" onclick="addSubtaskField()" class="mt-4 flex items-center gap-3 text-[10px] font-black uppercase text-indigo-600 hover:text-indigo-800 transition-all group">
                            <span class="w-8 h-8 bg-indigo-50 rounded-xl flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-all">+</span>
                            Add New Step
                        </button>
                    </div>

                    {{-- Meta Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-gray-50">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Category</label>
                            <select name="category_id" required class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl font-bold text-sm cursor-pointer focus:ring-2 focus:ring-indigo-500/10 transition-all">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ $task->category_id == $category->id ? 'selected' : '' }}>
                                        {{ strtoupper($category->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Due Date</label>
                            <input type="date" name="deadline" value="{{ old('deadline', $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('Y-m-d') : '') }}" required 
                                class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl font-bold text-sm focus:ring-2 focus:ring-indigo-500/10 transition-all">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between px-2 pb-10">
                    <a href="{{ route('tasks.index') }}" class="text-[11px] font-black text-gray-400 uppercase tracking-widest hover:text-gray-700 transition-all">
                        ← Back to List
                    </a>
                    <button type="submit" class="px-12 py-4 bg-gray-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-indigo-600 shadow-xl shadow-gray-200 hover:shadow-indigo-100 transition-all active:scale-95">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function addSubtaskField() {
            const container = document.getElementById('subtask-container');
            const div = document.createElement('div');
            div.className = 'flex gap-3 items-center p-3 bg-gray-50 rounded-2xl border border-dashed border-gray-200 animate-slide-down';
            div.innerHTML = `
                <div class="w-5 h-5 bg-indigo-100 rounded-lg flex items-center justify-center shrink-0">
                    <div class="w-1.5 h-1.5 bg-indigo-500 rounded-full"></div>
                </div>
                <input type="text" name="subtasks[]" class="flex-1 bg-transparent border-none p-0 text-sm font-bold text-gray-700 focus:ring-0" placeholder="Type new step here...">
                <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600 font-black text-lg px-2">×</button>
            `;
            container.appendChild(div);
        }
    </script>

    <style>
        @keyframes slide-down {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-slide-down { animation: slide-down 0.3s ease-out; }
    </style>
</x-app-layout>