@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Rekomendasi Pemilihan Prodi Berdasarkan Nilai Quiz</h3>
                </div>
                <div class="card-body">
                    <!-- Statistik -->
                    <div class="row mb-4">
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-user-graduate"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Siswa</span>
                                    <span class="info-box-number">{{ $stats['total_students'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-star"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">High Achievers</span>
                                    <span class="info-box-number">{{ $stats['high_achievers'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Medium Achievers</span>
                                    <span class="info-box-number">{{ $stats['medium_achievers'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-primary">
                                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Kecocokan Prodi</span>
                                    <span class="info-box-number">{{ $stats['matched_recommendations'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Rekomendasi -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Siswa</th>
                                    <th>Nilai Terbaik</th>
                                    <th>Score Final</th>
                                    <th>Rekomendasi Prodi</th>
                                    <th>Pilihan User</th>
                                    <th>Kecocokan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attempts as $attempt)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $attempt->moodleUser->masterUser->user->name ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $attempt->moodleUser->username }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $attempt->sumgrades }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $attempt->program_recommendation['score'] ?? 0 }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $attempt->program_recommendation['level'] ?? 'unknown' }}</strong>
                                        <br>
                                        <small>{{ implode(', ', $attempt->program_recommendation['programs'] ?? []) }}</small>
                                    </td>
                                    <td>
                                        @if($attempt->moodleUser->masterUser->ereg)
                                            {{ $attempt->moodleUser->masterUser->ereg->jurusan1 }}<br>
                                            {{ $attempt->moodleUser->masterUser->ereg->jurusan2 }}
                                        @else
                                            <span class="text-danger">Belum memilih</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($attempt->ereg_comparison['match'])
                                            <span class="badge bg-success">Cocok</span>
                                            <br>
                                            <small>{{ implode(', ', $attempt->ereg_comparison['matched_programs']) }}</small>
                                        @else
                                            <span class="badge bg-warning">Tidak Cocok</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.moodle.quiz-attempts.show', $attempt->id) }}" 
                                           class="btn btn-sm btn-info">Detail</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $attempts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
<script src="{{ asset('assets/js/bundle.js') }}"></script>