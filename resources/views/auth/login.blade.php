<x-guest-layout>
    <style>
        [x-cloak] { display: none !important; }
        html, body { 
            height: 100%; 
            overflow: hidden; 
            margin: 0;
            background-color: #f8fafc;
        }
    </style>

    <div class="h-screen w-full flex items-center justify-center px-6">
        <div class="w-full max-w-[400px] animate-in fade-in zoom-in duration-500">
            <div class="flex justify-center mb-8">
                <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-xl shadow-indigo-200 rotate-3 hover:rotate-0 transition-transform duration-300">
                    <span class="text-white font-black text-2xl italic tracking-tighter">P.</span>
                </div>
            </div>
            
            <div class="bg-white py-10 px-10 shadow-2xl shadow-slate-200/60 border border-slate-100 rounded-[2.5rem]">
                <div class="mb-8 text-center">
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Welcome back</h2>
                    <p class="text-xs text-slate-400 mt-2 font-bold uppercase tracking-widest">Sign in to your account</p>
                </div>

                {{-- Notifikasi Sukses (Termasuk Hapus Akun) --}}
                @if (session('status'))
                    <div class="mb-6 font-bold text-[11px] uppercase tracking-wider text-green-600 bg-green-50 p-4 rounded-2xl border border-green-100 text-center animate-bounce">
                        {{ session('status') }}
                    </div>
                @endif

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
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus 
                            class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-indigo-100 focus:bg-white transition-all duration-200 placeholder-slate-300"
                            placeholder="name@company.com">
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2 px-1">
                            <label for="password" class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-[10px] font-black uppercase tracking-widest text-indigo-600 hover:text-indigo-400 transition-colors">
                                    Forgot?
                                </a>
                            @endif
                        </div>
                        
                        <div class="relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   class="w-full bg-gray-50 border-none text-gray-800 text-sm font-bold rounded-2xl px-5 py-3.5 focus:ring-2 focus:ring-indigo-100 transition-all placeholder-slate-300"
                                   placeholder="••••••••">

                            <button type="button" 
                                    @click="show = !show" 
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-indigo-300 hover:text-indigo-600 focus:outline-none transition-colors">
                                <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center ml-1">
                        <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded-lg border-slate-200 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        <label for="remember_me" class="ml-3 text-[11px] font-bold text-slate-500 cursor-pointer select-none uppercase tracking-wide">Stay logged in</label>
                    </div>

                    <button type="submit" class="w-full py-4 bg-indigo-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] hover:bg-indigo-700 shadow-xl shadow-indigo-100 transition-all active:scale-[0.97]">
                        Sign In
                    </button>
                </form>

                <div class="mt-10 pt-8 border-t border-slate-50 text-center">
                    <p class="text-[11px] text-slate-400 font-bold uppercase tracking-widest">New here? 
                        <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-400 ml-1">Create Account</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>