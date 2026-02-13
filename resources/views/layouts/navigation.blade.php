<div class="relative">
    <button onclick="toggleProfileMenu()" class="flex items-center gap-2 focus:outline-none">
        <span class="text-sm font-bold text-gray-700">{{ Auth::user()->name }}</span>
        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div id="profile-menu" class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-100 rounded-xl shadow-xl z-50">
        <div class="py-2">
            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-xs font-bold text-gray-600 hover:bg-indigo-50 hover:text-indigo-600">Profile</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-2 text-xs font-bold text-red-500 hover:bg-red-50">Log Out</button>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleProfileMenu() {
        const menu = document.getElementById('profile-menu');
        menu.classList.toggle('hidden');
    }

    // Biar menu ketutup kalau klik di luar
    window.addEventListener('click', function(e) {
        const menu = document.getElementById('profile-menu');
        const btn = e.target.closest('button');
        if (!btn && !menu.classList.contains('hidden')) {
            menu.classList.add('hidden');
        }
    });
</script>