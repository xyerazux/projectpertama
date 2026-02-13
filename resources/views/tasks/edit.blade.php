<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-1.5 h-5 bg-indigo-600 rounded-full"></div>
            <h2 class="font-black text-lg text-gray-800 tracking-tight uppercase">Edit Task</h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4">
            <form action="{{ route('tasks.update', $task->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm space-y-6">
                    {{-- Status Toggle --}}
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Mark as Completed</span>
                        <select name="status" class="bg-transparent border-none font-bold text-sm text-indigo-600 focus:ring-0 cursor-pointer">
                            <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>

                    {{-- Task Name --}}
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Task Name</label>
                        <input type="text" name="title" value="{{ old('title', $task->title) }}" required 
                            class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500/10 font-bold">
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Description</label>
                        <textarea name="description" rows="3" 
                            class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500/10 font-semibold text-sm">{{ old('description', $task->description) }}</textarea>
                    </div>

                    {{-- Link Attachment --}}
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Attachment Link (URL)</label>
                        <input type="url" name="link_attachment" value="{{ old('link_attachment', $task->link_attachment) }}" 
                            class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500/10 font-bold text-indigo-500">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Category --}}
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Category</label>
                            <select name="category_id" required class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl font-bold text-sm">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ $task->category_id == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Due Date --}}
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Due Date</label>
                            <input type="date" name="deadline" value="{{ old('deadline', $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('Y-m-d') : '') }}" required 
                                class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl font-bold text-sm">
                        </div>
                    </div>

                    {{-- Priority --}}
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 ml-1">Priority</label>
                        <div class="flex gap-4">
                            @foreach(['low', 'medium', 'high'] as $p)
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="priority" value="{{ $p }}" class="hidden peer" {{ $task->priority == $p ? 'checked' : '' }}>
                                    <div class="text-center py-4 rounded-2xl border border-gray-100 text-gray-400 font-black uppercase text-[10px] tracking-widest peer-checked:border-indigo-500 peer-checked:text-indigo-600 peer-checked:bg-indigo-50 transition-all">
                                        {{ $p }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between px-2">
                    <a href="{{ route('tasks.index') }}" class="text-[11px] font-black text-gray-400 uppercase tracking-widest">
                        ← Cancel
                    </a>
                    <button type="submit" class="px-12 py-4 bg-gray-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-indigo-600 shadow-xl transition-all">
                        Update Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>