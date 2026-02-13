<x-guest-layout>
    <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 px-6">
        <div class="sm:mx-auto sm:w-full sm:max-w-[400px]">
            <div class="flex justify-center mb-6">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-200">
                    <span class="text-white font-black text-xl italic tracking-tighter">P.</span>
                </div>
            </div>

            <div class="bg-white py-8 px-10 shadow-sm border border-slate-200 rounded-2xl">
                <div class="mb-8 text-center sm:text-left">
                    <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">Set New Password</h2>
                    <p class="text-xs text-slate-400 mt-1 font-medium">Please enter your new password below.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100">
                        <ul class="text-[10px] text-red-600 font-bold uppercase tracking-wide space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
                    @csrf

                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $request->email) }}" required readonly class="w-full px-4 py-2 bg-slate-100 border-slate-200 rounded-lg text-sm text-slate-500 cursor-not-allowed">
                    </div>

                    <div x-data="{ show: false }">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">New Password</label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="password" required autofocus class="w-full px-4 py-2 bg-slate-50 border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 focus:bg-white transition-all">
                            <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-600 transition-colors">
                                <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Confirm Password</label>
                        <input type="password" name="password_confirmation" required class="w-full px-4 py-2 bg-slate-50 border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 focus:bg-white transition-all">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-2.5 bg-indigo-600 text-white rounded-lg text-xs font-bold uppercase tracking-widest hover:bg-indigo-700 shadow-md shadow-indigo-100 transition-all active:scale-[0.98]">
                            Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>