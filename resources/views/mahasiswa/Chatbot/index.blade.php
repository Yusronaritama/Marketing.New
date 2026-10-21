@extends('layouts.mahasiswa.app')

@section('content')
<div class="relative w-full max-w-[1400px] mx-auto min-h-[90vh] bg-slate-900 rounded-[2.5rem] p-4 lg:p-8 shadow-2xl flex flex-col justify-center items-center group mb-10 mt-6">
    
    <!-- Immersive Background for Focus Mode -->
    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 via-purple-500/20 to-blue-500/20 mix-blend-overlay opacity-80 rounded-[2.5rem]"></div>
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-400/30 rounded-full blur-[100px] pointer-events-none animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-400/30 rounded-full blur-[100px] pointer-events-none animate-pulse" style="animation-delay: 2s;"></div>

    <!-- The Glass Window (App Interface) -->
    <div class="relative z-10 w-full min-h-[85vh] bg-white/75 backdrop-blur-2xl rounded-3xl border border-white/60 shadow-[0_8px_32px_rgba(0,0,0,0.15)] flex flex-col transition-all duration-500">
        
        <!-- MacOS-style Title Bar -->
        <div class="h-12 bg-white/40 border-b border-white/50 flex items-center px-5 gap-2.5 shrink-0 relative rounded-t-3xl">
            <!-- Window Controls -->
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-full bg-red-400 shadow-inner"></div>
                <div class="w-3 h-3 rounded-full bg-amber-400 shadow-inner"></div>
                <div class="w-3 h-3 rounded-full bg-green-400 shadow-inner"></div>
            </div>
            
            <!-- Centered Title -->
            <div class="absolute left-1/2 transform -translate-x-1/2 flex items-center gap-2 text-slate-500">
                <i class="fas fa-robot text-blue-500 text-xs"></i>
                <span class="text-[11px] font-bold uppercase tracking-[0.2em]">AI Admission Assistant</span>
            </div>
            
            <!-- Right Actions -->
            <div class="ml-auto flex items-center gap-3">
                <span class="text-[10px] font-bold text-slate-400 bg-white/50 px-2 py-1 rounded-md border border-white/60 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> ONLINE
                </span>
            </div>
        </div>

        <!-- Vue Component Mount Point -->
        <!-- We pass user data if needed via data attributes -->
        <div id="vue-chatbot-app" class="flex-1 flex flex-col"
             data-user-name="{{ Auth::user()->name ?? 'Calon Mahasiswa' }}">
        </div>

    </div>
</div>
@endsection
