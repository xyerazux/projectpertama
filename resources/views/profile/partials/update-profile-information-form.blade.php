<section>
    <header class="mb-6">
        <h3 class="text-lg font-bold text-gray-900">
            Profile Information
        </h3>
        <p class="text-sm text-gray-500">
            Update your account's profile information and email address.
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div>
            <label class="block font-semibold">Name</label>
            <input type="text" name="name"
                value="{{ old('name', auth()->user()->name) }}"
                class="w-full border rounded p-2">
        </div>

        <div>
            <label class="block font-semibold">Email</label>
            <input type="email" name="email"
                value="{{ old('email', auth()->user()->email) }}"
                class="w-full border rounded p-2">
        </div>

        <button class="px-4 py-2 bg-indigo-600 text-white rounded">
            Save
        </button>
    </form>
</section>
