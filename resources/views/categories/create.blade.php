<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl">Tambah Kategori</h2>
    </x-slot>

    <div class="py-12 max-w-md mx-auto">
        <form method="POST" action="{{ route('categories.store') }}">
            @csrf
            <input name="name"
                   class="w-full border rounded px-3 py-2 mb-3"
                   placeholder="Nama kategori">

            <button class="bg-indigo-600 text-white px-4 py-2 rounded">
                Simpan
            </button>
        </form>
    </div>
</x-app-layout>
