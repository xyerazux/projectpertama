<x-app-layout>
    {{-- Script untuk Confetti --}}
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    {{-- Custom Styles untuk Animasi & Efek Premium --}}
    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        .gradient-indigo {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a78bfa 100%);
        }
        .gradient-emerald {
            background: linear-gradient(135deg, #10b981 0%, #34d399 50%, #6ee7b7 100%);
        }
        .animate-float { animation: float 6s ease-in-out infinite; }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        .animate-pulse-slow { animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        .animate-slide-in { animation: slideIn 0.5s ease-out; }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .step-enter { transition: all 0.3s ease; }
        .step-enter:hover { transform: translateX(4px); }
        .card-hover { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .card-hover:hover { 
            transform: translateY(-4px); 
            box-shadow: 0 20px 40px -12px rgba(99, 102, 241, 0.25);
            border-color: rgba(99, 102, 241, 0.4);
        }
        .btn-press { transition: all 0.15s ease; }
        .btn-press:active { transform: scale(0.98); }
        .timeline-glow::before {
            content: '';
            position: absolute;
            left: 31px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, transparent, rgba(99, 102, 241, 0.3), transparent);
            filter: blur(1px);
        }
        .progress-bar-fill {
            transition: width 0.5s ease-out;
        }
        .priority-high { @apply text-red-600 bg-red-50 border-red-200; }
        .priority-medium { @apply text-amber-600 bg-amber-50 border-amber-200; }
        .priority-low { @apply text-emerald-600 bg-emerald-50 border-emerald-200; }
        [x-cloak] { display: none !important; }
        
        /* Custom range slider styling */
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #6366f1;
            cursor: pointer;
            border: 2px solid white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
            transition: transform 0.1s ease;
        }
        input[type="range"]::-webkit-slider-thumb:hover {
            transform: scale(1.1);
        }
        input[type="range"]::-moz-range-thumb {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #6366f1;
            cursor: pointer;
            border: 2px solid white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }
        
        /* Mobile Optimizations */
        @media (max-width: 640px) {
            .touch-target {
                min-height: 44px;
                min-width: 44px;
            }
            .text-responsive {
                font-size: 0.875rem;
            }
            .card-padding-mobile {
                padding: 1rem;
            }
        }
        
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        /* Prevent horizontal scroll */
        body {
            overflow-x: hidden;
        }
    </style>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-indigo-50/30 to-slate-100 pb-32 relative overflow-hidden">
        {{-- Decorative Background Elements --}}
        <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-200/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3 animate-pulse-slow"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-emerald-200/20 rounded-full blur-3xl translate-y-1/3 -translate-x-1/4 animate-pulse-slow" style="animation-delay: 1s"></div>

        {{-- Header Section - Responsive --}}
        <div class="glass-card border-b border-slate-200/60 sticky top-0 z-40 shadow-sm">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-10 py-4 sm:py-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <div class="relative">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 gradient-indigo rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-200/50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                </svg>
                            </div>
                            <div class="absolute -bottom-1 -right-1 w-3 h-3 sm:w-4 sm:h-4 bg-emerald-400 rounded-full border-2 border-white animate-pulse"></div>
                        </div>
                        <div>
                            <h1 class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-800 tracking-tight">
                                Project Roadmap
                            </h1>
                            <p class="text-[10px] sm:text-xs text-slate-500 font-semibold tracking-wide mt-0.5 hidden xs:block">
                                Visualize • Track • Achieve
                            </p>
                        </div>
                    </div>
                    
                    <button x-data="" x-on:click="$dispatch('open-modal', 'add-roadmap')" 
                        class="group relative gradient-indigo text-white px-5 sm:px-6 lg:px-8 py-3 sm:py-3.5 rounded-xl sm:rounded-2xl text-[10px] sm:text-xs font-black uppercase tracking-wider hover:shadow-xl hover:shadow-indigo-300/50 transition-all btn-press overflow-hidden touch-target w-full sm:w-auto">
                        <span class="relative z-10 flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="hidden sm:inline">Launch Goal</span>
                            <span class="sm:hidden">Add</span>
                        </span>
                        <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                    </button>
                </div>
            </div>
        </div>

        {{-- Main Content - Responsive --}}
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-10 mt-6 sm:mt-10">
            <div class="relative timeline-glow">
                {{-- Timeline Line - Responsive --}}
                <div class="absolute left-8 sm:left-10 top-0 bottom-0 w-0.5 bg-gradient-to-b from-indigo-200 via-slate-200 to-transparent"></div>

                <div class="space-y-12 sm:space-y-16">
                    @forelse($roadmaps as $roadmap)
                        @php
                            $totalSteps = $roadmap->steps->count();
                            $completedSteps = $roadmap->steps->where('is_completed', true)->count();
                            $percent = $totalSteps > 0 ? ($completedSteps / $totalSteps) * 100 : 0;
                            $isDone = $percent == 100;

                            $nodeStyles = [
                                ['bg' => 'bg-rose-500', 'gradient' => 'from-rose-400 to-pink-500'],
                                ['bg' => 'bg-amber-500', 'gradient' => 'from-amber-400 to-orange-500'],
                                ['bg' => 'bg-indigo-500', 'gradient' => 'from-indigo-400 to-violet-500'],
                                ['bg' => 'bg-emerald-500', 'gradient' => 'from-emerald-400 to-teal-500'],
                                ['bg' => 'bg-fuchsia-500', 'gradient' => 'from-fuchsia-400 to-purple-500'],
                                ['bg' => 'bg-sky-500', 'gradient' => 'from-sky-400 to-cyan-500'],
                            ];
                            $colorSet = $nodeStyles[$roadmap->id % count($nodeStyles)];
                        @endphp

                        <div x-data="{ 
                                open: false, 
                                progress: {{ $percent }},
                                checkCelebration() {
                                    if (this.progress === 100) {
                                        confetti({ 
                                            particleCount: 150, 
                                            spread: 70, 
                                            origin: { y: 0.6 },
                                            colors: ['#6366f1', '#8b5cf6', '#10b981']
                                        });
                                    }
                                }
                            }" 
                            x-init="checkCelebration()"
                            class="relative pl-20 sm:pl-24 group animate-slide-in"
                            style="animation-delay: {{ $loop->index * 0.1 }}s">
                            
                            {{-- Bullet Node - Responsive --}}
                            <div class="absolute left-[26px] sm:left-[34px] top-8 sm:top-10 z-20">
                                <div class="w-4 h-4 sm:w-5 sm:h-5 rounded-full border-4 border-white shadow-lg 
                                    {{ $isDone ? 'gradient-emerald' : 'bg-gradient-to-br ' . $colorSet['gradient'] }}
                                    animate-float"
                                    style="animation-delay: {{ ($roadmap->id % 5) * 0.2 }}s">
                                </div>
                                @if(!$isDone && $percent > 0)
                                    <div class="absolute inset-0 rounded-full bg-indigo-400/30 animate-ping"></div>
                                @endif
                            </div>

                            {{-- Main Card - Responsive --}}
                            <div class="glass-card rounded-2xl sm:rounded-3xl overflow-hidden card-hover">
                                
                                {{-- Card Header - Responsive --}}
                                <div class="p-4 sm:p-6 lg:p-8 cursor-pointer" @click="open = !open">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-3">
                                                <span class="px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-lg sm:rounded-xl text-[9px] sm:text-[10px] font-black uppercase tracking-wider transition-all
                                                    {{ $isDone 
                                                        ? 'gradient-emerald text-white shadow-md shadow-emerald-200/50' 
                                                        : 'bg-slate-100 text-slate-600 border border-slate-200' 
                                                    }}">
                                                    {{ str_replace('_', ' ', $roadmap->status) }}
                                                </span>
                                                @if($roadmap->target_date)
                                                    <span class="flex items-center gap-1 sm:gap-1.5 text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-wide">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-3.5 sm:w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                        <span class="hidden xs:inline">{{ \Carbon\Carbon::parse($roadmap->target_date)->format('M d, Y') }}</span>
                                                        <span class="xs:hidden">{{ \Carbon\Carbon::parse($roadmap->target_date)->format('M d') }}</span>
                                                    </span>
                                                @endif
                                            </div>
                                            <h3 class="text-lg sm:text-xl lg:text-2xl font-black text-slate-800 tracking-tight leading-tight group-hover:text-indigo-700 transition-colors line-clamp-2">
                                                {{ $roadmap->title }}
                                            </h3>
                                            @if($roadmap->description)
                                                <p class="text-xs sm:text-sm text-slate-500 mt-2 line-clamp-2 hidden sm:block">{{ $roadmap->description }}</p>
                                            @endif
                                        </div>

                                        {{-- Progress Percentage - Responsive --}}
                                        <div class="flex items-center gap-3 sm:gap-4 shrink-0">
                                            <div class="text-right">
                                                <p class="text-[8px] sm:text-[10px] font-black text-slate-400 uppercase tracking-wider hidden xs:block">Progress</p>
                                                <p class="text-2xl sm:text-3xl font-black {{ $isDone ? 'text-emerald-600' : 'text-indigo-600' }}">
                                                    {{ round($percent) }}<span class="text-lg sm:text-xl">%</span>
                                                </p>
                                            </div>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6 text-slate-400 transition-transform duration-300 group-hover:text-indigo-500" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                {{-- Expanded Section - Responsive --}}
                                <div x-show="open" x-collapse x-cloak class="px-4 sm:px-6 lg:px-8 pb-8 sm:pb-10 pt-4 sm:pt-6 bg-gradient-to-b from-slate-50/80 to-transparent border-t border-slate-100/60">
                                    
                                    {{-- Progress Bar Visual - Responsive --}}
                                    <div class="mb-6 sm:mb-8">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wide">Completion Status</span>
                                            <span class="text-xs sm:text-sm font-black {{ $isDone ? 'text-emerald-600' : 'text-indigo-600' }}" x-text="progress + '%'"></span>
                                        </div>
                                        <div class="h-2 sm:h-3 bg-slate-200 rounded-full overflow-hidden">
                                            <div class="h-full progress-bar-fill rounded-full {{ $isDone ? 'gradient-emerald' : 'gradient-indigo' }}" 
                                                :style="'width: ' + progress + '%'"></div>
                                        </div>
                                        <div class="flex justify-between mt-2 text-[10px] sm:text-xs text-slate-400">
                                            <span>{{ $completedSteps }} completed</span>
                                            <span>{{ $totalSteps }} total steps</span>
                                        </div>
                                    </div>

                                    {{-- Steps Grid - Responsive --}}
                                    <div class="mb-8 sm:mb-10">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-0 mb-4">
                                            <h4 class="text-xs sm:text-sm font-black text-slate-600 uppercase tracking-wider">Milestones</h4>
                                            <div class="flex gap-1.5 sm:gap-2 flex-wrap">
                                                <span class="px-1.5 sm:px-2 py-1 bg-red-50 text-red-600 text-[8px] sm:text-[9px] font-bold rounded-lg border border-red-100">
                                                    🔴 {{ $roadmap->steps->where('priority', 'high')->where('is_completed', false)->count() }}
                                                </span>
                                                <span class="px-1.5 sm:px-2 py-1 bg-amber-50 text-amber-600 text-[8px] sm:text-[9px] font-bold rounded-lg border border-amber-100">
                                                    🟡 {{ $roadmap->steps->where('priority', 'medium')->where('is_completed', false)->count() }}
                                                </span>
                                                <span class="px-1.5 sm:px-2 py-1 bg-emerald-50 text-emerald-600 text-[8px] sm:text-[9px] font-bold rounded-lg border border-emerald-100">
                                                    🟢 {{ $roadmap->steps->where('priority', 'low')->where('is_completed', false)->count() }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3">
                                            @foreach($roadmap->steps as $step)
                                                <div class="step-enter bg-white/80 rounded-xl sm:rounded-2xl border border-slate-100 shadow-sm hover:shadow-md overflow-hidden touch-target"
                                                     x-data="{ editMode: false, taskProgress: {{ $step->progress ?? 0 }} }">
                                                    
                                                    {{-- Task Card Content - Responsive --}}
                                                    <div class="p-3 sm:p-4">
                                                        <div class="flex items-start justify-between gap-2 sm:gap-3 mb-3">
                                                            <div class="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">
                                                                {{-- Checkbox Toggle - Responsive --}}
                                                                <form action="{{ route('roadmap.steps.toggle', $step) }}" method="POST">
                                                                    @csrf @method('PATCH')
                                                                    <button type="submit" 
                                                                        class="w-5 h-5 sm:w-6 sm:h-6 rounded-lg border-2 flex items-center justify-center transition-all touch-target
                                                                            {{ $step->is_completed 
                                                                                ? 'gradient-emerald border-transparent text-white shadow-md' 
                                                                                : 'border-slate-200 hover:border-indigo-400 hover:bg-indigo-50' 
                                                                            }}">
                                                                        @if($step->is_completed)
                                                                            <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                                            </svg>
                                                                        @endif
                                                                    </button>
                                                                </form>
                                                                
                                                                {{-- Title - Responsive --}}
                                                                <span class="text-xs sm:text-sm font-bold {{ $step->is_completed ? 'text-slate-300 line-through' : 'text-slate-700' }} tracking-tight truncate">
                                                                    {{ $step->title }}
                                                                </span>
                                                            </div>
                                                            
                                                            {{-- Priority Badge - Responsive --}}
                                                            <span class="px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-lg text-[8px] sm:text-[9px] font-black uppercase tracking-wider whitespace-nowrap border
                                                                {{ $step->is_completed 
                                                                    ? 'bg-slate-100 text-slate-400 border-slate-200' 
                                                                    : ($step->priority == 'high' ? 'priority-high' : ($step->priority == 'low' ? 'priority-low' : 'priority-medium')) 
                                                                }}">
                                                                <span class="hidden sm:inline">{{ $step->priority ?? 'medium' }}</span>
                                                                <span class="sm:hidden">
                                                                    @if($step->priority == 'high') 🔴
                                                                    @elseif($step->priority == 'low') 🟢
                                                                    @else 🟡
                                                                    @endif
                                                                </span>
                                                            </span>
                                                        </div>
                                                        
                                                        {{-- Category & Due Date - Responsive --}}
                                                        <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-3">
                                                            @if($step->category)
                                                                <span class="flex items-center gap-1 px-1.5 sm:px-2 py-1 rounded-lg text-[9px] sm:text-[10px] font-bold border {{ $step->category_color ?? 'text-slate-600 bg-slate-50 border-slate-200' }}">
                                                                    <span>{{ $step->category_icon }}</span>
                                                                    <span class="hidden xs:inline">{{ ucfirst($step->category) }}</span>
                                                                </span>
                                                            @endif
                                                            @if($step->due_date)
                                                                <span class="flex items-center gap-1 text-[9px] sm:text-[10px] font-bold {{ $step->due_date->isPast() && !$step->is_completed ? 'text-red-500' : 'text-slate-400' }}">
                                                                    <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                                    </svg>
                                                                    <span class="hidden xs:inline">{{ \Carbon\Carbon::parse($step->due_date)->format('M d') }}</span>
                                                                    <span class="xs:hidden">{{ \Carbon\Carbon::parse($step->due_date)->format('M/d') }}</span>
                                                                </span>
                                                            @endif
                                                        </div>
                                                        
                                                        {{-- Progress Bar per Task - Responsive --}}
                                                        <div class="mb-2">
                                                            <div class="flex justify-between text-[8px] sm:text-[9px] font-bold text-slate-400 mb-1">
                                                                <span>Progress</span>
                                                                <span>{{ $step->progress ?? 0 }}%</span>
                                                            </div>
                                                            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                                                <div class="h-full {{ ($step->progress ?? 0) == 100 ? 'gradient-emerald' : 'gradient-indigo' }} rounded-full" 
                                                                    style="width: {{ $step->progress ?? 0 }}%"></div>
                                                            </div>
                                                        </div>
                                                        
                                                        {{-- Description - Responsive --}}
                                                        @if($step->description)
                                                            <p class="text-[10px] sm:text-xs text-slate-500 line-clamp-2 mb-3 hidden sm:block">{{ $step->description }}</p>
                                                        @endif
                                                        
                                                        {{-- Actions - Responsive --}}
                                                        <div class="flex items-center justify-between pt-2 sm:pt-3 border-t border-slate-50">
                                                            <button @click="editMode = !editMode" class="text-[8px] sm:text-[9px] font-bold text-indigo-500 hover:text-indigo-700 uppercase tracking-wider touch-target">
                                                                Edit
                                                            </button>
                                                            {{-- ✅ FIX: Delete hanya task, bukan roadmap --}}
                                                            <form action="{{ route('roadmap.steps.destroy', $step) }}" method="POST" class="inline" onsubmit="return confirm('Delete this task?')">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="text-[8px] sm:text-[9px] font-bold text-red-400 hover:text-red-600 uppercase tracking-wider touch-target">
                                                                    Delete
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                    
                                                    {{-- Edit Form - Responsive --}}
                                                    <div x-show="editMode" x-collapse class="px-3 sm:px-4 pb-3 sm:pb-4 bg-slate-50/50 border-t border-slate-100">
                                                        <form action="{{ route('roadmap.steps.update', $step) }}" method="POST" class="space-y-2.5 sm:space-y-3">
                                                            @csrf @method('PATCH')
                                                            
                                                            <input type="text" name="title" value="{{ $step->title }}" 
                                                                class="w-full bg-white border border-slate-200 rounded-lg sm:rounded-xl py-2 sm:py-2.5 px-2.5 sm:px-3 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-indigo-100 focus:border-indigo-300 outline-none touch-target">
                                                            
                                                            <div class="grid grid-cols-2 gap-2">
                                                                <select name="priority" class="bg-white border border-slate-200 rounded-lg sm:rounded-xl py-2 sm:py-2.5 px-2.5 sm:px-3 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-indigo-100 outline-none touch-target">
                                                                    <option value="high" {{ ($step->priority ?? 'medium') == 'high' ? 'selected' : '' }}>🔴 High</option>
                                                                    <option value="medium" {{ ($step->priority ?? 'medium') == 'medium' ? 'selected' : '' }}>🟡 Medium</option>
                                                                    <option value="low" {{ ($step->priority ?? 'medium') == 'low' ? 'selected' : '' }}>🟢 Low</option>
                                                                </select>
                                                                
                                                                <input type="text" name="category" value="{{ $step->category ?? '' }}" placeholder="📌 Category"
                                                                    class="bg-white border border-slate-200 rounded-lg sm:rounded-xl py-2 sm:py-2.5 px-2.5 sm:px-3 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-indigo-100 outline-none touch-target">
                                                            </div>
                                                            
                                                            <input type="date" name="due_date" value="{{ $step->due_date ? \Carbon\Carbon::parse($step->due_date)->format('Y-m-d') : '' }}" 
                                                                class="w-full bg-white border border-slate-200 rounded-lg sm:rounded-xl py-2 sm:py-2.5 px-2.5 sm:px-3 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-indigo-100 outline-none touch-target">
                                                            
                                                            <textarea name="description" rows="2" placeholder="📝 Description..."
                                                                class="w-full bg-white border border-slate-200 rounded-lg sm:rounded-xl py-2 sm:py-2.5 px-2.5 sm:px-3 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-indigo-100 outline-none resize-none touch-target">{{ $step->description ?? '' }}</textarea>
                                                            
                                                            {{-- Real-time Progress Slider - Responsive --}}
                                                            <div>
                                                                <label class="text-[8px] sm:text-[9px] font-bold text-slate-400 uppercase flex items-center justify-between">
                                                                    <span>Progress</span>
                                                                    <span class="text-indigo-600 font-black" x-text="taskProgress + '%'"></span>
                                                                </label>
                                                                <input type="range" name="progress" min="0" max="100" 
                                                                    x-model="taskProgress"
                                                                    value="{{ $step->progress ?? 0 }}" 
                                                                    @input="taskProgress = $event.target.value"
                                                                    class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-indigo-500 mt-2 touch-target">
                                                                <div class="flex justify-between text-[7px] sm:text-[8px] text-slate-300 mt-1">
                                                                    <span>0%</span>
                                                                    <span>50%</span>
                                                                    <span>100%</span>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="flex gap-2 pt-2">
                                                                <button type="submit" class="flex-1 gradient-indigo text-white py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-[8px] sm:text-[9px] font-black uppercase tracking-wider btn-press touch-target">Save</button>
                                                                <button type="button" @click="editMode = false" class="flex-1 bg-slate-200 text-slate-600 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-[8px] sm:text-[9px] font-black uppercase tracking-wider btn-press touch-target">Cancel</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Action Bar - Add Task - Responsive --}}
                                    <div class="pt-6 sm:pt-8 border-t border-slate-100/60">
                                        <h4 class="text-xs sm:text-sm font-black text-slate-600 uppercase tracking-wider mb-4">Add New Task</h4>
                                        
                                        <div x-data="{ showAdvanced: false }">
                                            {{-- Basic Form - Responsive --}}
                                            <form action="{{ route('roadmap.steps.store', $roadmap) }}" method="POST" class="space-y-2.5 sm:space-y-3">
                                                @csrf
                                                
                                                <div class="flex gap-2 sm:gap-3">
                                                    <input type="text" name="title" placeholder="✨ Task title..." required
                                                        class="flex-1 bg-white/80 border-2 border-slate-100 rounded-xl sm:rounded-2xl py-3 sm:py-4 px-3 sm:px-6 text-xs sm:text-sm font-semibold focus:ring-4 focus:ring-indigo-100 focus:border-indigo-300 outline-none backdrop-blur-sm touch-target">
                                                    <button type="submit" class="gradient-indigo text-white px-4 sm:px-6 rounded-xl sm:rounded-2xl text-[9px] sm:text-[10px] font-black uppercase tracking-wider btn-press whitespace-nowrap touch-target">
                                                        <span class="hidden sm:inline">Add</span>
                                                        <span class="sm:hidden">+</span>
                                                    </button>
                                                </div>
                                                
                                                {{-- Advanced Options Toggle - Responsive --}}
                                                <button type="button" @click="showAdvanced = !showAdvanced" 
                                                    class="text-[10px] sm:text-xs font-bold text-indigo-500 hover:text-indigo-700 flex items-center gap-1 touch-target">
                                                    <span x-text="showAdvanced ? 'Hide' : 'Show'"></span> <span class="hidden xs:inline">advanced options</span>
                                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 transition-transform" :class="showAdvanced ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </button>
                                                
                                                {{-- Advanced Fields - Responsive --}}
                                                <div x-show="showAdvanced" x-collapse class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-3 pt-3">
                                                    <select name="priority" class="bg-white/80 border border-slate-200 rounded-xl py-2.5 sm:py-3 px-3 sm:px-4 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-indigo-100 outline-none touch-target">
                                                        <option value="medium">🟡 Priority: Medium</option>
                                                        <option value="high">🔴 Priority: High</option>
                                                        <option value="low">🟢 Priority: Low</option>
                                                    </select>
                                                    
                                                    <input type="text" name="category" placeholder="📌 Category" list="categories"
                                                        class="bg-white/80 border border-slate-200 rounded-xl py-2.5 sm:py-3 px-3 sm:px-4 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-indigo-100 outline-none touch-target">
                                                    <datalist id="categories">
                                                        <option value="Design">
                                                        <option value="Development">
                                                        <option value="Testing">
                                                        <option value="Bug Fix">
                                                        <option value="Documentation">
                                                        <option value="Deployment">
                                                        <option value="Marketing">
                                                        <option value="Research">
                                                        <option value="Meeting">
                                                        <option value="Security">
                                                        <option value="Performance">
                                                        <option value="Maintenance">
                                                        <option value="Feature Request">
                                                        <option value="Infrastructure">
                                                    </datalist>
                                                    
                                                    <input type="date" name="due_date" 
                                                        class="bg-white/80 border border-slate-200 rounded-xl py-2.5 sm:py-3 px-3 sm:px-4 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-indigo-100 outline-none touch-target">
                                                </div>
                                                
                                                <div x-show="showAdvanced" x-collapse>
                                                    <textarea name="description" placeholder="📝 Task description (optional)..." rows="2"
                                                        class="w-full bg-white/80 border border-slate-200 rounded-xl py-2.5 sm:py-3 px-3 sm:px-4 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-indigo-100 outline-none resize-none touch-target"></textarea>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    {{-- Delete Roadmap Button - Responsive --}}
                                    <div class="pt-4 sm:pt-6 mt-4 sm:mt-6 border-t border-slate-100/60 flex justify-end">
                                        <form action="{{ route('roadmap.destroy', $roadmap) }}" method="POST" onsubmit="return confirm('⚠️ Are you sure you want to delete this ENTIRE roadmap and all its tasks?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="flex items-center gap-2 px-4 sm:px-5 py-2 sm:py-2.5 bg-red-50 text-red-500 rounded-lg sm:rounded-xl hover:bg-red-500 hover:text-white transition-all border border-red-100 btn-press text-[10px] sm:text-xs font-bold touch-target w-full sm:w-auto justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <span class="hidden xs:inline">Delete Roadmap</span>
                                                <span class="xs:hidden">Delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        {{-- Empty State - Responsive --}}
                        <div class="text-center py-16 sm:py-20 lg:py-28 glass-card rounded-2xl sm:rounded-3xl border-2 border-dashed border-slate-200/60 relative overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-br from-indigo-50/30 to-transparent"></div>
                            <div class="relative z-10 px-4">
                                <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-4 sm:mb-6 gradient-indigo rounded-2xl sm:rounded-3xl flex items-center justify-center shadow-xl shadow-indigo-200/40 animate-float">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 sm:h-10 sm:w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg sm:text-xl font-black text-slate-700 mb-2">No roadmaps yet</h3>
                                <p class="text-xs sm:text-sm text-slate-400 mb-4 sm:mb-6 max-w-xs mx-auto">Start your journey by launching your first goal. Every achievement begins with a single step.</p>
                                <button x-data="" x-on:click="$dispatch('open-modal', 'add-roadmap')" 
                                    class="gradient-indigo text-white px-6 sm:px-8 py-3 sm:py-3.5 rounded-xl sm:rounded-2xl text-[10px] sm:text-xs font-black uppercase tracking-wider hover:shadow-xl hover:shadow-indigo-300/50 transition-all btn-press inline-flex items-center gap-2 touch-target w-full sm:w-auto justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Launch Your First Goal
                                </button>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Mobile Bottom Stats Bar --}}
        <div class="fixed bottom-0 left-0 right-0 z-30 lg:hidden">
            <div class="glass-card border-t border-slate-200/60 px-4 py-3 safe-area-bottom">
                <div class="flex items-center justify-between">
                    <div class="text-center flex-1">
                        <div class="text-base sm:text-lg font-black text-indigo-600">{{ $roadmaps->count() }}</div>
                        <div class="text-[8px] sm:text-[9px] text-slate-400 font-bold uppercase">Goals</div>
                    </div>
                    <div class="w-px h-6 sm:h-8 bg-slate-100"></div>
                    <div class="text-center flex-1">
                        <div class="text-base sm:text-lg font-black text-emerald-600">
                            {{ $roadmaps->sum(fn($r) => $r->steps->where('is_completed', true)->count()) }}
                        </div>
                        <div class="text-[8px] sm:text-[9px] text-slate-400 font-bold uppercase">Done</div>
                    </div>
                    <div class="w-px h-6 sm:h-8 bg-slate-100"></div>
                    <div class="text-center flex-1">
                        <div class="text-base sm:text-lg font-black text-amber-600">
                            {{ $roadmaps->sum(fn($r) => $r->steps->where('is_completed', false)->count()) }}
                        </div>
                        <div class="text-[8px] sm:text-[9px] text-slate-400 font-bold uppercase">Pending</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Add Roadmap - Responsive --}}
    <x-modal name="add-roadmap" focusable>
        <div class="p-0 overflow-hidden rounded-2xl sm:rounded-3xl shadow-2xl shadow-slate-300/50 max-h-[90vh] overflow-y-auto">
            {{-- Modal Header - Responsive --}}
            <div class="gradient-indigo p-6 sm:p-8 lg:p-10 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 sm:w-40 h-32 sm:h-40 bg-white/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/3"></div>
                <div class="absolute bottom-0 left-0 w-24 sm:w-32 h-24 sm:h-32 bg-white/10 rounded-full blur-xl translate-y-1/3 -translate-x-1/4"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center gap-2.5 sm:gap-3 mb-3 sm:mb-4">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black tracking-tight">Launch New Goal</h2>
                    </div>
                    <p class="text-[10px] sm:text-[11px] text-indigo-100 font-bold uppercase tracking-[0.25em]">Define your next milestone</p>
                </div>
            </div>
            
            <form method="post" action="{{ route('roadmap.store') }}" class="p-5 sm:p-8 lg:p-10 bg-white">
                @csrf
                <div class="space-y-5 sm:space-y-7">
                    <div>
                        <label class="block text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 sm:mb-3 ml-1">Goal Title</label>
                        <input type="text" name="title" 
                            class="w-full bg-slate-50/80 border-2 border-slate-100 rounded-xl sm:rounded-2xl py-3 sm:py-4 px-4 sm:px-6 font-semibold text-sm sm:text-base text-slate-700 placeholder-slate-400 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-300 transition-all outline-none backdrop-blur-sm touch-target" 
                            placeholder="e.g., Launch E-Commerce Platform" required />
                    </div>

                    <div>
                        <label class="block text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 sm:mb-3 ml-1">Description (Optional)</label>
                        <textarea name="description" rows="3"
                            class="w-full bg-slate-50/80 border-2 border-slate-100 rounded-xl sm:rounded-2xl py-3 sm:py-4 px-4 sm:px-6 font-semibold text-sm sm:text-base text-slate-700 placeholder-slate-400 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-300 transition-all outline-none backdrop-blur-sm resize-none touch-target" 
                            placeholder="Brief description of your goal..."></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 sm:mb-3 ml-1">Initial Status</label>
                            <select name="status" class="w-full bg-slate-50/80 border-2 border-slate-100 rounded-xl sm:rounded-2xl py-3 sm:py-4 px-4 sm:px-6 text-sm sm:text-base font-semibold text-slate-700 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-300 outline-none backdrop-blur-sm appearance-none cursor-pointer touch-target">
                                <option value="planned">📋 Planned</option>
                                <option value="in_progress">🚀 In Progress</option>
                                <option value="on_hold">⏸️ On Hold</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 sm:mb-3 ml-1">Target Date</label>
                            <input type="date" name="target_date" 
                                class="w-full bg-slate-50/80 border-2 border-slate-100 rounded-xl sm:rounded-2xl py-3 sm:py-4 px-4 sm:px-6 text-sm sm:text-base font-semibold text-slate-700 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-300 outline-none backdrop-blur-sm cursor-pointer touch-target" />
                        </div>
                    </div>
                </div>

                <div class="mt-8 sm:mt-10 flex flex-col-reverse sm:flex-row justify-end items-center gap-3 sm:gap-4 pt-4 sm:pt-6 border-t border-slate-100">
                    <button type="button" x-on:click="$dispatch('close')" 
                        class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-red-500 transition-colors px-5 sm:px-6 py-2.5 sm:py-3 rounded-xl hover:bg-red-50 touch-target w-full sm:w-auto">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="w-full sm:w-auto bg-slate-900 text-white px-8 sm:px-10 py-3 sm:py-4 rounded-xl sm:rounded-2xl text-[9px] sm:text-[10px] font-black uppercase tracking-widest hover:gradient-indigo hover:shadow-xl hover:shadow-indigo-200/50 transition-all shadow-lg shadow-slate-200/50 btn-press touch-target">
                        🚀 Launch Journey
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- Toast Notification - Responsive --}}
    @if(session('success'))
    <div x-data="{ show: true }" 
         x-show="show" 
         x-init="setTimeout(() => show = false, 4000)"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-20 sm:bottom-6 right-4 sm:right-6 z-50 max-w-[calc(100%-2rem)]">
        <div class="bg-slate-900 text-white px-4 sm:px-6 py-3 sm:py-4 rounded-xl sm:rounded-2xl shadow-2xl flex items-center gap-2.5 sm:gap-3">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-xs sm:text-sm font-semibold">{{ session('success') }}</span>
            <button @click="show = false" class="ml-2 text-slate-400 hover:text-white shrink-0 touch-target">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    @endif
</x-app-layout>