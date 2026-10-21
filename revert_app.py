import sys
import re

file_path = r'resources\views\layouts\mahasiswa\app.blade.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Reverse app.blade.php
content = re.sub(r'<body class="bg-slate-950 font-sans antialiased text-slate-200">', '<body class="bg-slate-50 font-sans antialiased text-slate-800">', content)
content = re.sub(
    r'<!-- Space Background layer -->\s*<div class="fixed inset-0 -z-10 w-full h-full bg-slate-950">\s*<div class="absolute inset-0 bg-gradient-to-b from-indigo-950/40 via-slate-950 to-black pointer-events-none"></div>\s*<div class="absolute inset-0 opacity-40 pointer-events-none" style="background-image: radial-gradient\(circle at center, white 1px, transparent 1px\); background-size: 40px 40px; background-position: 0 0, 20px 20px;"></div>\s*</div>',
    r'<div class="fixed inset-0 -z-10 w-full h-full bg-slate-50"></div>',
    content
)
content = re.sub(
    r'<nav class="sticky top-0 z-50 w-full bg-slate-950/80 backdrop-blur-xl border-b border-slate-800 shadow-lg"',
    r'<nav class="sticky top-0 z-50 w-full bg-white border-b border-slate-200 shadow-sm"',
    content
)
content = re.sub(
    r":class=\"{ 'shadow-xl bg-slate-950/95': scrolled }\"",
    r":class=\"{ 'shadow-md': scrolled }\"",
    content
)
content = content.replace(
    'class="w-full flex items-center justify-center gap-2 bg-red-950/50 hover:bg-red-900/50 text-red-400 font-bold py-3 rounded-xl transition-all text-sm border border-red-900/50"',
    'class="w-full flex items-center justify-center gap-2 bg-red-50 hover:bg-red-100 text-red-600 font-bold py-3 rounded-xl transition-all text-sm border border-red-100"'
)
content = content.replace(
    'class="w-full py-3 text-sm font-semibold text-slate-400 hover:text-slate-200 rounded-xl hover:bg-slate-800 transition-all"',
    'class="w-full py-3 text-sm font-semibold text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-50 transition-all"'
)

# Reverse Mobile Nav
content = content.replace(
    '<div class="md:hidden fixed bottom-0 left-0 right-0 z-[80] bg-slate-950/90 backdrop-blur-xl border-t border-slate-800 shadow-[0_-2px_20px_rgba(0,0,0,0.5)] pb-[env(safe-area-inset-bottom)]">',
    '<div class="md:hidden fixed bottom-0 left-0 right-0 z-[80] bg-white border-t border-slate-200 shadow-[0_-2px_12px_rgba(0,0,0,0.06)] pb-[env(safe-area-inset-bottom)]">'
)
content = content.replace(
    "? 'bg-blue-600 text-white shadow-[0_0_15px_rgba(37,99,235,0.5)]'\n                    : 'text-slate-400 hover:text-slate-200'",
    "? 'bg-blue-700 text-white shadow-md shadow-blue-200'\n                    : 'text-slate-400 hover:text-slate-600'"
)
content = content.replace(
    '<div class="w-7 h-7 rounded-full bg-slate-800 border border-slate-700 overflow-hidden flex items-center justify-center">',
    '<div class="w-7 h-7 rounded-full bg-blue-50 border border-blue-200 overflow-hidden flex items-center justify-center">'
)
content = content.replace(
    '<span class="text-[10px] font-bold text-blue-400">{{ $initials }}</span>',
    '<span class="text-[10px] font-bold text-blue-700">{{ $initials }}</span>'
)


with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Reverted app.blade.php")
