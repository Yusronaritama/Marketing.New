@extends('layouts.admin.app')

@section('content')
<style>
    /* Menyelaraskan dengan font dan warna di sidebar */
    .content-wrapper {
        padding: 2rem;
        background-color: #f8f9fa; /* Abu-abu sangat muda agar card terlihat menonjol */
        min-height: 100vh;
    }

    .card-custom {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        background: #fff;
    }

    .card-custom .card-header {
        background-color: #fff;
        border-bottom: 1px solid #f0f0f0;
        padding: 1.5rem;
        border-radius: 12px 12px 0 0;
    }

    .card-title-custom {
        color: #4e73df; /* Warna biru sesuai logo UBP */
        font-weight: 700;
        margin: 0;
        font-size: 1.1rem;
    }

    .form-label {
        font-weight: 600;
        color: #5a5c69;
        margin-bottom: 0.5rem;
    }

    /* Styling Input agar serupa dengan elemen modern di sidebar */
    .form-control-custom {
        display: block;
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #6e707e;
        background-color: #fff;
        border: 1px solid #d1d3e2;
        border-radius: 8px;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    }

    .form-control-custom:focus {
        border-color: #bac8f3;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    /* Styling Tombol */
    .btn-save {
        background-color: #4e73df;
        border: none;
        padding: 0.6rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-save:hover {
        background-color: #2e59d9;
        transform: translateY(-1px);
    }

    .btn-back {
        background-color: #eaecf4;
        color: #5a5c69;
        border: none;
        padding: 0.6rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        margin-right: 10px;
    }

    .btn-back:hover {
        background-color: #d1d3e2;
        color: #3a3b45;
    }
</style>

<div class="content-wrapper">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card card-custom">
                    <div class="card-header">
                        <h6 class="card-title-custom">
                            <i class="fas fa-clipboard-check mr-2"></i> Verifikasi Lanjutan Farmasi - {{ $user->name }}
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.profile-lengkap.store-farmasi-verification', $user->id) }}" method="POST">
                            @csrf
                            
                            <div class="mb-4">
                                <label class="form-label">Status Verifikasi</label>
                                <select name="status_verifikasi" class="form-control-custom" required>
                                    <option value="pending" {{ ($existingVerification->status_verifikasi ?? '') == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                                    <option value="verified" {{ ($existingVerification->status_verifikasi ?? '') == 'verified' ? 'selected' : '' }}>✅ Verified (Lulus Seleksi Farmasi)</option>
                                    <option value="rejected" {{ ($existingVerification->status_verifikasi ?? '') == 'rejected' ? 'selected' : '' }}>❌ Rejected (Tidak Lulus Seleksi Farmasi)</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Keterangan / Catatan</label>
                                <textarea name="keterangan" class="form-control-custom" rows="5" placeholder="Masukkan alasan atau catatan verifikasi di sini...">{{ $existingVerification->keterangan ?? '' }}</textarea>
                            </div>

                            <div class="d-flex justify-content-start mt-4">
                                <a href="{{ route('admin.profile-lengkap.index') }}" class="btn btn-back">
                                    Kembali
                                </a>
                                <button type="submit" class="btn btn-primary btn-save">
                                    Simpan Verifikasi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection