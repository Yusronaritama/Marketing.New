@extends('layouts.admin.app')

@section('title', 'Buat Verifikasi Farmasi')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Buat Verifikasi Baru</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.farmasi.index') }}">Verifikasi Farmasi</a></li>
                        <li class="breadcrumb-item active">Buat Verifikasi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Data Peserta</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Informasi:</strong> Data akan diambil otomatis dari Master Ereg. Hanya status verifikasi dan keterangan yang dapat diubah.
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Informasi Peserta</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Nama Lengkap</th>
                                    <td>{{ $lulus->nama_lengkap }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $lulus->email }}</td>
                                </tr>
                                <tr>
                                    <th>Username</th>
                                    <td>{{ $lulus->username }}</td>
                                </tr>
                                <tr>
                                    <th>Master User ID</th>
                                    <td>{{ $lulus->master_user_id }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Informasi Kelulusan</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Prodi Lulus</th>
                                    <td>{{ $lulus->nama_prodi_lulus }}</td>
                                </tr>
                                <tr>
                                    <th>Pilihan Lulus</th>
                                    <td>{{ $lulus->pilihan_lulus_text }}</td>
                                </tr>
                                <tr>
                                    <th>Nilai Quiz</th>
                                    <td>{{ number_format($lulus->nilai_quiz, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Status Kelulusan</th>
                                    <td>
                                        <span class="badge bg-{{ $lulus->status_color }}">
                                            {{ $lulus->status_kelulusan }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
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
                                            <p><strong>Email:</strong> {{ $masterEreg->gmail ?? '-' }}</p>
                                        </div>
                                        <div class="col-md-3">
                                            <p><strong>No. HP:</strong> {{ $masterEreg->phone ?? '-' }}</p>
                                            <p><strong>Phone (Alternatif):</strong> {{ $masterEreg->phone ?? '-' }}</p>
                                            <p><strong>Jalur Pendaftaran:</strong> {{ $masterEreg->jalur_pendaftaran ?? '-' }}</p>
                                        </div>
                                        <div class="col-md-3">
                                            @php
                                                $jurusan1Name = isset($masterEreg->jurusan1) && $masterEreg->jurusan1 ? \App\Http\Controllers\Admin\FarmasiController::getProdiNameStatic($masterEreg->jurusan1) : '-';
                                                $jurusan2Name = isset($masterEreg->jurusan2) && $masterEreg->jurusan2 ? \App\Http\Controllers\Admin\FarmasiController::getProdiNameStatic($masterEreg->jurusan2) : '-';
                                            @endphp
                                            <p><strong>Prodi Pilihan 1:</strong> {{ $jurusan1Name }}</p>
                                            <p><strong>Prodi Pilihan 2:</strong> {{ $jurusan2Name }}</p>
                                            <p><strong>Status Pekerjaan:</strong> {{ $masterEreg->status_pekerjaan ?? '-' }}</p>
                                        </div>
                                        <div class="col-md-3">
                                            <p><strong>Jurusan Lulusan:</strong> {{ $masterEreg->jurusan_lulusan ?? '-' }}</p>
                                            <p><strong>Status Ereg:</strong> 
                                                <span class="badge bg-{{ $masterEreg->status == 'completed' ? 'success' : 'warning' }}">
                                                    {{ $masterEreg->status ?? '-' }}
                                                </span>
                                            </p>
                                            <p><strong>Step Completed:</strong> {{ $masterEreg->step_completed ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <form method="POST" action="{{ route('admin.farmasi.store', $lulus->id) }}">
                        @csrf
                        
                        <!-- Data dari Master Ereg (HIDDEN FIELDS) -->
                        <input type="hidden" name="master_ereg_id" value="{{ $autoFillData['master_ereg_id'] }}">
                        <input type="hidden" name="nomer_hp" value="{{ $autoFillData['nomer_hp'] }}">
                        <input type="hidden" name="phone" value="{{ $autoFillData['phone'] }}">
                        <input type="hidden" name="prodi_pilihan1" value="{{ $autoFillData['prodi_pilihan1'] }}">
                        <input type="hidden" name="prodi_pilihan2" value="{{ $autoFillData['prodi_pilihan2'] }}">
                        
                        <!-- Hanya input yang bisa diubah oleh admin -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status Verifikasi <span class="text-danger">*</span></label>
                                    <select name="status_verifikasi" class="form-select" required>
                                        <option value="">-- Pilih Status --</option>
                                        <option value="menunggu" {{ old('status_verifikasi') == 'menunggu' ? 'selected' : '' }}>
                                            Menunggu
                                        </option>
                                        <option value="diverifikasi" {{ old('status_verifikasi') == 'diverifikasi' ? 'selected' : '' }}>
                                            Diverifikasi
                                        </option>
                                        <option value="ditolak" {{ old('status_verifikasi') == 'ditolak' ? 'selected' : '' }}>
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
                                    <textarea name="keterangan" class="form-control" rows="3" placeholder="Masukkan keterangan jika diperlukan...">{{ old('keterangan') }}</textarea>
                                    @error('keterangan')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Summary Data yang Akan Disimpan -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Ringkasan Data yang Akan Disimpan</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <tr>
                                                <th width="30%">Master Ereg ID</th>
                                                <td>{{ $autoFillData['master_ereg_id'] }}</td>
                                            </tr>
                                            <tr>
                                                <th>No. HP</th>
                                                <td>{{ $autoFillData['nomer_hp'] }}</td>
                                            </tr>
                                            <tr>
                                                <th>Phone (Alternatif)</th>
                                                <td>{{ $autoFillData['phone'] }}</td>
                                            </tr>
                                            <tr>
                                                <th>Prodi Pilihan 1</th>
                                                <td>{{ $autoFillData['prodi_pilihan1'] }}</td>
                                            </tr>
                                            <tr>
                                                <th>Prodi Pilihan 2</th>
                                                <td>{{ $autoFillData['prodi_pilihan2'] }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.farmasi.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save"></i> Simpan Verifikasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection