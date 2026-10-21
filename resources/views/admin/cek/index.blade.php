@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Daftar Lulusan - Prodi Unknown</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Kelulusan Tanpa Prodi</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Master User ID</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>No. Telp / WA</th>
                            <th>Nilai Quiz</th>
                            <th>Status</th>
                            <th>Tanggal Lulus</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lulusanUnknown as $lulus)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $lulus->master_user_id }}</td>
                                <td>{{ $lulus->nama_lengkap }}</td>
                                <td>{{ $lulus->email }}</td>
                                <td>{{ $lulus->phone ?? '-' }}</td>
                                <td>{{ number_format($lulus->nilai_quiz, 2) }}</td>
                                <td>
                                    <span class="badge bg-success text-white">{{ $lulus->status_kelulusan }}</span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($lulus->tanggal_lulus)->format('d-m-Y H:i') }}</td>
                                <td>
                                    {{-- Tombol untuk aksi perbaikan data --}}
                                    <a href="#" class="btn btn-sm btn-warning">Set Prodi</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada data lulusan dengan prodi Unknown.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection