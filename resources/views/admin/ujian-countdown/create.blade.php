@extends('layouts.admin.app')

@section('title', 'Tambah Countdown Ujian')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@section('content')
<div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
    <div class="flex flex-col gap-5.5 p-6.5">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-black dark:text-white">
                    Tambah Countdown Ujian
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Buat countdown untuk ujian baru
                </p>
            </div>
            
            <a href="{{ route('admin.ujian-countdown.index') }}" 
               class="inline-flex items-center justify-center rounded-md border border-stroke px-4 py-2.5 text-sm font-medium hover:bg-gray-100 dark:border-strokedark dark:hover:bg-meta-4">
                Kembali
            </a>
        </div>

        <!-- Form -->
        <form action="{{ route('admin.ujian-countdown.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 gap-5.5 md:grid-cols-2">
                <!-- Nama Ujian -->
                <div class="md:col-span-2">
                    <label class="mb-3 block text-sm font-medium text-black dark:text-white">
                        Nama Ujian <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           name="ujian_name" 
                           value="{{ old('ujian_name') }}"
                           placeholder="Masukkan nama ujian"
                           class="w-full rounded-lg border border-stroke bg-transparent px-5 py-3 text-black outline-none transition focus:border-primary dark:border-strokedark dark:bg-form-input dark:text-white dark:focus:border-primary"
                           required>
                    @error('ujian_name')
                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Deskripsi -->
                <div class="md:col-span-2">
                    <label class="mb-3 block text-sm font-medium text-black dark:text-white">
                        Deskripsi (Opsional)
                    </label>
                    <textarea name="description" 
                              rows="3"
                              placeholder="Masukkan deskripsi ujian"
                              class="w-full rounded-lg border border-stroke bg-transparent px-5 py-3 text-black outline-none transition focus:border-primary dark:border-strokedark dark:bg-form-input dark:text-white dark:focus:border-primary">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Tanggal Mulai -->
                <div>
                    <label class="mb-3 block text-sm font-medium text-black dark:text-white">
                        Tanggal & Waktu Mulai <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           name="start_date" 
                           id="start_date"
                           value="{{ old('start_date') }}"
                           placeholder="Pilih tanggal dan waktu mulai"
                           class="w-full rounded-lg border border-stroke bg-transparent px-5 py-3 text-black outline-none transition focus:border-primary dark:border-strokedark dark:bg-form-input dark:text-white dark:focus:border-primary"
                           required>
                    @error('start_date')
                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Tanggal Berakhir -->
                <div>
                    <label class="mb-3 block text-sm font-medium text-black dark:text-white">
                        Tanggal & Waktu Berakhir <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           name="end_date" 
                           id="end_date"
                           value="{{ old('end_date') }}"
                           placeholder="Pilih tanggal dan waktu berakhir"
                           class="w-full rounded-lg border border-stroke bg-transparent px-5 py-3 text-black outline-none transition focus:border-primary dark:border-strokedark dark:bg-form-input dark:text-white dark:focus:border-primary"
                           required>
                    @error('end_date')
                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Info Durasi -->
                <div class="md:col-span-2">
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                        <h4 class="mb-2 text-sm font-medium text-black dark:text-white">
                            Informasi Durasi
                        </h4>
                        <div id="duration-info" class="text-sm text-gray-500 dark:text-gray-400">
                            Durasi akan dihitung otomatis setelah memilih tanggal
                        </div>
                    </div>
                </div>
                
                <!-- Status Aktif -->
                <div class="md:col-span-2">
                    <div class="flex items-center space-x-3">
                        <input type="checkbox" 
                               name="is_active" 
                               id="is_active"
                               value="1"
                               class="h-5 w-5 rounded border-stroke bg-transparent"
                               {{ old('is_active') ? 'checked' : '' }}>
                        <label for="is_active" class="text-sm font-medium text-black dark:text-white">
                            Jadikan countdown aktif
                        </label>
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Jika diaktifkan, countdown lain akan dinonaktifkan
                    </p>
                </div>
            </div>
            
            <!-- Tombol Aksi -->
            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('admin.ujian-countdown.index') }}" 
                   class="inline-flex items-center justify-center rounded-md border border-stroke px-6 py-3 text-sm font-medium hover:bg-gray-100 dark:border-strokedark dark:hover:bg-meta-4">
                    Batal
                </a>
                <button type="submit" 
                        style="background-color: #3C50E0 !important; color: white !important; opacity: 1 !important;"
                        class="inline-flex items-center justify-center rounded-md px-6 py-3 text-sm font-medium hover:opacity-90">
                    Simpan Countdown
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi flatpickr dengan lokal Indonesia
    const startDatePicker = flatpickr("#start_date", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        time_24hr: true,
        locale: "id",
        minDate: "today",
        defaultHour: 8,
        defaultMinute: 0,
        onChange: function(selectedDates, dateStr, instance) {
            updateDurationInfo();
            // Set min date untuk end date
            if (selectedDates[0]) {
                endDatePicker.set('minDate', selectedDates[0]);
            }
        }
    });

    const endDatePicker = flatpickr("#end_date", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        time_24hr: true,
        locale: "id",
        minDate: "today",
        defaultHour: 17,
        defaultMinute: 0,
        onChange: function() {
            updateDurationInfo();
        }
    });

    // Fungsi untuk menghitung dan menampilkan durasi
    function updateDurationInfo() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const durationInfo = document.getElementById('duration-info');

        if (startDate && endDate) {
            const start = moment(startDate);
            const end = moment(endDate);
            
            if (end.isBefore(start)) {
                durationInfo.innerHTML = '<span class="text-danger">Tanggal berakhir tidak boleh sebelum tanggal mulai</span>';
                return;
            }

            const duration = moment.duration(end.diff(start));
            const days = Math.floor(duration.asDays());
            const hours = duration.hours();
            const minutes = duration.minutes();

            let durationText = '';
            if (days > 0) {
                durationText += `${days} hari `;
            }
            if (hours > 0) {
                durationText += `${hours} jam `;
            }
            if (minutes > 0) {
                durationText += `${minutes} menit`;
            }

            durationInfo.innerHTML = `
                <div class="grid grid-cols-3 gap-2">
                    <div class="text-center">
                        <div class="text-lg font-semibold text-primary">${days}</div>
                        <div class="text-xs">Hari</div>
                    </div>
                    <div class="text-center">
                        <div class="text-lg font-semibold text-primary">${hours}</div>
                        <div class="text-xs">Jam</div>
                    </div>
                    <div class="text-center">
                        <div class="text-lg font-semibold text-primary">${minutes}</div>
                        <div class="text-xs">Menit</div>
                    </div>
                </div>
                <div class="mt-2 text-xs text-gray-400">
                    Total: ${duration.asHours().toFixed(1)} jam
                </div>
            `;
        }
    }

    // Validasi form
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        if (!startDate) {
            e.preventDefault();
            showToast('Tanggal mulai harus diisi', 'error');
            return;
        }
        
        if (!endDate) {
            e.preventDefault();
            showToast('Tanggal berakhir harus diisi', 'error');
            return;
        }
        
        if (moment(endDate).isBefore(moment(startDate))) {
            e.preventDefault();
            showToast('Tanggal berakhir tidak boleh sebelum tanggal mulai', 'error');
            return;
        }
    });
    
    // Fungsi toast
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-md shadow-lg text-white ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        }`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Update duration info saat halaman dimuat jika ada nilai lama
    updateDurationInfo();
});
</script>
@endpush