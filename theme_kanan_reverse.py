import sys
import re

file_path = r'resources\views\mahasiswa\dashboard\progress\kanan\index.blade.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

replacements = {
    r'\bbg-slate-900/60 backdrop-blur-xl\b': 'bg-white',
    r'\bborder border-slate-700/50 shadow-\[0_0_30px_rgba\(0,0,0,0\.5\)\]\b': 'tech-border',
    r'\btext-white\b': 'text-gray-800',  
    r'\btext-slate-100\b': 'text-slate-800',
    r'\btext-slate-200\b': 'text-slate-700',
    r'\btext-slate-400\b': 'text-gray-500',
    r'\btext-slate-300\b': 'text-gray-600',
    r'\bbg-slate-800/50\b': 'bg-slate-50',
    r'\bbg-slate-800/30\b': 'bg-slate-50/50',
    r'\bbg-slate-800/80\b': 'bg-slate-100',
    r'\bbg-blue-900/30\b': 'bg-blue-50',
    r'\btext-blue-400\b': 'text-blue-600',
    r'\btext-blue-300\b': 'text-blue-700',
    r'\bborder-blue-700/50\b': 'border-blue-200',
    r'\bborder-slate-700\b': 'border-slate-200',
    r'\bborder-slate-700/50\b': 'border-slate-100',
    r'\bbg-emerald-900/30\b': 'bg-emerald-50',
    r'\bbg-amber-900/30\b': 'bg-amber-50',
    r'\bbg-amber-900/50\b': 'bg-amber-100',
    r'\btext-emerald-400\b': 'text-emerald-600',
    r'\btext-amber-400\b': 'text-amber-600',
    r'\btext-amber-300\b': 'text-amber-700',
    r'\btext-amber-200\b': 'text-amber-800',
    r'\bborder-emerald-700/50\b': 'border-emerald-100',
    r'\bborder-amber-700/50\b': 'border-amber-100',
    r'\bborder-amber-700/80\b': 'border-amber-200',
    r'\bbg-rose-900/30\b': 'bg-rose-50',
    r'\btext-rose-400\b': 'text-rose-700',
    r'\bborder-rose-700/50\b': 'border-rose-200',
    r'\bbg-red-900/30\b': 'bg-red-50',
    r'\btext-red-400\b': 'text-red-700',
    r'\btext-red-500\b': 'text-red-600',
    r'\bborder-red-700/50\b': 'border-red-200',
    r'\bborder-red-700/80\b': 'border-red-300',
    r'\bbg-yellow-900/30\b': 'bg-yellow-50',
    r'\bbg-green-900/30\b': 'bg-green-50',
    r'\bbg-green-900/50\b': 'bg-green-100',
    r'\btext-green-400\b': 'text-green-700',
    r'\btext-green-300\b': 'text-green-800',
    r'\bborder-green-700/50\b': 'border-green-200',
    r'\bbg-gradient-to-r from-blue-950/80 to-indigo-950/80 backdrop-blur-xl border border-blue-500/30 shadow-\[0_0_40px_rgba\(59,130,246,0\.3\)\]\b': 'bg-gradient-to-r from-blue-600 to-indigo-600',
    r'\bbg-gradient-to-r from-emerald-950/80 to-green-950/80 backdrop-blur-xl border border-emerald-500/30 shadow-\[0_0_40px_rgba\(16,185,129,0\.3\)\]\b': 'bg-gradient-to-r from-green-500 to-emerald-600',
}

for old, new in replacements.items():
    content = re.sub(old, new, content)

# Reverse tech grid css modification
content = content.replace(
    'linear-gradient(to right, rgba(255,255,255,0.05) 1px, transparent 1px)',
    'linear-gradient(to right, rgba(0,0,0,0.05) 1px, transparent 1px)'
).replace(
    'linear-gradient(to bottom, rgba(255,255,255,0.05) 1px, transparent 1px)',
    'linear-gradient(to bottom, rgba(0,0,0,0.05) 1px, transparent 1px)'
).replace(
    'border: 1px solid rgba(255,255,255,0.1);\n        box-shadow: 0 0 20px rgba(0,0,0,0.5);',
    'border: 1px solid #e2e8f0;\n        box-shadow: 0 0 0 1px rgba(255,255,255,0.5) inset;'
)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print('Redesign reversed!')
