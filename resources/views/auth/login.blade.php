<x-guest-layout>
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 px-6">
        <div class="sm:mx-auto sm:w-full sm:max-w-[380px]">
            <div class="flex justify-center mb-6">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-200">
                    <span class="text-white font-black text-xl italic tracking-tighter">P.</span>
                </div>
            </div>
            
            <div class="bg-white py-8 px-10 shadow-sm border border-slate-200 rounded-2xl">
                <div class="mb-8">
                    <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">Welcome back</h2>
                    <p class="text-xs text-slate-400 mt-1 font-medium">Please enter your details to sign in.</p>
                </div>

                @if (session('status'))
                    <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-3 rounded-lg border border-green-200">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200">
                        <ul class="list-disc list-inside text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full px-4 py-2 bg-slate-50 border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 focus:bg-white transition-all duration-200">
                    </div>

                    <div class="mt-4" x-data="{ show: false }">
                        <div class="flex justify-between items-center mb-1">
                            <label for="password" class="text-[10px] font-black uppercase tracking-widest text-gray-400">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-[10px] font-black uppercase tracking-widest text-indigo-600 hover:text-indigo-500">
                                    Forgot?
                                </a>
                            @endif
                        </div>
                        
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   class="w-full bg-gray-50 border-none text-gray-800 text-xs font-bold rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-100 transition-all"
                                   placeholder="••••••••">

                            <button type="button" 
                                    @click="show = !show" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-indigo-400 hover:text-indigo-600 focus:outline-none p-1">
                                
                                <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>

                                <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="flex items-center ml-1">
                        <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        <label for="remember_me" class="ml-2 text-xs font-semibold text-slate-500 cursor-pointer select-none">Remember me</label>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-indigo-600 text-white rounded-lg text-xs font-bold uppercase tracking-widest hover:bg-indigo-700 shadow-md shadow-indigo-100 transition-all active:scale-[0.98]">
                        Sign In
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-slate-50 text-center">
                    <p class="text-xs text-slate-400 font-medium">New user? 
                        <a href="{{ route('register') }}" class="text-indigo-600 font-bold hover:underline ml-1">Create Account</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>