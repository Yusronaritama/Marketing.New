@extends('layouts.admin.app')

@section('title', 'Edit Verifikasi Farmasi')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Edit Verifikasi</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.farmasi.index') }}">Verifikasi Farmasi</a></li>
                        <li class="breadcrumb-item active">Edit Verifikasi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Edit Verifikasi</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Informasi:</strong> Data dari Master Ereg tidak dapat diubah. Hanya status verifikasi dan keterangan yang dapat diperbarui.
                    </div>

                    <!-- Data dari Master Ereg (READONLY) -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0">Data dari Master Ereg (Tidak Dapat Diubah)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <p><strong>Master Ereg ID:</strong> {{ $masterEreg->id ?? '-' }}</p>
                                            <p><strong>Nama:</strong> {{ $masterEreg->nama ?? '-' }}</p>
                                            <p><strong>No. HP:</strong> {{ $masterEreg->phone ?? '-' }}</p>
                                        </div>
                                        <div class="col-md-3">
                                            <p><strong>Prodi Pilihan 1:</strong> 
                                                {{ isset($masterEreg->jurusan1) && $masterEreg->jurusan1 ? \App\Http\Controllers\Admin\FarmasiController::getProdiNameStatic($masterEreg->jurusan1) : '-' }}
                                            </p>
                                            <p><strong>Prodi Pilihan 2:</strong> 
                                                {{ isset($masterEreg->jurusan2) && $masterEreg->jurusan2 ? \App\Http\Controllers\Admin\FarmasiController::getProdiNameStatic($masterEreg->jurusan2) : '-' }}
                                            </p>
                                        </div>
                                        <div class="col-md-3">
                                            <p><strong>Jalur Pendaftaran:</strong> {{ $masterEreg->jalur_pendaftaran ?? '-' }}</p>
                                            <p><strong>Status Ereg:</strong> 
                                                <span class="badge bg-{{ $masterEreg->status == 'completed' ? 'success' : 'warning' }}">
                                                    {{ $masterEreg->status ?? '-' }}
                                                </span>
                                            </p>
                                        </div>
                                        <div class="col-md-3">
                                            <p><strong>Verifikator Sebelumnya:</strong> {{ $verification->nama_verifikator ?? '-' }}</p>
                                            <p><strong>Tanggal Verifikasi:</strong> {{ $verification->tanggal_verifikasi ? $verification->tanggal_verifikasi->format('d/m/Y H:i') : '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.farmasi.update', $verification->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <!-- Hanya input yang bisa diubah oleh admin -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status Verifikasi <span class="text-danger">*</span></label>
                                    <select name="status_verifikasi" class="form-select" required>
                                        <option value="menunggu" {{ old('status_verifikasi', $verification->status_verifikasi) == 'menunggu' ? 'selected' : '' }}>
                                            Menunggu
                                        </option>
                                        <option value="diverifikasi" {{ old('status_verifikasi', $verification->status_verifikasi) == 'diverifikasi' ? 'selected' : '' }}>
                                            Diverifikasi
                                        </option>
                                        <option value="ditolak" {{ old('status_verifikasi', $verification->status_verifikasi) == 'ditolak' ? 'selected' : '' }}>
                                            Ditolak
                                        </option>
                                    </select>
                                    @error('status_verifikasi')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Verifikator</label>
                                    <input type="text" class="form-control" value="{{ Auth::user()->name }}" readonly>
                                    <small class="text-muted">Otomatis terisi dengan nama Anda</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Keterangan</label>
                                    <textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan', $verification->keterangan) }}</textarea>
                                    @error('keterangan')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.farmasi.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back"></i> Kembali
                            </a>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.farmasi.show', $verification->id) }}" class="btn btn-info">
                                    <i class="bx bx-show"></i> Lihat Detail
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save"></i> Update Verifikasi
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection