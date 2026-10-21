@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Hasil Ujian Online</h4>
                </div>
                <div class="card-body">
                    <div id="quiz-results">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Memuat hasil ujian...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetchQuizResults();
});

function fetchQuizResults() {
    fetch('{{ route("quiz.my-results") }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('quiz-results');
        
        if (data.success) {
            const result = data.data;
            container.innerHTML = `
                <div class="alert alert-success">
                    <h5>Hasil Ujian Berhasil Diambil</h5>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <strong>Nama:</strong> ${result.user_info.name}
                    </div>
                    <div class="col-md-6">
                        <strong>VA Number:</strong> ${result.user_info.va_number}
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6>Hasil Quiz</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Nilai:</strong> ${result.quiz_result.score}
                            </div>
                            <div class="col-md-6">
                                <strong>Status:</strong> 
                                <span class="badge ${result.passing_result.is_passed ? 'bg-success' : 'bg-danger'}">
                                    ${result.passing_result.status}
                                </span>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <strong>Waktu Mulai:</strong> ${formatTimestamp(result.quiz_result.time_start)}
                            </div>
                            <div class="col-md-6">
                                <strong>Waktu Selesai:</strong> ${formatTimestamp(result.quiz_result.time_finish)}
                            </div>
                        </div>
                    </div>
                </div>
                
                ${result.passing_result.keterangan ? `
                <div class="alert alert-info">
                    <strong>Keterangan:</strong> ${result.passing_result.keterangan}
                </div>
                ` : ''}
            `;
        } else {
            container.innerHTML = `
                <div class="alert alert-warning">
                    <h5>Belum Ada Hasil Ujian</h5>
                    <p>${data.message}</p>
                    <button class="btn btn-primary btn-sm" onclick="fetchQuizResults()">
                        Coba Lagi
                    </button>
                </div>
            `;
        }
    })
    .catch(error => {
        const container = document.getElementById('quiz-results');
        container.innerHTML = `
            <div class="alert alert-danger">
                <h5>Terjadi Kesalahan</h5>
                <p>Gagal memuat hasil ujian. Silakan refresh halaman.</p>
            </div>
        `;
        console.error('Error:', error);
    });
}

function formatTimestamp(timestamp) {
    if (!timestamp) return '-';
    const date = new Date(timestamp * 1000);
    return date.toLocaleString('id-ID');
}
</script>
@endsection