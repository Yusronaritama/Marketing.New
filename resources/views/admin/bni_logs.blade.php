@extends('layouts.admin.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">BNI Payment Sync (Direct Inquiry)</h5>
            <div>
                <a href="{{ route('bni.sync_all') }}" class="btn btn-success btn-sm" onclick="return confirm('Mulai sinkronisasi semua data dengan server BNI?')">
                    <i class="bi bi-arrow-repeat"></i> Sinkron Semua dari BNI
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('info'))
                <div class="alert alert-info">{{ session('info') }}</div>
            @endif

            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Nama Mahasiswa</th>
                        <th>VA Number</th>
                        <th>Status Database</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        // Ambil data user untuk ditampilkan di tabel
                        $displayUsers = \App\Models\User::whereNotNull('va_number')->orderBy('updated_at', 'desc')->get(); 
                    @endphp
                    @forelse($displayUsers as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td><code class="fw-bold">{{ $user->va_number }}</code></td>
                        <td>
                            @if($user->va_status == 'PAID')
                                <span class="badge bg-success">PAID / LUNAS</span>
                            @else
                                <span class="badge bg-warning text-dark">PENDING</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-search"></i> Cek BNI
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">Tidak ada data VA.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection