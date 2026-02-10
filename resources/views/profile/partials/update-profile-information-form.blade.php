<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Name</label>
                <input id="name" name="name" type="text" class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-100 transition-all font-semibold" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">Email</label>
                <input id="email" name="email" type="email" class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-100 transition-all font-semibold" value="{{ old('email', $user->email) }}" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>
        </div>

        <div class="flex items-center gap-4 pt-4">
            <button class="px-8 py-4 bg-gray-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-indigo-600 shadow-lg transition-all active:scale-95">
                Save Identity
            </button>

            @if (session('status') === 'profile-updated')
                <p class="text-xs font-bold text-emerald-500 animate-pulse tracking-wide">Changes Saved.</p>
            @endif
        </div>
    </form>
</section>