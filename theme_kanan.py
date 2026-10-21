import sys
import re

file_path = r'resources\views\mahasiswa\dashboard\progress\kanan\index.blade.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

replacements = {
    r'\bbg-white\b': 'bg-slate-900/60 backdrop-blur-xl',
    r'\btech-border\b': 'border border-slate-700/50 shadow-[0_0_30px_rgba(0,0,0,0.5)]',
    r'\btext-gray-800\b': 'text-white',
    r'\btext-gray-900\b': 'text-white',
    r'\btext-slate-800\b': 'text-slate-100',
    r'\btext-slate-700\b': 'text-slate-200',
    r'\btext-gray-500\b': 'text-slate-400',
    r'\btext-gray-600\b': 'text-slate-300',
    r'\bbg-slate-50\b': 'bg-slate-800/50',
    r'\bbg-slate-50/50\b': 'bg-slate-800/30',
    r'\bbg-slate-100\b': 'bg-slate-800/80',
    r'\bbg-blue-50\b': 'bg-blue-900/30',
    r'\btext-blue-600\b': 'text-blue-400',
    r'\btext-blue-700\b': 'text-blue-300',
    r'\bborder-blue-200\b': 'border-blue-700/50',
    r'\bborder-slate-200\b': 'border-slate-700',
    r'\bborder-slate-100\b': 'border-slate-700/50',
    r'\bbg-emerald-50\b': 'bg-emerald-900/30',
    r'\bbg-amber-50\b': 'bg-amber-900/30',
    r'\bbg-amber-100\b': 'bg-amber-900/50',
    r'\btext-emerald-600\b': 'text-emerald-400',
    r'\btext-amber-600\b': 'text-amber-400',
    r'\btext-amber-700\b': 'text-amber-300',
    r'\btext-amber-800\b': 'text-amber-200',
    r'\bborder-emerald-100\b': 'border-emerald-700/50',
    r'\bborder-amber-100\b': 'border-amber-700/50',
    r'\bborder-amber-200\b': 'border-amber-700/80',
    r'\bbg-rose-50\b': 'bg-rose-900/30',
    r'\btext-rose-700\b': 'text-rose-400',
    r'\bborder-rose-200\b': 'border-rose-700/50',
    r'\bbg-red-50\b': 'bg-red-900/30',
    r'\btext-red-700\b': 'text-red-400',
    r'\btext-red-600\b': 'text-red-500',
    r'\bborder-red-200\b': 'border-red-700/50',
    r'\bborder-red-300\b': 'border-red-700/80',
    r'\bbg-yellow-50\b': 'bg-yellow-900/30',
    r'\bbg-green-50\b': 'bg-green-900/30',
    r'\bbg-green-100\b': 'bg-green-900/50',
    r'\btext-green-700\b': 'text-green-400',
    r'\btext-green-800\b': 'text-green-300',
    r'\bborder-green-200\b': 'border-green-700/50',
    r'\bbg-gradient-to-r from-blue-600 to-indigo-600\b': 'bg-gradient-to-r from-blue-950/80 to-indigo-950/80 backdrop-blur-xl border border-blue-500/30 shadow-[0_0_40px_rgba(59,130,246,0.3)]',
    r'\bbg-gradient-to-r from-green-500 to-emerald-600\b': 'bg-gradient-to-r from-emerald-950/80 to-green-950/80 backdrop-blur-xl border border-emerald-500/30 shadow-[0_0_40px_rgba(16,185,129,0.3)]',
}

for old, new in replacements.items():
    content = re.sub(old, new, content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print('Redesign applied!')
