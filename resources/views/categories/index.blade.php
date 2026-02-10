<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800">Categories</h2>
        <p class="text-sm text-gray-500 mt-1">Manage your task categories</p>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white p-6 rounded-xl shadow-sm mb-6">
                <form action="{{ route('categories.store') }}" method="POST" class="flex gap-4">
                    @csrf
                    <input type="text" name="name" placeholder="Category name (e.g., School)" 
                           class="flex-1 border rounded-lg px-4 py-2 focus:ring-indigo-200" required>
                    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-semibold transition">
                        Add New
                    </button>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse ($categories as $category)
                    <div class="bg-white rounded-xl shadow-sm p-6 flex justify-between items-center">
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800">{{ $category->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $category->tasks_count }} tasks</p>
                        </div>

                        <form action="{{ route('categories.destroy', $category->id) }}" method="POST" 
                              onsubmit="return confirm('Delete this category?')">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:text-red-700 font-semibold">Delete</button>
                        </form>
                    </div>
                @empty
                    <p class="text-gray-500">No categories found.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>