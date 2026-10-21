@extends('layouts.admin.app') 

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Enroll User ke Moodle Course (AJAX Mode)</h6>
        </div>
        <div class="card-body">
            
            {{-- Area Progress Bar (Disembunyikan saat awal) --}}
            <div id="progressContainer" style="display: none;" class="mb-4">
                <h5 id="progressText">Memproses: 0%</h5>
                <div class="progress">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%"></div>
                </div>
                <small class="text-muted">Mohon jangan tutup halaman ini sampai proses selesai.</small>
            </div>

            {{-- Form tidak submit biasa, tapi di-handle JS --}}
            <form id="enrollForm">
                @csrf
                
                <div class="row mb-3">
                    <div class="col-md-5">
                        <label for="course_id"><strong>Pilih Course Tujuan:</strong></label>
                        <select name="course_id" id="course_id" class="form-control" required>
                            <option value="">-- Pilih Course --</option>
                            @foreach($courses as $course)
                                <option value="{{ $course['id'] }}">
                                    {{ $course['fullname'] }} (ID: {{ $course['id'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-7 text-right d-flex align-items-end justify-content-end">
                        <button type="submit" id="btnSubmit" class="btn btn-primary">
                            <i class="fas fa-user-graduate"></i> Mulai Proses Enrollment
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr class="bg-light">
                                <th width="5%" class="text-center">
                                    <input type="checkbox" id="checkAll">
                                </th>
                                <th>Username (VA)</th>
                                <th>Moodle User ID</th>
                                <th>Status Enrol</th>
                                <th>Catatan Sync</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="user-check">
                                </td>
                                <td>{{ $user->username }}</td>
                                <td><span class="badge badge-secondary">{{ $user->moodle_user_id }}</span></td>
                                <td>
                                    @if($user->enrol_status == 'enrolled')
                                        <span class="badge badge-success">Sudah Enrolled</span>
                                    @else
                                        <span class="badge badge-warning">Belum Enrolled</span>
                                    @endif
                                </td>
                                <td class="sync-note-text">{{ $user->sync_notes }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada data.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $users->links() }}
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
    // 1. Check All Functionality
    document.getElementById('checkAll').addEventListener('click', function(e) {
        document.querySelectorAll('.user-check').forEach(chk => chk.checked = e.target.checked);
    });

    // 2. Main Logic AJAX
    document.getElementById('enrollForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const courseId = document.getElementById('course_id').value;
        const selectedCheckboxes = document.querySelectorAll('.user-check:checked');
        
        // Validasi Sederhana
        if (!courseId) {
            Swal.fire('Error', 'Silahkan pilih course terlebih dahulu.', 'error');
            return;
        }
        if (selectedCheckboxes.length === 0) {
            Swal.fire('Warning', 'Pilih minimal satu user untuk dienroll.', 'warning');
            return;
        }

        // Konfirmasi User
        const confirm = await Swal.fire({
            title: 'Mulai Enrollment?',
            text: `Anda akan mendaftarkan ${selectedCheckboxes.length} user. Proses ini akan berjalan bertahap.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Jalankan!',
            cancelButtonText: 'Batal'
        });

        if (!confirm.isConfirmed) return;

        // --- MULAI PROSES BATCHING ---
        
        // UI Update
        const btnSubmit = document.getElementById('btnSubmit');
        const progressContainer = document.getElementById('progressContainer');
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');

        btnSubmit.disabled = true;
        progressContainer.style.display = 'block';
        
        // Kumpulkan semua ID ke dalam Array
        let allIds = Array.from(selectedCheckboxes).map(cb => cb.value);
        
        // Konfigurasi Batch (Misal 10 user per request agar sangat ringan)
        const BATCH_SIZE = 10; 
        const totalBatches = Math.ceil(allIds.length / BATCH_SIZE);
        
        let successCount = 0;
        let failCount = 0;

        for (let i = 0; i < totalBatches; i++) {
            // Ambil potongan array (0-10, 10-20, dst)
            const start = i * BATCH_SIZE;
            const end = start + BATCH_SIZE;
            const batchIds = allIds.slice(start, end);

            try {
                // Kirim AJAX Request menggunakan Axios
                const response = await axios.post("{{ route('moodle.enroll.store') }}", {
                    course_id: courseId,
                    user_ids: batchIds,
                    _token: "{{ csrf_token() }}" // Token Laravel
                });

                successCount += batchIds.length;
                console.log(`Batch ${i+1} OK`);

            } catch (error) {
                console.error(`Batch ${i+1} Gagal`, error);
                failCount += batchIds.length;
            }

            // Update Progress Bar
            const percent = Math.round(((i + 1) / totalBatches) * 100);
            progressBar.style.width = percent + '%';
            progressText.innerText = `Memproses: ${percent}% (${successCount} Berhasil, ${failCount} Gagal)`;
        }

        // --- SELESAI ---
        btnSubmit.disabled = false;
        
        await Swal.fire({
            title: 'Selesai!',
            text: `Proses selesai. Berhasil: ${successCount}, Gagal: ${failCount}. Halaman akan dimuat ulang.`,
            icon: failCount > 0 ? 'warning' : 'success'
        });

        // Reload halaman untuk update status tabel
        window.location.reload();
    });
</script>
@endsection