<x-guest-layout>
    <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 px-6">
        <div class="sm:mx-auto sm:w-full sm:max-w-[400px]">
            <div class="bg-white py-8 px-10 shadow-sm border border-slate-200 rounded-2xl">
                <div class="mb-8 text-center sm:text-left">
                    <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">Create Account</h2>
                    <p class="text-xs text-slate-400 mt-1 font-medium">Join us to start organizing your tasks.</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 ml-1">Full Name</label>
                        <input type="text" name="name" :value="old('name')" required autofocus class="w-full px-4 py-2 bg-slate-50 border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 focus:bg-white transition-all">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 ml-1">Email</label>
                        <input type="email" name="email" :value="old('email')" required class="w-full px-4 py-2 bg-slate-50 border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 focus:bg-white transition-all">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 ml-1">Password</label>
                            <input type="password" name="password" required class="w-full px-4 py-2 bg-slate-50 border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 focus:bg-white transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 ml-1">Confirm</label>
                            <input type="password" name="password_confirmation" required class="w-full px-4 py-2 bg-slate-50 border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 focus:bg-white transition-all">
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-2.5 bg-slate-800 text-white rounded-lg text-xs font-bold uppercase tracking-widest hover:bg-black shadow-md transition-all active:scale-[0.98]">
                            Register Now
                        </button>
                    </div>
                </form>

                <div class="mt-8 pt-6 border-t border-slate-50 text-center">
                    <p class="text-xs text-slate-400 font-medium">Already have an account? 
                        <a href="{{ route('login') }}" class="text-indigo-600 font-bold hover:underline ml-1">Sign In</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>