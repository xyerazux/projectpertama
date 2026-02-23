<x-guest-layout>
    <style>
        [x-cloak] { display: none !important; }
        html, body { 
            height: 100%; 
            overflow: hidden; 
            margin: 0;
            background-color: #f8fafc;
        }
        @media (max-width: 640px) {
            .touch-target { min-height: 44px; min-width: 44px; }
        }
    </style>

    <div class="min-h-screen w-full flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-[400px] animate-in fade-in zoom-in duration-500">
            {{-- Logo Section --}}
            <div class="flex justify-center mb-6">
                <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-xl shadow-indigo-200 rotate-3 hover:rotate-0 transition-transform duration-300">
                    <span class="text-white font-black text-2xl italic tracking-tighter">P.</span>
                </div>
            </div>
            
            <div class="bg-white py-10 px-8 sm:px-10 shadow-2xl shadow-slate-200/60 border border-slate-100 rounded-[2.5rem]">
                <div class="mb-8 text-center">
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Welcome Back</h2>
                    <p class="text-xs text-slate-400 mt-2 font-bold uppercase tracking-widest">Sign In To Your Account</p>
                </div>

                {{-- Status Message --}}
                @if (session('status'))
                    <div class="mb-6 font-bold text-[11px] uppercase tracking-wider text-emerald-600 bg-emerald-50 p-4 rounded-2xl border border-emerald-100 text-center">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Error Messages --}}
                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100">
                        <ul class="list-none text-[10px] text-red-600 font-black uppercase tracking-wide space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf
                    
                    {{-- Email --}}
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus 
                            class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-indigo-100 focus:bg-white transition-all duration-200 placeholder-slate-300 touch-target"
                            placeholder="name@company.com"
                            maxlength="255"
                            autocomplete="email">
                    </div>

                    {{-- Password with Eye Icon --}}
                    <div>
                        <div class="flex justify-between items-center mb-2 px-1">
                            <label for="password" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-[10px] font-black uppercase tracking-widest text-indigo-600 hover:text-indigo-400 transition-colors">
                                    Forgot?
                                </a>
                            @endif
                        </div>
                        
                        <div class="relative">
                            <input id="login-password" 
                                   name="password" 
                                   type="password" 
                                   required 
                                   autocomplete="current-password"
                                   minlength="8"
                                   maxlength="255"
                                   class="w-full bg-slate-50 border-none text-slate-800 text-sm font-bold rounded-2xl px-5 py-3.5 focus:ring-2 focus:ring-indigo-100 focus:bg-white transition-all placeholder-slate-300 pr-10 touch-target"
                                   placeholder="••••••••">

                            <button type="button" 
                                    id="toggle-login-password"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-300 hover:text-indigo-600 focus:outline-none transition-colors touch-target">
                                <svg id="eye-open-login" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="eye-closed-login" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center ml-1">
                        <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded-lg border-slate-200 text-indigo-600 focus:ring-indigo-500 cursor-pointer touch-target">
                        <label for="remember_me" class="ml-3 text-[11px] font-bold text-slate-500 cursor-pointer select-none uppercase tracking-wide">Stay Logged In</label>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" class="w-full py-4 bg-indigo-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] hover:bg-indigo-700 shadow-xl shadow-indigo-100 transition-all active:scale-[0.97] touch-target">
                        Sign In
                    </button>
                </form>

                {{-- Register Link --}}
                <div class="mt-8 pt-6 border-t border-slate-50 text-center">
                    <p class="text-[11px] text-slate-400 font-bold uppercase tracking-widest mb-4">
                        New Here?
                    </p>
                    <a href="{{ route('register') }}" class="inline-block w-full py-3 border-2 border-indigo-600 text-indigo-600 rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] hover:bg-indigo-600 hover:text-white transition-all touch-target">
                        Create Account
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Password Toggle Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('login-password');
            const toggleBtn = document.getElementById('toggle-login-password');
            const eyeOpen = document.getElementById('eye-open-login');
            const eyeClosed = document.getElementById('eye-closed-login');

            if (toggleBtn && passwordInput) {
                toggleBtn.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    eyeOpen.classList.toggle('hidden');
                    eyeClosed.classList.toggle('hidden');
                });
            }
        });
    </script>
</x-guest-layout>