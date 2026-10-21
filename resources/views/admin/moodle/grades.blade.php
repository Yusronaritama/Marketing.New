@extends('layouts.admin.app')

@section('title', 'Sync Nilai Moodle')

@section('content')
<div class="container-fluid">
    
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Cek Nilai Course ID: 2 (AJAX Mode)</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <p>Klik tombol di bawah untuk memulai proses sinkronisasi. Sistem akan memeriksa user satu per satu untuk menghindari Time Out.</p>
            
            <button id="btn-start" class="btn btn-primary btn-lg">
                <i class="fas fa-play"></i> Mulai Pengecekan
            </button>

            <div id="progress-container" class="mt-4" style="display: none;">
                <h5 class="small font-weight-bold">Progress: <span id="progress-text">0/0</span></h5>
                <div class="progress mb-2">
                    <div id="progress-bar" class="progress-bar bg-info" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div id="status-text" class="text-xs text-muted font-italic">Menunggu...</div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-success">Hasil: User yang Memiliki Nilai</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="result-table" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Moodle User ID</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        </tbody>
                </table>
            </div>
            <div id="empty-msg" class="text-center p-3 text-muted" style="display: none;">
                Belum ada data ditemukan.
            </div>
        </div>
    </div>
</div>

@endsection

{{-- 1. KITA BUKA KOMENTAR JQUERY AGAR DILOAD --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        // Debugging: Cek apakah script jalan
        console.log("Script Moodle Grade siap dijalankan.");

        const COURSE_ID = 2; 
        let userList = [];
        let totalUsers = 0;
        let processedCount = 0;
        let foundCount = 0;

        // Gunakan .on('click') untuk memastikan event terikat dengan benar
        $(document).on('click', '#btn-start', function(e) {
            e.preventDefault(); // Mencegah reload jika button ada di dalam form
            console.log("Tombol diklik!"); // Debugging

            // Disable tombol agar tidak diklik 2x
            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memuat User...');
            
            $('#progress-container').show();
            $('#table-body').empty();
            $('#empty-msg').hide();
            
            // Langkah 1: Ambil daftar semua user dulu
            // Perbaikan URL: Gunakan dummy ID '0' lalu replace di JS agar aman dari error route Laravel
            var urlUsers = "{{ route('admin.moodle.api.users', 0) }}".replace('/0', '/' + COURSE_ID);

            $.ajax({
                url: urlUsers,
                type: "GET",
                success: function(response) {
                    console.log("User list didapat:", response); // Debugging
                    userList = response.users;
                    totalUsers = userList.length;
                    processedCount = 0;
                    foundCount = 0;

                    btn.text('Sedang Memproses...');
                    updateProgress();
                    
                    if(totalUsers > 0) {
                        // Langkah 2: Mulai proses recursive user pertama
                        processUser(0); 
                    } else {
                        finishProcess("Tidak ada user terdaftar di course ini.");
                    }
                },
                error: function(xhr) {
                    console.error(xhr);
                    alert('Gagal mengambil daftar user. Cek Console (F12) untuk detail.');
                    btn.prop('disabled', false).html('<i class="fas fa-play"></i> Mulai Pengecekan');
                }
            });
        });

        // Fungsi Recursive
        function processUser(index) {
            if (index >= totalUsers) {
                finishProcess("Selesai! Ditemukan " + foundCount + " user dengan nilai.");
                return;
            }

            let currentUser = userList[index];
            $('#status-text').text(`Mengecek: ${currentUser.fullname} (${index + 1}/${totalUsers})`);

            $.ajax({
                url: "{{ route('admin.moodle.api.check_grade') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    course_id: COURSE_ID,
                    user_id: currentUser.id
                },
                success: function(res) {
                    if (res.has_grade) {
                        foundCount++;
                        let row = `
                            <tr>
                                <td>${currentUser.id}</td>
                                <td class="font-weight-bold">${currentUser.fullname}</td>
                                <td>${currentUser.email}</td>
                                <td class="text-success font-weight-bold">${parseFloat(res.grade).toFixed(2)}</td>
                            </tr>
                        `;
                        $('#table-body').append(row);
                    }
                },
                error: function(err) {
                    console.error("Gagal cek user ID " + currentUser.id, err);
                },
                complete: function() {
                    processedCount++;
                    updateProgress();
                    // Jeda sedikit (50ms) agar browser tidak hang/freeze saat render UI
                    setTimeout(function() {
                        processUser(index + 1);
                    }, 50);
                }
            });
        }

        function updateProgress() {
            let percent = 0;
            if (totalUsers > 0) {
                percent = Math.round((processedCount / totalUsers) * 100);
            }
            $('#progress-bar').css('width', percent + '%').attr('aria-valuenow', percent);
            $('#progress-text').text(`${processedCount} / ${totalUsers} User`);
        }

        function finishProcess(msg) {
            $('#btn-start').prop('disabled', false).html('<i class="fas fa-play"></i> Cek Ulang');
            $('#status-text').text(msg);
            if(foundCount === 0) {
                $('#empty-msg').show();
            }
            alert(msg);
        }
    });
</script>