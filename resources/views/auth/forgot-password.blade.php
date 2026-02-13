<x-guest-layout>
    <div class="min-h-screen bg-slate-50 flex flex-col justify-center items-center py-12 px-6">
        <div class="w-full max-w-[400px]">
            {{-- Logo Section --}}
            <div class="flex justify-center mb-8">
                <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-xl shadow-indigo-200 rotate-3">
                    <span class="text-white font-black text-2xl italic tracking-tighter">P.</span>
                </div>
            </div>

            {{-- Card Section --}}
            <div class="bg-white p-8 md:p-10 shadow-sm border border-slate-200 rounded-[2.5rem]">
                <div class="mb-8">
                    <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Forgot Password?</h2>
                    <p class="text-[13px] text-slate-400 mt-2 font-medium leading-relaxed">
                        No problem. Enter your email address and we'll send a reset link to your inbox.
                    </p>
                </div>

                {{-- Notifikasi Sukses --}}
                @if (session('status'))
                    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-xs text-emerald-700 font-bold leading-tight">{{ session('status') }}</p>
                    </div>
                @endif

                {{-- Error Handling --}}
                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-100">
                        <div class="flex items-center gap-2 mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-[10px] font-black text-red-600 uppercase tracking-widest">Error Occurred</span>
                        </div>
                        <ul class="text-xs text-red-500 font-medium list-none">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] mb-2 ml-1">Email Address</label>
                        <input type="email" name="email" :value="old('email')" required autofocus 
                            class="w-full px-5 py-3.5 bg-slate-50 border-slate-200 rounded-2xl text-sm font-semibold focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 focus:bg-white transition-all duration-300 placeholder:text-slate-300" 
                            placeholder="example@mail.com">
                    </div>

                    <button type="submit" class="w-full py-4 bg-indigo-600 text-white rounded-2xl text-xs font-black uppercase tracking-[0.15em] hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all active:scale-[0.97] flex items-center justify-center gap-2">
                        Send Reset Link
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </form>

                <div class="mt-10 pt-6 border-t border-slate-50 text-center">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-xs font-black text-slate-400 hover:text-indigo-600 transition-colors uppercase tracking-widest">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Login
                    </a>
                </div>
            </div>
            
            <p class="text-center mt-8 text-[10px] font-bold text-slate-300 uppercase tracking-[0.2em]">Productivity App &copy; 2026</p>
        </div>
    </div>
</x-guest-layout>