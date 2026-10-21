@extends('layouts.admin.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h4>Daftar User (Hanya yang Mengisi Biodata)</h4>
            <span class="badge bg-primary">{{ $users->total() }} Total Pengguna</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama (User)</th>
                            <th>Email</th>
                            <th>NIK</th>
                            <th>Nama Lengkap (Biodata)</th>
                            <th>Asal Sekolah</th>
                            <th>Status Bio</th>
                            <th>Tanggal Isi</th>
                        </tr>
                    </thead>
                   <tbody>
    @forelse($users as $index => $user)
    <tr>
        <td>{{ $users->firstItem() + $index }}</td>
        <td>{{ $user->name }}</td>
        <td>{{ $user->email }}</td>
        
        {{-- Gunakan Null Coalescing atau Optional Operator --}}
        <td>
            @if($user->biodata)
                <code>{{ $user->biodata->nik }}</code>
            @else
                <span class="text-gray-400 italic">Data Kosong</span>
            @endif
        </td>

        <td>{{ $user->biodata->nama_lengkap ?? '-' }}</td>
        <td>{{ $user->biodata->asal_sekolah ?? '-' }}</td>

        <td>
            @if($user->biodata)
                <span class="badge {{ $user->biodata->status_bio == 'completed' ? 'bg-success' : 'bg-warning' }}">
                    {{ strtoupper($user->biodata->status_bio) }}
                </span>
            @else
                <span class="badge bg-danger">BELUM MENGISI</span>
            @endif
        </td>

        <td>{{ $user->biodata ? $user->biodata->created_at->format('d M Y H:i') : '-' }}</td>
    </tr>
    @empty
    <tr>
        <td colspan="8" class="text-center">Tidak ada user dalam kategori ini.</td>
    </tr>
    @endforelse
</tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection