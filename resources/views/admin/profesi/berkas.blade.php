<div class="mt-8 bg-gray-900 rounded-xl p-6 border border-gray-800">
    <h3 class="text-white font-bold text-lg mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        Daftar Berkas Pendaftar
    </h3>

    <div x-data="{ openFile: null }" class="space-y-4">
        @php
            $dokumenList = [
                'Kartu Keluarga' => $berkas->file_kk ?? null,
                'KTP' => $berkas->file_ktp ?? null,
                'Surat Sehat' => $berkas->file_surat_sehat ?? null,
                'Ijazah' => $berkas->file_ijazah ?? null,
                'Transkrip' => $berkas->file_transkrip ?? null,
                'Akreditasi' => $berkas->file_akreditasi ?? null,
                'Asuransi' => $berkas->file_asuransi ?? null,
                'Pasfoto' => $berkas->file_pasfoto ?? null,
            ];
        @endphp

        <select x-model="openFile" class="w-full bg-black text-cyan-400 border border-gray-700 rounded-lg p-3 font-bold focus:ring-cyan-500 focus:border-cyan-500">
            <option value="">-- Pilih Berkas untuk Ditampilkan --</option>
            @foreach($dokumenList as $label => $file)
                <option value="{{ $file ? asset('storage/' . $file) : '' }}" {{ !$file ? 'disabled' : '' }}>
                    {{ $label }} {{ !$file ? '(Belum diupload)' : '' }}
                </option>
            @endforeach
        </select>

        <template x-if="openFile">
            <div class="mt-4 border-2 border-gray-700 rounded-lg overflow-hidden h-[600px] bg-white">
                <iframe :src="openFile" class="w-full h-full" frameborder="0"></iframe>
            </div>
        </template>
        
        <template x-if="openFile === ''">
            <div class="text-gray-500 text-center py-10">Silakan pilih berkas dari dropdown di atas.</div>
        </template>
    </div>
</div>