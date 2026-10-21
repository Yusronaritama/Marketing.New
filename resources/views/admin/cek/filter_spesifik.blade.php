@extends('layouts.admin.app')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Kelulusan (Nilai 50)</h1>
        <a href="{{ route('admin.cek.unknown') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success border-left-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Section Filter & Cari --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-body">
                    <form action="{{ url()->current() }}" method="GET">
                        <div class="row">
                            {{-- Input Cari Nama --}}
                            <div class="col-md-4 mb-3">
                                <label class="small font-weight-bold">Cari Mahasiswa:</label>
                                <div class="input-group">
                                    <input type="text" name="nama" class="form-control" placeholder="Nama atau Email..." value="{{ request('nama') }}">
                                </div>
                            </div>

                            {{-- Filter Biodata --}}
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Status Biodata:</label>
                                <select name="biodata" class="form-control">
                                    <option value="">Semua</option>
                                    <option value="sudah" {{ request('biodata') == 'sudah' ? 'selected' : '' }}>Sudah Isi</option>
                                    <option value="belum" {{ request('biodata') == 'belum' ? 'selected' : '' }}>Belum Isi</option>
                                </select>
                            </div>

                            {{-- Filter Ereg --}}
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Status e-Reg:</label>
                                <select name="ereg" class="form-control">
                                    <option value="">Semua</option>
                                    <option value="sudah" {{ request('ereg') == 'sudah' ? 'selected' : '' }}>Sudah Registrasi</option>
                                    <option value="belum" {{ request('ereg') == 'belum' ? 'selected' : '' }}>Belum Registrasi</option>
                                </select>
                            </div>

                            {{-- Tombol Aksi --}}
                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-search fa-sm"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Utama --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Data Lulusan Terpilih</h6>
            <span class="badge badge-danger">Total: {{ $lulusanFilter->count() }} Data</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th>Nama Mahasiswa</th>
                            <th>No. Telp</th>
                            <th>Prodi Lulus</th>
                            <th class="text-center">Nilai</th>
                            <th class="text-center">Biodata</th>
                            <th class="text-center">e-Reg</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lulusanFilter as $lulus)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="font-weight-bold text-dark">{{ $lulus->nama_lengkap }}</div>
                                    <div class="small text-muted">{{ $lulus->email }}</div>
                                </td>
                                <td>{{ $lulus->phone ?? '-' }}</td>
                                <td><span class="badge badge-info">{{ $lulus->nama_prodi_lulus }}</span></td>
                                <td class="text-center font-weight-bold text-danger">{{ number_format($lulus->nilai_quiz, 0) }}</td>
                                
                                {{-- Status Biodata --}}
                                <td class="text-center">
                                    @if($lulus->biodata_id)
                                        <i class="fas fa-check-circle text-success fa-lg"></i>
                                    @else
                                        <i class="fas fa-times-circle text-danger fa-lg"></i>
                                    @endif
                                </td>

                                {{-- Status e-Reg --}}
                                <td class="text-center">
                                    @if($lulus->ereg_id)
                                        <i class="fas fa-check-circle text-success fa-lg"></i>
                                    @else
                                        <i class="fas fa-times-circle text-danger fa-lg"></i>
                                    @endif
                                </td>

                                {{-- Tombol Hapus --}}
                                <td class="text-center">
                                    <form action="{{ route('admin.cek.destroy', $lulus->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data kelulusan {{ $lulus->nama_lengkap }} ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-circle btn-danger shadow-sm">
                                            <i class="fas fa-trash fa-xs"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-user-slash fa-3x mb-3"></i><br>
                                    Data tidak ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto focus ke input nama jika tidak ada value
        const namaInput = document.querySelector('input[name="nama"]');
        if (namaInput && !namaInput.value) {
            namaInput.focus();
        }
    });
</script>