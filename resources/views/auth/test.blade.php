<style>
  @keyframes shake {
    10%, 90% { transform: translate3d(-1px, 0, 0); }
    20%, 80% { transform: translate3d(2px, 0, 0); }
    30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
    40%, 60% { transform: translate3d(4px, 0, 0); }
  }
  .shake-error { animation: shake 0.82s cubic-bezier(.36,.07,.19,.97) both; }

  .duplicate-notification {
    animation: fadeIn 0.5s ease-out;
    border-left: 4px solid #f59e0b;
  }
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .swal2-confirm-button {
    background-color: #3b82f6 !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 6px !important;
    padding: 10px 24px !important;
    font-weight: 600 !important;
    font-size: 1rem !important;
    transition: background-color 0.2s !important;
    display: inline-block !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
  }
  .swal2-confirm-button:hover { background-color: #2563eb !important; }

  .swal2-container { z-index: 9999 !important; }
</style>

<x-guest-layout>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        .form-container { transition: all 0.5s ease-in-out; }
        .hidden-form { display: none; }
    </style>

    <div class="py-6 max-w-lg mx-auto">
        <div class="flex mb-8 bg-blue-100 p-1 rounded-xl" data-aos="fade-down">
		    <button onclick="switchForm('reguler')" id="tab-reguler" class="w-1/3 py-2 text-sm font-bold rounded-lg transition-all duration-300 bg-white shadow text-blue-600">
		        S1 REGULER
		    </button>
            @php
                $s2Status = \Illuminate\Support\Facades\Storage::disk('local')->exists('app_s2_status.json') 
                    ? json_decode(\Illuminate\Support\Facades\Storage::disk('local')->get('app_s2_status.json'), true)['active'] ?? true 
                    : true;
            @endphp
            @if($s2Status)
		    <button onclick="switchForm('s2')" id="tab-s2" class="w-1/3 py-2 text-sm font-bold rounded-lg transition-all duration-300 text-slate-600 hover:text-blue-600 hover:bg-blue-50 cursor-pointer">
		        S2 PASCASARJANA
		    </button>
            @else
            <button id="tab-s2" class="w-1/3 py-2 text-sm font-bold rounded-lg transition-all duration-300 text-slate-600 pointer-events-none cursor-not-allowed opacity-50">
                S2 PASCASARJANA
            </button>
            @endif
            @php
                $apotekerStatus = \Illuminate\Support\Facades\Storage::disk('local')->exists('app_apoteker_status.json') 
                    ? json_decode(\Illuminate\Support\Facades\Storage::disk('local')->get('app_apoteker_status.json'), true)['active'] ?? false 
                    : false;
            @endphp
            @if($apotekerStatus)
            <button onclick="switchForm('apoteker')" id="tab-apoteker" class="w-1/3 py-2 text-sm font-bold rounded-lg transition-all duration-300 text-slate-600 hover:text-blue-600 hover:bg-blue-50 cursor-pointer">
                PROFESI APOTEKER
            </button>
            @else
            <button id="tab-apoteker" class="w-1/3 py-2 text-sm font-bold rounded-lg transition-all duration-300 text-slate-600 pointer-events-none cursor-not-allowed opacity-50">
                PROFESI APOTEKER
            </button>
            @endif

    
		</div>

        @if($is_maintenance)
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6" data-aos="fade-down">
                <p class="text-sm text-red-700 font-bold">Pemberitahuan:</p>
                <p class="text-sm text-red-600">{{ $maintenance_message }}</p>
            </div>
        @endif
@if($apotekerStatus)
		<div id="form-apoteker" class="form-container hidden-form">
    <h2 class="mb-3 text-3xl font-bold text-blue-700">PENDAFTARAN PROFESI APOTEKER</h2>
    <p class="mb-6 font-light text-blue-500">Silakan lengkapi data pendaftaran Anda. Username & password otomatis dibuat dari data nomor WhatsApp Anda.</p>
@if($pendaftaran_tutup)
    <div class="alert alert-danger" style="text-align:center; padding: 40px; border: 1px solid #ebccd1; background-color: #f2dede; border-radius: 8px;">
        <h2 style="color: #a94442;">Pendaftaran Saat Ini Ditutup</h2>
        <p style="margin-bottom: 20px;">
            Mohon maaf, saat ini sedang tidak dalam periode pendaftaran. 
            Berikut adalah jadwal resmi pendaftaran:
        </p>

        <ul style="list-style: none; padding: 0; margin-bottom: 25px;">
            @foreach($daftarGelombang as $gel)
                <li style="margin-bottom: 5px; font-weight: bold;">
                    {{ $gel->nama_gelombang }}: 
                    {{ \Carbon\Carbon::parse($gel->tanggal_mulai)->format('d M Y') }} 
                    s/d 
                    {{ \Carbon\Carbon::parse($gel->tanggal_selesai)->format('d M Y') }}
                </li>
            @endforeach
        </ul>

        <p style="font-size: 0.9em; color: #777;">Silakan kembali saat periode pendaftaran sudah dibuka.</p>
        <br>
        <a href="/" class="btn btn-primary" style="padding: 10px 20px; background: #333; color: #fff; text-decoration: none; border-radius: 5px;">
            Kembali ke Beranda
        </a>
    </div>
@else
    <form action="{{ route('register-test') }}" method="POST" class="mandiriForm" data-type="apoteker">
        @csrf
        <input type="hidden" name="type" value="apoteker"> <!-- INI PENTING -->

        <div class="mb-4">
            <label class="block mb-2 text-sm font-medium text-blue-700">Nama Lengkap (tanpa gelar)</label>
            <input type="text" name="name" required class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500">
        </div>

        <div class="mb-4">
            <label class="block mb-2 text-sm font-medium text-blue-700">Email</label>
            <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500" placeholder="contoh@gmail.com">
        </div>

        <div class="mb-4">
            <label class="block mb-2 text-sm font-medium text-blue-700">Nomor WhatsApp</label>
            <input type="tel" name="phone" required placeholder="0812..." maxlength="13" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500">
        </div>

        <div class="mb-4 p-4 border rounded-lg bg-blue-50">
            <label class="block mb-2 text-sm font-medium text-blue-800">Apakah Anda Alumni UBP Karawang?</label>
            <div class="flex space-x-4">
                <label class="flex items-center">
                    <input type="radio" name="is_alumni" value="1" onchange="toggleNimField(true)" class="form-radio text-blue-600">
                    <span class="ml-2">Ya, Saya Alumni</span>
                </label>
                <label class="flex items-center">
                    <input type="radio" name="is_alumni" value="0" onchange="toggleNimField(false)" class="form-radio text-blue-600" checked>
                    <span class="ml-2">Bukan Alumni</span>
                </label>
            </div>
            
            <div id="nim-container" class="mt-4 hidden">
                <label class="block mb-2 text-sm font-medium text-blue-700">Masukkan NIM Anda</label>
                <input type="text" name="nim" id="input-nim" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500" placeholder="Contoh: 18416226201...">
            </div>
        </div>

        <!--<div class="mb-6">-->
        <!--    <label class="block mb-2 text-sm font-medium text-blue-700">Kode Promo (Opsional)</label>-->
            <!-- NAME HARUS TETAP 'promo_code' AGAR CONTROLLER BISA MEMBACANYA -->
        <!--    <input type="text" name="promo_code" id="promo_code_apoteker" placeholder="Masukkan kode promo jika ada" class="w-full px-4 py-2 border border-dashed border-blue-300 rounded-lg focus:ring-blue-500 bg-blue-50 uppercase">-->
        <!--    <div id="promo-message-apoteker" class="mt-2 text-sm hidden"></div>-->
        <!--</div>-->

        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 transition duration-300">
            DAFTAR APOTEKER & GENERATE VA
        </button>
    </form>
    @endif
</div>
@endif
        <!-- FORM REGULER -->
        <div id="form-reguler" class="form-container">
            <h2 data-aos="fade-up" class="mb-3 text-3xl font-bold text-blue-800">PENDAFTARAN MANDIRI</h2>
            <p data-aos="fade-up" data-aos-delay="80" class="mb-6 font-light text-blue-500">
                Gunakan form ini untuk mengetes integrasi VA Mandiri SLIK.
            </p>

            <form action="{{ route('register-test') }}" method="POST" class="mandiriForm" data-type="reguler">
                @csrf
                <input type="hidden" name="type" value="reguler">

                <div class="mb-4" data-aos="fade-right" data-aos-delay="120">
                    <label class="block mb-2 text-sm font-medium text-blue-700">Nama Lengkap</label>
                    <input type="text" id="input-nama-reguler" name="name" required class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500" placeholder="Hanya huruf">
                </div>

                <div class="mb-4" data-aos="fade-right" data-aos-delay="160">
                    <label class="block mb-2 text-sm font-medium text-blue-700">Email</label>
                    <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500" placeholder="pmb@gmail.com">
                </div>

                <div class="mb-4" data-aos="fade-right" data-aos-delay="200">
                    <label class="block mb-2 text-sm font-medium text-blue-700">Nomor WhatsApp</label>
                    <input type="tel" id="input-phone-reguler" name="phone" required placeholder="0812..." maxlength="13" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500">
                </div>

                <div class="mb-6" data-aos="fade-right" data-aos-delay="240">
                    <label class="block mb-2 text-sm font-medium text-blue-700">Kode Promo (Opsional)</label>
                    <!--<input type="text" name="promo_code" id="promo_code" value="PROMOKEMERDEKAAN" placeholder="Masukkan kode promo jika ada" class="w-full px-4 py-2 border border-dashed border-blue-300 rounded-lg focus:ring-blue-500 bg-blue-50 uppercase">-->
                    <input type="text" name="promo_code" id="promo_code" placeholder="Masukkan kode promo jika ada" class="w-full px-4 py-2 border border-dashed border-blue-300 rounded-lg focus:ring-blue-500 bg-blue-50 uppercase">
                    <div id="promo-message" class="mt-2 text-sm hidden"></div>
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 transition duration-300">
                    DAFTAR & GENERATE VA
                </button>
            </form>

            <div class="mt-6 text-center">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="text-sm text-blue-500 hover:text-blue-600 hover:underline">Login</a>
            </div>
        </div>

        <!-- FORM S2 -->
        @if($s2Status)
        <div id="form-s2" class="form-container hidden-form">
            <h2 class="mb-3 text-3xl font-bold text-indigo-800">PENDAFTARAN PASCARSARJANA (S2)</h2>
            <p class="mb-6 font-light text-blue-500">Lengkapi data akademik untuk pendaftaran jenjang S2.</p>

            <form action="{{ route('register-test') }}" method="POST" class="mandiriForm" data-type="s2">
                @csrf
                <input type="hidden" name="type" value="s2">

                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-blue-700">Nama Lengkap</label>
                    <input type="text" name="name" required placeholder="Contoh: Budi" class="w-full px-4 py-2 border border-indigo-200 rounded-lg focus:ring-indigo-500">
                </div>

                <div class="mb-4" data-aos="fade-right" data-aos-delay="160">
                    <label class="block mb-2 text-sm font-medium text-blue-700">Email</label>
                    <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500" placeholder="pmb@gmail.com">
                </div>

                <div class="mb-4" data-aos="fade-right" data-aos-delay="200">
                    <label class="block mb-2 text-sm font-medium text-blue-700">Nomor WhatsApp</label>
                    <input type="tel" name="phone" required placeholder="0812..." class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-blue-700">Program Studi S2 yang Dituju</label>
                    <select name="program_study" class="w-full px-4 py-2 border border-indigo-200 rounded-lg focus:ring-indigo-500">
                        <option value="61114">Magister Manajemen (S2)</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition duration-300">
                    KIRIM PENDAFTARAN S2
                </button>
            </form>

            <div class="mt-6 text-center">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="text-sm text-blue-500 hover:text-blue-600 hover:underline">Login</a>
            </div>
        </div>
        @endif
    </div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.10.8/sweetalert2.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

<script>
    function toggleNimField(isAlumni) {
        const nimContainer = document.getElementById('nim-container');
        const inputNim = document.getElementById('input-nim');
        
        if (isAlumni) {
            nimContainer.classList.remove('hidden');
            inputNim.setAttribute('required', 'required'); // Wajibkan jika dicentang
        } else {
            nimContainer.classList.add('hidden');
            inputNim.removeAttribute('required'); // Hilangkan wajib jika tidak dicentang
            inputNim.value = ''; // Kosongkan nilai jika dibatalkan
        }
    }

    function showRegulerInfo() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Informasi Penting!',
                html: `
                    <ul class="text-left text-sm space-y-2" style="list-style-type: none; padding-left: 0;">
                        <li>✅ <b>Username & Password</b> otomatis dikirim ke Email.</li>
                        <li>✅ Gunakan <b>Email Aktif</b> untuk menerima bukti bayar.</li>
                        <li>✅ Pastikan nomor HP diawali <b>08...</b></li>
                        <li>💰 <b>Kode Promo</b> tersedia untuk diskon biaya pendaftaran.</li>
                    </ul>
                `,
                icon: 'info',
                confirmButtonText: 'Saya Mengerti',
                customClass: { confirmButton: 'swal2-confirm-button' },
                backdrop: `rgba(0,0,123,0.1)`
            });
        }
    }

    function switchForm(type) {
        const reguler  = document.getElementById('form-reguler');
        const s2       = document.getElementById('form-s2');
        const apoteker = document.getElementById('form-apoteker');
        
        const btnReguler  = document.getElementById('tab-reguler');
        const btnS2       = document.getElementById('tab-s2');
        const btnApoteker = document.getElementById('tab-apoteker');

        // Reset semua form dan tombol ke state default (hidden / abu-abu)
        [reguler, s2, apoteker].forEach(form => form.classList.add('hidden-form'));
        [btnReguler, btnS2, btnApoteker].forEach(btn => {
            btn.classList.remove('bg-white', 'shadow', 'text-blue-600', 'text-indigo-600', 'text-blue-600');
            btn.classList.add('text-slate-600');
        });

        if (type === 's2') {
            s2.classList.remove('hidden-form');
            btnS2.classList.add('bg-white', 'shadow', 'text-indigo-600');
            btnS2.classList.remove('text-slate-600');
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Info Pendaftaran S2',
                    html: 'Pendaftaran gelombang ini sedang diproses. <br><b>Informasi jadwal perkuliahan diupdate pada 4-11 April.</b>',
                    icon: 'info', toast: true, position: 'top-end',
                    showConfirmButton: false, timer: 5000, timerProgressBar: true
                });
            }
        } else if (type === 'apoteker') {
            apoteker.classList.remove('hidden-form');
            btnApoteker.classList.add('bg-white', 'shadow', 'text-blue-600');
            btnApoteker.classList.remove('text-slate-600');
        } else {
            reguler.classList.remove('hidden-form');
            btnReguler.classList.add('bg-white', 'shadow', 'text-blue-600');
            btnReguler.classList.remove('text-slate-600');
            showRegulerInfo(); // Menampilkan info pop-up
        }
        
        if (typeof AOS !== 'undefined') AOS.refresh();
    }

    document.addEventListener("DOMContentLoaded", function () {
        if (typeof AOS !== 'undefined') {
            AOS.init({ duration: 800, once: true });
        }
        
        setTimeout(() => showRegulerInfo(), 1000);

        // Validasi nama — hanya huruf, spasi, titik, koma
        document.querySelectorAll('input[name="name"]').forEach(input => {
            input.addEventListener('input', function () {
                this.value = this.value.replace(/[^a-zA-Z\s.,]/g, '');
            });
        });

        // Validasi nomor HP
        document.querySelectorAll('input[name="phone"]').forEach(input => {
            input.addEventListener('input', function () {
                let val = this.value.replace(/\D/g, '');
                if (val.length > 0 && val[0] !== '0') val = '0' + val;
                this.value = val.substring(0, 13);
            });
            input.addEventListener('blur', function () {
                if (this.value.length > 0 && !this.value.startsWith('08')) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning', title: 'Format Salah',
                            text: 'Nomor WhatsApp harus diawali dengan 08',
                            toast: true, position: 'top-end',
                            showConfirmButton: false, timer: 3000
                        });
                    } else {
                        alert('Format Salah: Nomor WhatsApp harus diawali dengan 08');
                    }
                    this.value = '';
                }
            });
        });

        // =========================================================
        // === VALIDASI PROMO CODE
        // =========================================================
        let promoValidated = false;
        let promoDataCache = null;

       const promoInput   = document.getElementById('promo_code');
        const promoMessage = document.getElementById('promo-message');

        if (promoInput) {
            let debounceTimer;
            promoInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                const code = this.value.trim().toUpperCase();

                if (code.length === 0) {
                    promoMessage.className = 'mt-2 text-sm hidden';
                    promoMessage.innerHTML = '';
                    promoValidated  = false;
                    promoDataCache  = null;
                    return;
                }

                debounceTimer = setTimeout(() => validatePromoCode(code), 800);
            });

            if (promoInput.value.trim() !== '') {
                promoInput.dispatchEvent(new Event('input'));
            }
        }

        function validatePromoCode(code) {
            const activeForm = document.querySelector('.form-container:not(.hidden-form) form');
            const formType   = activeForm ? activeForm.getAttribute('data-type') : 'reguler';
            
            let isAlumni = '0';
            if (formType === 'apoteker') {
                const checkedAlumni = document.querySelector('input[name="is_alumni"]:checked');
                isAlumni = checkedAlumni ? checkedAlumni.value : '0';
            }

            const activePromoMessage = formType === 'apoteker' 
                ? document.getElementById('promo-message-apoteker') 
                : document.getElementById('promo-message');

            activePromoMessage.innerHTML  = '<i class="fas fa-spinner fa-spin"></i> Memeriksa kode promo...';
            activePromoMessage.className  = 'mt-2 text-sm text-blue-600';
            activePromoMessage.classList.remove('hidden');

            fetch('{{ route("validate.promo") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ 
                    promo_code: code,
                    type: formType,
                    is_alumni: isAlumni
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.valid) {
                    promoValidated = true;
                    promoDataCache = data;

                    const promoInfo   = data.promo_code || {};
                    const discountPct = promoInfo.discount    ?? '-';
                    const desc        = promoInfo.description ?? '';
                    const isPaid      = data.status_info?.is_paid ?? false;

                    const pricing      = data.pricing || {};
                    const fOriginal    = pricing.formatted_original    || '';
                    const fDiscounted  = pricing.formatted_discounted  || '';
                    const fSaving      = pricing.formatted_saving      || '';

                    if (isPaid) {
                        activePromoMessage.innerHTML = `
                            ✅ <strong>Promo Valid!</strong> Diskon ${discountPct}% ${desc ? '— ' + desc : ''}
                            <br><span class="font-bold text-green-700">🎉 TAGIHAN: RP 0 (LUNAS — tidak perlu bayar VA)</span>`;
                        activePromoMessage.className = 'mt-2 text-sm text-green-700 bg-green-50 border border-green-200 p-2 rounded';
                    } else {
                        activePromoMessage.innerHTML = `
                            ✅ <strong>Promo Valid!</strong> Diskon ${discountPct}% ${desc ? '— ' + desc : ''}
                            <br>Harga normal: <span class="line-through text-blue-400">`+fOriginal+`</span>
                            → Bayar: <strong>`+fDiscounted+`</strong> <span class="text-xs italic">(hemat `+fSaving+`)</span>`;
                        activePromoMessage.className = 'mt-2 text-sm text-green-600 bg-green-50 border border-green-200 p-2 rounded';
                    }
                } else {
                    promoValidated = false;
                    promoDataCache = null;
                    activePromoMessage.innerHTML = `❌ ${data.message}`;
                    activePromoMessage.className = 'mt-2 text-sm text-red-600 bg-red-50 p-2 rounded';
                }
            })
            .catch(err => {
                console.error(err);
                activePromoMessage.innerHTML = '⚠️ Gagal memvalidasi kode promo';
                activePromoMessage.className = 'mt-2 text-sm text-orange-600';
            });
        }

        document.querySelectorAll('input[name="is_alumni"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const promoInput = document.getElementById('promo_code_apoteker');
                if (promoInput && promoInput.value.trim().length > 0) {
                    validatePromoCode(promoInput.value.trim().toUpperCase());
                }
            });
        });

        // =========================================================
        // === FORM SUBMISSION (AJAX)
        // =========================================================
        document.querySelectorAll('.mandiriForm').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(this);
                const formType = this.getAttribute('data-type');
                const isS2     = formType === 's2';
                
                // Fallback jika Swal gagal di-load
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Memproses Pendaftaran...',
                        html: isS2 ? 'Menyimpan data S2...' : 'Membuat Virtual Account Mandiri...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                }

                // Cek duplikasi dulu
                fetch('{{ route("check.duplicate.registration-test") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        email: formData.get('email'),
                        phone: formData.get('phone')
                    })
                })
                .then(res => res.json())
                .then(dupData => {
                    if (dupData.is_duplicate) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'info',
                                title: 'Akun Terdaftar',
                                text: dupData.message,
                                timer: 2000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                didClose: () => { window.location.href = dupData.redirect; }
                            });
                        } else {
                            alert(dupData.message);
                            window.location.href = dupData.redirect;
                        }
                    } else {
                        return fetch('{{ route("register-test") }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                    }
                })
                .then(res => res ? res.json() : null)
                .then(data => {
                    if (!data) return;

                    if (data.status === 'success') {
                        const isFree = data.is_free ?? false;
                        const amount = new Intl.NumberFormat('id-ID').format(data.amount ?? 0);

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                html: isS2 || formType === 'apoteker'
                                    ? `Pendaftaran berhasil. Mengalihkan ke dashboard...<br><br>VA Mandiri: <strong>${data.va_number}</strong>`
                                    : isFree
                                        ? `VA: <strong>${data.va_number}</strong><br>
                                           <span class="text-green-600 font-bold">🎉 GRATIS — Promo 100% diterapkan</span><br><br>
                                           Mengalihkan ke dashboard...`
                                        : `VA Mandiri: <strong>${data.va_number}</strong><br>
                                           Nominal: <strong>Rp ${amount}</strong><br><br>
                                           Mengalihkan ke dashboard...`,
                                timer: 3000,
                                timerProgressBar: true,
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                willClose: () => {
                                    window.location.href = '{{ route("mahasiswa.va.dashboard") }}';
                                }
                            });
                        } else {
                            alert('Pendaftaran Berhasil! Nomor VA: ' + data.va_number);
                            window.location.href = '{{ route("mahasiswa.va.dashboard") }}';
                        }
                    } else {
                        const userMsg = data.message || 'Terjadi kesalahan sistem. Silakan coba lagi.';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Pendaftaran Gagal',
                                text: userMsg,
                                confirmButtonText: 'Tutup',
                                customClass: { confirmButton: 'swal2-confirm-button' }
                            });
                        } else {
                            alert('Pendaftaran Gagal: ' + userMsg);
                        }
                    }
                })
                .catch(err => {
                    console.error(err);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', 'Gagal menghubungi server', 'error');
                    } else {
                        alert('Error: Gagal menghubungi server');
                    }
                });
            });
        });
    });
</script>
</x-guest-layout>