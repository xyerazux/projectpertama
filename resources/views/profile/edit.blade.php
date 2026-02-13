<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-1.5 h-6 bg-indigo-600 rounded-full"></div>
            <h2 class="font-black text-xl text-gray-800 tracking-tight uppercase">
                {{ __('Account Settings') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-10 bg-gray-50/50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            {{-- SEKSI 1: IDENTITY DETAILS (Sekarang jadi yang utama) --}}
            <div class="bg-white p-10 rounded-[2.5rem] border border-gray-100 shadow-sm transition hover:shadow-md">
                <h3 class="text-xs font-black text-gray-900 uppercase tracking-[0.2em] mb-8 flex items-center gap-2">
                    <span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
                    Identity Details
                </h3>
                @include('profile.partials.update-profile-information-form')
            </div>

            {{-- SEKSI 2: PRIORITY SYSTEM --}}
            <div class="bg-white p-10 rounded-[2.5rem] border border-gray-100 shadow-sm transition hover:shadow-md">
                <h3 class="text-xs font-black text-gray-900 uppercase tracking-[0.2em] mb-8 flex items-center gap-2">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                    Priority System
                </h3>
                <form method="POST" action="{{ route('profile.priority') }}" class="max-w-xl space-y-6">
                    @csrf @method('PATCH')
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3 ml-1">Calculation Mode</label>
                        <select name="priority_mode" class="w-full px-6 py-4 bg-gray-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-100 focus:bg-white transition-all text-[15px] font-semibold text-gray-700">
                            <option value="manual" {{ auth()->user()->priority_mode == 'manual' ? 'selected' : '' }}>Manual Control</option>
                            <option value="auto" {{ auth()->user()->priority_mode == 'auto' ? 'selected' : '' }}>Smart Automation</option>
                        </select>
                    </div>
                    <button type="submit" class="px-10 py-4 bg-indigo-600 text-white rounded-2xl font-bold text-xs uppercase tracking-[0.2em] shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95">
                        Update Preferences
                    </button>
                </form>
            </div>

            {{-- SEKSI 3: SECURITY --}}
            <div class="bg-white p-10 rounded-[2.5rem] border border-gray-100 shadow-sm transition hover:shadow-md">
                <h3 class="text-xs font-black text-gray-900 uppercase tracking-[0.2em] mb-8 flex items-center gap-2">
                    <span class="w-2 h-2 bg-amber-500 rounded-full"></span>
                    Security
                </h3>
                @include('profile.partials.update-password-form')
            </div>

            {{-- SEKSI 4: DANGER ZONE --}}
            <div class="bg-red-50/20 p-10 rounded-[2.5rem] border border-red-100 border-dashed transition hover:bg-red-50/40">
                <h3 class="text-xs font-black text-red-600 uppercase tracking-[0.2em] mb-8 flex items-center gap-2">
                    <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                    Terminal (Danger Zone)
                </h3>
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>