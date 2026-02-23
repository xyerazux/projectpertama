<x-guest-layout>
    <style>
        [x-cloak] { display: none !important; }
        html, body { 
            min-height: 100%; 
            margin: 0;
            background-color: #f8fafc;
        }
        .submitting { pointer-events: none; opacity: 0.7; }
        @media (max-width: 640px) {
            .touch-target { min-height: 44px; min-width: 44px; }
        }
    </style>

    {{-- Changed from h-screen to min-h-screen with py-8 --}}
    <div class="min-h-screen w-full flex items-center justify-center px-4 py-8 sm:py-12">
        <div class="w-full max-w-[400px] animate-in fade-in zoom-in duration-500 my-auto">
            {{-- Logo Section --}}
            <div class="flex justify-center mb-6">
                <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-xl shadow-indigo-200 rotate-3 hover:rotate-0 transition-transform duration-300">
                    <span class="text-white font-black text-2xl italic tracking-tighter">P.</span>
                </div>
            </div>
            
            {{-- Reduced padding from py-10 px-10 to py-8 px-6 sm:px-8 --}}
            <div class="bg-white py-8 px-6 sm:py-10 sm:px-8 shadow-2xl shadow-slate-200/60 border border-slate-100 rounded-[2.5rem]">
                <div class="mb-6 sm:mb-8 text-center">
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Create Account</h2>
                    <p class="text-xs text-slate-400 mt-2 font-bold uppercase tracking-widest">Join Our Productivity Circle</p>
                </div>

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

                {{-- 🔐 HONEYPOT: Spam Protection --}}
                <div style="display:none !important" aria-hidden="true">
                    <input type="text" name="website" tabindex="-1" autocomplete="off">
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-5 sm:space-y-6" id="registerForm" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    
                    {{-- Full Name --}}
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Full Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required autofocus 
                            class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-indigo-100 focus:bg-white transition-all duration-200 placeholder-slate-300 touch-target"
                            placeholder="John Doe"
                            maxlength="255"
                            pattern="[a-zA-Z\s]{3,255}"
                            title="Name must be 3-255 characters, letters only"
                            autocomplete="name">
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required 
                            class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-indigo-100 focus:bg-white transition-all duration-200 placeholder-slate-300 touch-target"
                            placeholder="john@example.com"
                            maxlength="255"
                            autocomplete="email">
                    </div>

                    {{-- 🔐 Password & Confirm - SIDE BY SIDE (Grid) --}}
                    <div class="grid grid-cols-2 gap-3">
                        {{-- Password --}}
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Password</label>
                            <div class="relative">
                                <input id="register-password" 
                                       name="password" 
                                       type="password" 
                                       required 
                                       autocomplete="new-password"
                                       minlength="8"
                                       maxlength="255"
                                       class="w-full bg-slate-50 border-none text-slate-800 text-sm font-bold rounded-2xl px-5 py-3.5 focus:ring-2 focus:ring-indigo-100 focus:bg-white transition-all placeholder-slate-300 pr-10 touch-target"
                                       placeholder="••••••••">

                                <button type="button" 
                                        id="toggle-register-password"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-300 hover:text-indigo-600 focus:outline-none transition-colors touch-target">
                                    <svg id="eye-open-register" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg id="eye-closed-register" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                            {{-- Password Strength Indicator --}}
                            <div class="mt-1.5 h-1 bg-slate-200 rounded-full overflow-hidden">
                                <div id="password-strength" class="h-full w-0 bg-red-500 transition-all duration-300"></div>
                            </div>
                        </div>

                        {{-- Confirm Password --}}
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Confirm</label>
                            <div class="relative">
                                <input id="register-password-confirm" 
                                       name="password_confirmation" 
                                       type="password" 
                                       required 
                                       autocomplete="new-password"
                                       minlength="8"
                                       maxlength="255"
                                       class="w-full bg-slate-50 border-none text-slate-800 text-sm font-bold rounded-2xl px-5 py-3.5 focus:ring-2 focus:ring-indigo-100 focus:bg-white transition-all placeholder-slate-300 pr-10 touch-target"
                                       placeholder="••••••••">

                                <button type="button" 
                                        id="toggle-register-confirm"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-300 hover:text-indigo-600 focus:outline-none transition-colors touch-target">
                                    <svg id="eye-open-confirm" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg id="eye-closed-confirm" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Security Info --}}
                    <div class="flex items-start gap-2 p-3 bg-indigo-50 rounded-xl border border-indigo-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <p class="text-[9px] text-indigo-700 font-bold leading-tight">
                            Minimum 8 characters. Use letters, numbers, and symbols.
                        </p>
                    </div>

                    {{-- Submit Button with Anti-Spam --}}
                    <button type="submit" 
                            :disabled="submitting"
                            :class="{ 'submitting': submitting }"
                            class="w-full py-4 bg-indigo-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] hover:bg-indigo-700 shadow-xl shadow-indigo-100 transition-all active:scale-[0.97] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 touch-target">
                        <span x-text="submitting ? 'Creating Account...' : 'Create Account'"></span>
                        <svg x-show="submitting" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </form>

                {{-- Login Link - Reduced spacing --}}
                <div class="mt-6 pt-5 border-t border-slate-50 text-center">
                    <p class="text-[11px] text-slate-400 font-bold uppercase tracking-widest mb-3">
                        Already Have An Account?
                    </p>
                    <a href="{{ route('login') }}" class="inline-block w-full py-3 border-2 border-indigo-600 text-indigo-600 rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] hover:bg-indigo-600 hover:text-white transition-all touch-target">
                        Sign In Instead
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Password Toggle & Security Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password Toggle - Password Field
            const passwordInput = document.getElementById('register-password');
            const togglePasswordBtn = document.getElementById('toggle-register-password');
            const eyeOpen = document.getElementById('eye-open-register');
            const eyeClosed = document.getElementById('eye-closed-register');

            if (togglePasswordBtn && passwordInput) {
                togglePasswordBtn.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    eyeOpen.classList.toggle('hidden');
                    eyeClosed.classList.toggle('hidden');
                });
            }

            // Password Toggle - Confirm Field
            const confirmInput = document.getElementById('register-password-confirm');
            const toggleConfirmBtn = document.getElementById('toggle-register-confirm');
            const eyeOpenConfirm = document.getElementById('eye-open-confirm');
            const eyeClosedConfirm = document.getElementById('eye-closed-confirm');

            if (toggleConfirmBtn && confirmInput) {
                toggleConfirmBtn.addEventListener('click', function() {
                    const type = confirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    confirmInput.setAttribute('type', type);
                    eyeOpenConfirm.classList.toggle('hidden');
                    eyeClosedConfirm.classList.toggle('hidden');
                });
            }

            // 🔐 Password Strength Checker
            const passwordStrengthBar = document.getElementById('password-strength');
            
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                if (password.length >= 8) strength += 25;
                if (password.length >= 12) strength += 25;
                if (/\d/.test(password)) strength += 25;
                if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 25;
                
                passwordStrengthBar.style.width = strength + '%';
                
                if (strength < 50) {
                    passwordStrengthBar.className = 'h-full transition-all duration-300 bg-red-500';
                } else if (strength < 75) {
                    passwordStrengthBar.className = 'h-full transition-all duration-300 bg-amber-500';
                } else {
                    passwordStrengthBar.className = 'h-full transition-all duration-300 bg-emerald-500';
                }
            });

            // 🔐 Anti-Spam: Prevent form resubmission
            const form = document.getElementById('registerForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const honeypot = this.querySelector('input[name="website"]');
                    if (honeypot && honeypot.value !== '') {
                        e.preventDefault();
                        console.warn('Spam detected!');
                        return false;
                    }
                    if (this.dataset.submitted === 'true') {
                        e.preventDefault();
                        return false;
                    }
                    this.dataset.submitted = 'true';
                });
            }

            // 🔐 Prevent right-click on password fields
            const passwordFields = document.querySelectorAll('input[type="password"]');
            passwordFields.forEach(field => {
                field.addEventListener('contextmenu', function(e) {
                    e.preventDefault();
                    return false;
                });
            });
        });
    </script>
</x-guest-layout>