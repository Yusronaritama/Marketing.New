<div class="animate-fade-in-up relative">
    <!-- Decorative background glow for the card -->
    <div class="absolute -inset-1 bg-gradient-to-r from-blue-100 to-purple-100 rounded-[2.5rem] blur-xl opacity-50 pointer-events-none"></div>

    <div class="relative bg-white/80 backdrop-blur-xl rounded-[2rem] shadow-xl border border-white p-6 sm:p-10">
        {{-- Header Section --}}
        <div class="flex items-center gap-4 mb-8 pb-6 border-b border-indigo-100/50 relative">
            <!-- Animated Icon Container -->
            <div class="relative h-14 w-14 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-[0_8px_16px_-6px_rgba(79,70,229,0.5)] overflow-hidden group-hover:scale-105 transition-transform">
                <div class="absolute inset-0 bg-white/20 blur-sm translate-y-full group-hover:translate-y-0 transition-transform duration-500"></div>
                <i class="fas fa-user text-xl relative z-10"></i>
            </div>
            <div>
                <h2 class="text-2xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-slate-800 to-indigo-800 tracking-tight">Data Pribadi</h2>
                <p class="text-sm font-medium text-slate-500 mt-0.5">Lengkapi identitas diri Anda dengan valid dan benar.</p>
            </div>
        </div>
        
        <form id="step1Form" x-on:submit.prevent="submitStep1()">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                {{-- 1. NAMA LENGKAP --}}
                <div class="space-y-2 group">
                    <label class="text-[11px] font-extrabold text-slate-600 uppercase tracking-widest ml-1">
                        Nama Lengkap <span class="text-pink-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-id-card text-slate-400 group-focus-within:text-indigo-500 transition-colors duration-300"></i>
                        </div>
                        <input type="text" name="nama" value="{{ $registration->nama ?? auth()->user()->name }}"
                               class="w-full pl-12 pr-4 py-3.5 bg-white/50 backdrop-blur-sm border border-white/80 rounded-xl text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-400 focus:bg-white transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)] hover:shadow-md"
                               placeholder="Sesuai KTP/Ijazah" required>
                    </div>
                </div>
                
                {{-- 2. EMAIL --}}
                <div class="space-y-2 group">
                    <label class="text-[11px] font-extrabold text-slate-600 uppercase tracking-widest ml-1">
                        Alamat Email <span class="text-pink-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-slate-400 group-focus-within:text-indigo-500 transition-colors duration-300"></i>
                        </div>
                        <input type="email" name="email" value="{{ $registration->gmail ?? auth()->user()->email }}"
                               class="w-full pl-12 pr-4 py-3.5 bg-white/50 backdrop-blur-sm border border-white/80 rounded-xl text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-400 focus:bg-white transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)] hover:shadow-md"
                               placeholder="contoh@email.com" required>
                    </div>
                </div>
                
                {{-- 3. NOMOR TELEPON --}}
                <div class="space-y-2 group">
                    <label class="text-[11px] font-extrabold text-slate-600 uppercase tracking-widest ml-1">
                        Nomor WhatsApp <span class="text-pink-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fab fa-whatsapp text-slate-400 group-focus-within:text-emerald-500 transition-colors duration-300 text-lg"></i>
                        </div>
                        <input type="tel" name="phone" value="{{ $registration->phone ?? auth()->user()->phone }}"
                               class="w-full pl-12 pr-4 py-3.5 bg-white/50 backdrop-blur-sm border border-white/80 rounded-xl text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-400 focus:bg-white transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)] hover:shadow-md"
                               placeholder="0812xxxx" required>
                    </div>
                </div>
                
                {{-- 4. STATUS PEKERJAAN --}}
                <div class="space-y-2 group">
                    <label class="text-[11px] font-extrabold text-slate-600 uppercase tracking-widest ml-1">
                        Status Pekerjaan <span class="text-pink-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-briefcase text-slate-400 group-focus-within:text-indigo-500 transition-colors duration-300"></i>
                        </div>
                        <select name="status_pekerjaan" 
                                class="w-full pl-12 pr-10 py-3.5 bg-white/50 backdrop-blur-sm border border-white/80 rounded-xl text-slate-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-400 focus:bg-white transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)] hover:shadow-md appearance-none cursor-pointer" 
                                required>
                            <option value="" disabled {{ !isset($registration->status_pekerjaan) ? 'selected' : '' }}>-- Pilih Status --</option>
                            <option value="0" {{ isset($registration->status_pekerjaan) && $registration->status_pekerjaan == '0' ? 'selected' : '' }}>Tidak Bekerja</option>
                            <option value="1" {{ isset($registration->status_pekerjaan) && $registration->status_pekerjaan == '1' ? 'selected' : '' }}>Bekerja</option>
                        </select>
                        {{-- Custom Chevron --}}
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-xs text-indigo-400"></i>
                        </div>
                    </div>
                </div>

            </div>
            
            {{-- Navigation Buttons --}}
            <div class="flex justify-end mt-12 pt-8 border-t border-indigo-100/50">
                <button type="submit" 
                        class="group relative inline-flex items-center justify-center gap-3 px-9 py-4 text-sm font-extrabold text-white uppercase tracking-widest transition-all duration-500 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 rounded-xl hover:shadow-[0_0_25px_rgba(79,70,229,0.5)] focus:ring-4 focus:ring-indigo-500/20 disabled:opacity-70 disabled:cursor-not-allowed overflow-hidden"
                        x-bind:disabled="loading">
                    
                    <!-- Shimmer effect -->
                    <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent group-hover:animate-shimmer pointer-events-none"></div>

                    <span x-show="!loading" class="flex items-center gap-3 relative z-10">
                        Simpan & Lanjutkan 
                        <span class="bg-white/20 p-1.5 rounded-lg group-hover:bg-white/30 transition-colors">
                            <i class="fas fa-arrow-right transition-transform group-hover:translate-x-1"></i>
                        </span>
                    </span>
                    
                    <span x-show="loading" class="flex items-center gap-2 relative z-10" x-cloak>
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Menyimpan...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function submitStep1() {
    // Tampilkan loading state (via Alpine jika ada, atau manual)
    // Asumsi parent component Alpine memiliki variable 'loading'
    
    const form = document.getElementById('step1Form');
    const formData = new FormData(form);
    
    // Opsional: Validasi manual sederhana agar UX lebih cepat
    if(!formData.get('nama') || !formData.get('email') || !formData.get('phone') || !formData.get('status_pekerjaan')) {
        Swal.fire({
            icon: 'warning',
            title: 'Data Belum Lengkap',
            text: 'Mohon lengkapi semua kolom yang bertanda bintang (*).',
            confirmButtonColor: '#3b82f6'
        });
        return;
    }

    fetch('{{ route("mahasiswa.registration.step1") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });

            Toast.fire({
                icon: 'success',
                title: data.message || 'Data berhasil disimpan'
            }).then(() => {
                window.location.reload();
            });
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Gagal Menyimpan',
            text: error.message || 'Terjadi kesalahan pada server.',
            confirmButtonColor: '#ef4444'
        });
    });
}
</script>