@extends('layouts.admin.app') {{-- Sesuaikan dengan nama layout admin kamu --}}

@section('content')
<div class="container-fluid" style="padding: 20px;">
    
    <!-- HEADER -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h2 style="margin: 0; font-weight: 700; color: #1A2332;">Pengaturan Gelombang</h2>
            <p style="color: #8492A6; margin-top: 5px;">Kelola periode pendaftaran dan gelombang aktif</p>
        </div>
        <button onclick="openModal('addGelombangModal')" style="padding: 10px 20px; background: linear-gradient(135deg, #5B7FFF 0%, #667EEA 100%); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 10px rgba(91,127,255,0.3);">
            <i class="fas fa-plus mr-2"></i> Tambah Gelombang
        </button>
    </div>

    <!-- NOTIFIKASI -->
    @if(session('success'))
        <div style="padding: 15px; background: #E6F8F3; color: #00D4AA; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="padding: 15px; background: #FFEDED; color: #FF5B5B; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
        </div>
    @endif

    <!-- TABEL DATA -->
    <div style="background: white; border-radius: 12px; border: 1px solid #E8ECF1; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #F8FAFC; border-bottom: 1px solid #E8ECF1; color: #8492A6; font-size: 0.85rem; text-transform: uppercase;">
                    <th style="padding: 15px 20px;">No</th>
                    <th style="padding: 15px 20px;">Nama Gelombang</th>
                    <th style="padding: 15px 20px;">Angka</th>
                    <th style="padding: 15px 20px;">Periode Pendaftaran</th>
                    <th style="padding: 15px 20px;">Status</th>
                    <th style="padding: 15px 20px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($gelombang as $index => $g)
                <tr style="border-bottom: 1px solid #E8ECF1;">
                    <td style="padding: 15px 20px;">{{ $index + 1 }}</td>
                    <td style="padding: 15px 20px; font-weight: 600; color: #1A2332;">{{ $g->nama_gelombang }}</td>
                    <td style="padding: 15px 20px;">{{ $g->angka_gelombang }}</td>
                    <td style="padding: 15px 20px; color: #5B7FFF;">
                        {{ $g->tanggal_mulai ? date('d M Y', strtotime($g->tanggal_mulai)) : '-' }} 
                        <i class="fas fa-arrow-right mx-1" style="font-size: 0.7rem;"></i> 
                        {{ $g->tanggal_selesai ? date('d M Y', strtotime($g->tanggal_selesai)) : '-' }}
                    </td>
                  <td style="padding: 15px 20px;">
                        @if($g->is_active)
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="background: #E6F8F3; color: #00D4AA; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700;">
                                    <i class="fas fa-check-circle mr-1"></i> Sedang Aktif
                                </span>
                                <!-- Tombol Set Nonaktif (Hanya muncul di gelombang yg sedang aktif) -->
                                <form action="{{ route('admin.mastergelombang.set-inactive', $g->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Nonaktifkan {{ $g->nama_gelombang }}? Jika ini dinonaktifkan, pendaftaran mungkin akan error karena tidak ada gelombang aktif.');" style="background: none; border: none; color: #FF5B5B; font-size: 0.8rem; cursor: pointer; text-decoration: underline;" title="Matikan Gelombang Ini">
                                        Nonaktifkan
                                    </button>
                                </form>
                            </div>
                        @else
                            <form action="{{ route('admin.mastergelombang.set-active', $g->id) }}" method="POST" style="margin: 0; display: inline-block;">
                                @csrf
                                <button type="submit" onclick="return confirm('Aktifkan {{ $g->nama_gelombang }}? Gelombang lain akan otomatis nonaktif.');" style="background: #F8FAFC; border: 1px solid #E8ECF1; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; color: #8492A6; cursor: pointer; transition: 0.3s;">
                                    Set Aktif
                                </button>
                            </form>
                        @endif
                    </td>
                    <td style="padding: 15px 20px; text-align: right;">
                        <button onclick="openEditModal({{ $g->id }}, '{{ $g->nama_gelombang }}', '{{ $g->angka_gelombang }}', '{{ $g->tanggal_mulai }}', '{{ $g->tanggal_selesai }}')" style="background: none; border: none; color: #5B7FFF; cursor: pointer; font-size: 1.1rem; margin-right: 10px;" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        @if(!$g->is_active)
                            <form action="{{ route('admin.mastergelombang.destroy', $g->id) }}" method="POST" style="display: inline-block; margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Yakin ingin menghapus {{ $g->nama_gelombang }}?');" style="background: none; border: none; color: #FF5B5B; cursor: pointer; font-size: 1.1rem;" title="Hapus">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding: 30px; text-align: center; color: #8492A6;">Belum ada data gelombang.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

{{-- MODAL TAMBAH & EDIT --}}
@include('admin.mastergelombang.modals')

<script>
    function openModal(id) {
        document.getElementById(id).style.display = 'flex';
    }
    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }
    function openEditModal(id, nama, angka, mulai, selesai) {
        document.getElementById('edit_form').action = "/admin/kelola-gelombang/" + id;
        document.getElementById('edit_nama_gelombang').value = nama;
        document.getElementById('edit_angka_gelombang').value = angka;
        document.getElementById('edit_tanggal_mulai').value = mulai;
        document.getElementById('edit_tanggal_selesai').value = selesai;
        openModal('editGelombangModal');
    }
</script>
@endsection