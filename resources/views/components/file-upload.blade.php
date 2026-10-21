{{-- FILE: resources/views/components/file-upload.blade.php --}}
@props(['id', 'label', 'icon' => 'fa-file', 'required' => true])

<div class="group relative" x-data>
    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2 ml-1">
        {{ $label }} @if($required) <span class="text-red-500">*</span> @endif
    </label>
    
    <div class="relative w-full h-36 rounded-2xl border-2 border-dashed border-slate-300 bg-white/50 hover:bg-blue-50/50 hover:border-blue-400 transition-all duration-300 cursor-pointer flex flex-col items-center justify-center gap-1 group-hover:shadow-md"
         onclick="document.getElementById('{{ $id }}').click()"
         :class="{'!border-green-500 !bg-green-50/30': hasFile('{{ $id }}')}">
         
        {{-- Input File Asli (Hidden) --}}
        <input type="file" 
               name="{{ $id }}" 
               id="{{ $id }}" 
               class="hidden" 
               accept=".pdf,.jpg,.jpeg,.png" 
               @if($required) required @endif
               x-on:change="handleFileChange('{{ $id }}')">
        
        {{-- State: Kosong / Belum Upload --}}
        <div x-show="!hasFile('{{ $id }}')" class="text-center pointer-events-none">
            <div class="h-10 w-10 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-2 group-hover:bg-blue-100 group-hover:text-blue-500 transition-colors">
                <i class="fas {{ $icon }} text-lg"></i>
            </div>
            <p class="text-xs font-bold text-slate-600 group-hover:text-blue-600">Upload File</p>
            <p class="text-[9px] text-slate-400">PDF/JPG (Max 2MB)</p>
        </div>

        {{-- State: Terisi / Sudah Upload --}}
        <div x-show="hasFile('{{ $id }}')" class="text-center pointer-events-none" style="display:none;">
            <div class="h-10 w-10 mx-auto rounded-full bg-green-100 text-green-600 flex items-center justify-center mb-2 shadow-sm">
                <i class="fas fa-check text-lg"></i>
            </div>
            {{-- Nama file akan diisi via JS ke element ID ini --}}
            <p class="text-xs font-bold text-green-700 px-4 line-clamp-1 break-all" id="{{ $id }}_name"></p>
            <p class="text-[9px] text-green-600 mt-1 bg-green-200/50 px-2 py-0.5 rounded-full inline-block font-bold">Siap Scan</p>
        </div>
    </div>
</div>