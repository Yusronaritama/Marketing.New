import sys
import re

file_path = r'resources\views\mahasiswa\dashboard\progress\kiri\index.blade.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Remove the 2-column wrapper
content = content.replace(
    "{{-- WRAPPER 2-KOLOM UNTUK INFO DAN ORBITAL --}}\n<div class=\"flex flex-col xl:flex-row gap-6 xl:gap-8 w-full\">\n\n    {{-- KOLOM TENGAH (ORBITAL STEPPER) - Tampil di Kanan Info pada Desktop --}}\n    <div class=\"w-full xl:w-7/12 order-1 xl:order-2\">\n        {{-- MAIN CONTAINER --}}",
    "        {{-- MAIN CONTAINER --}}"
)
content = content.replace(
    "    </div>\n        </div>\n    </div>\n\n    {{-- KOLOM KIRI (INFO PANELS) - Tampil di Kiri Orbital pada Desktop --}}\n    <div class=\"w-full xl:w-5/12 flex flex-col gap-6 order-2 xl:order-1 mt-6 xl:mt-0\">",
    "        </div>\n    </div>\n\n    {{-- INFO PANELS --}}\n    <div class=\"w-full flex flex-col gap-6 mt-6\">"
)
content = content.replace(
    "    </div> {{-- End Kolom Kiri (Info Panels) --}}\n</div> {{-- End Wrapper 2-Kolom --}}",
    "    </div> {{-- End Info Panels --}}"
)

# 2. Revert Orbital Stepper container
content = content.replace(
    'class="w-full max-w-5xl mx-auto bg-blue-600/10 backdrop-blur-xl border border-blue-200/30 rounded-3xl shadow-xl shadow-blue-500/10 p-6 lg:p-8 relative overflow-hidden group transition-all duration-700 ease-out transform opacity-0 scale-95"',
    'class="w-full max-w-5xl mx-auto bg-white rounded-3xl shadow-lg border border-slate-100 p-6 lg:p-8 relative overflow-hidden group transition-all duration-700 ease-out transform opacity-0 scale-95"'
)

# 3. Revert Status Kelulusan Theme
content = content.replace(
    "{{ $rankTheme == 'emerald' \n                    ? 'bg-gradient-to-br from-blue-950 via-blue-900 to-blue-800 border-yellow-400 shadow-blue-900/50' \n                    : ($rankTheme == 'red' ? 'bg-red-900 border-red-700' : 'bg-slate-900 border-slate-800') }}",
    "{{ $rankTheme == 'emerald' \n                    ? 'bg-gradient-to-br from-blue-600 via-blue-700 to-blue-800 border-blue-500 shadow-blue-200' \n                    : ($rankTheme == 'red' ? 'bg-red-50 border-red-200' : 'bg-slate-50 border-slate-200') }}"
)
content = content.replace(
    "{{ $rankTheme == 'emerald' ? 'bg-red-700 text-white shadow-red-900/50' : 'bg-slate-800 text-slate-500' }}",
    "{{ $rankTheme == 'emerald' ? 'bg-white text-blue-600 shadow-blue-900/20' : 'bg-slate-200 text-slate-500' }}"
)
content = content.replace(
    "{{ $rankTheme == 'emerald' ? 'text-yellow-400 drop-shadow-sm' : 'text-slate-500' }}",
    "{{ $rankTheme == 'emerald' ? 'text-blue-100 drop-shadow-sm' : 'text-slate-500' }}"
)
content = content.replace(
    "text-3xl font-black italic uppercase tracking-tighter text-white",
    "text-3xl font-black italic uppercase tracking-tighter {{ $rankTheme == 'emerald' ? 'text-white' : 'text-slate-700' }}"
)
content = content.replace(
    "bg-black/20 backdrop-blur-sm rounded-xl p-4 border border-white/10",
    "bg-white/20 backdrop-blur-sm rounded-xl p-4 border border-white/30"
)

# 4. Revert Info Panels (Amber & Blue)
content = content.replace(
    'class="relative overflow-hidden rounded-xl border border-amber-500/30 bg-slate-900/50 backdrop-blur-xl p-1 group shadow-[0_8px_30px_rgba(0,0,0,0.5)]"',
    'class="relative overflow-hidden rounded-xl border border-amber-200 bg-amber-50 p-1 group shadow-sm"'
)
content = content.replace(
    'bg-amber-950/50 border border-amber-500/30 flex items-center justify-center shadow-inner shadow-amber-500/20',
    'bg-amber-100 border border-amber-200 flex items-center justify-center shadow-inner shadow-amber-500/10'
)
content = content.replace('text-amber-300 font-bold', 'text-amber-700 font-bold')
content = content.replace('bg-amber-500/20 text-amber-200 border border-amber-500/30', 'bg-amber-100 text-amber-700 border border-amber-200')
content = content.replace('text-slate-300 text-xs', 'text-slate-600 text-xs')
content = content.replace('text-white font-extrabold border-b border-dashed border-slate-500', 'text-slate-800 font-extrabold border-b border-dashed border-slate-400')
content = content.replace('text-amber-400 font-bold drop-shadow-[0_0_2px_currentColor]', 'text-amber-600 font-bold')
content = content.replace('text-emerald-400 font-bold drop-shadow-[0_0_2px_currentColor]', 'text-emerald-600 font-bold')

content = content.replace(
    'class="relative overflow-hidden rounded-xl border border-blue-500/30 bg-slate-900/50 backdrop-blur-xl p-1 group shadow-[0_8px_30px_rgba(0,0,0,0.5)]"',
    'class="relative overflow-hidden rounded-xl border border-blue-200 bg-blue-50 p-1 group shadow-sm"'
)
content = content.replace(
    'bg-blue-950/50 border border-blue-500/30 flex items-center justify-center shadow-inner shadow-blue-500/20',
    'bg-blue-100 border border-blue-200 flex items-center justify-center shadow-inner shadow-blue-500/10'
)
content = content.replace('text-blue-300 font-bold', 'text-blue-700 font-bold')
content = content.replace('bg-blue-500/20 text-blue-200 border border-blue-500/30', 'bg-blue-100 text-blue-700 border border-blue-200')
content = content.replace('text-blue-300 font-extrabold border-b border-dashed border-blue-500/50', 'text-blue-600 font-extrabold border-b border-dashed border-blue-300')

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print('Reverted kiri/index.blade.php')
