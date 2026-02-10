<x-guest-layout>
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

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Email Address</label>
                        <input type="email" name="email" required autofocus class="w-full px-4 py-2 bg-slate-50 border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 focus:bg-white transition-all duration-200">
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-1.5 ml-1">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-700">Forgot?</a>
                            @endif
                        </div>
                        <input type="password" name="password" required class="w-full px-4 py-2 bg-slate-50 border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 focus:bg-white transition-all duration-200">
                    </div>

                    <div class="flex items-center ml-1">
                        <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-xs font-semibold text-slate-500">Remember me</span>
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