@extends('layouts.admin.app') 

@section('content')
<div class="container-fluid" style="padding: 20px;">
    
    <div style="margin-bottom: 20px;">
        <h2 style="margin: 0; font-weight: 700; color: #1A2332;">Database Reader (Tabel Login)</h2>
        <p style="color: #8492A6; margin-top: 5px;">Mengekstrak data dari SQL secara spesifik dan memproses penentuan Gelombang otomatis.</p>
    </div>

    @if(session('error'))
        <div style="padding: 15px; background: #FFEDED; color: #FF5B5B; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
        </div>
    @endif
    @if(session('success'))
        <div style="padding: 15px; background: #E6F8F3; color: #00D4AA; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    @if(!isset($parsedData))
        {{-- FORM UPLOAD --}}
        <div style="background: white; border-radius: 12px; border: 1px solid #E8ECF1; box-shadow: 0 4px 15px rgba(0,0,0,0.02); max-width: 600px;">
<form action="{{ route('admin.database.sync-all-legacy') }}" method="POST" style="margin-top: 15px;">
    @csrf
    <button type="submit" onclick="return confirm('Peringatan: Sistem akan menyedot belasan ribu data dari database 2021, 2022, 2024, dan 2025 secara serentak. Pastikan server tidak sedang sibuk. Lanjutkan?');" style="width: 100%; padding: 15px; background: linear-gradient(135deg, #8B5CF6 0%, #6D28D9 100%); color: white; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 10px rgba(139, 92, 246, 0.3);">
        <i class="fas fa-cloud-download-alt mr-2"></i> Mass Backup Seluruh Data Legacy (2021 - 2025)
    </button>
</form>
            <form action="{{ route('admin.database.sync-legacy') }}" method="POST" style="margin-top: 20px;">
			    @csrf
			    <button type="submit" onclick="return confirm('Sistem akan menarik ribuan data secara otomatis dari database pmb2021. Lanjutkan?');" style="width: 100%; padding: 15px; background: linear-gradient(135deg, #10B981 0%, #059669 100%); color: white; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;">
			        <i class="fas fa-sync-alt mr-2"></i> Tarik Otomatis dari Database Lama (pmb2021)
			    </button>
			</form>
            <form action="{{ route('admin.database.process') }}" method="POST" enctype="multipart/form-data" style="padding: 30px;">
                @csrf
                <div style="margin-bottom: 20px;">
                    <label style="font-size: 0.85rem; font-weight: 600; color: #8492A6; margin-bottom: 8px; display: block;">UNGGAH FILE SQL</label>
                    <div style="border: 2px dashed #00D4AA; border-radius: 8px; padding: 40px; text-align: center; background: #F8FAFC;">
                        <i class="fas fa-user-lock" style="font-size: 3rem; color: #00D4AA; margin-bottom: 15px;"></i>
                        <input type="file" name="sql_file" accept=".sql" style="width: 100%; cursor: pointer;" required>
                        <p style="color: #8492A6; font-size: 0.8rem; margin-top: 10px;">Sistem hanya akan mengekstrak data dari tabel <strong>`login`</strong>.</p>
                    </div>
                </div>
                <button type="submit" style="width: 100%; padding: 12px 24px; background: linear-gradient(135deg, #00D4AA 0%, #00B38F 100%); color: white; border: none; border-radius: 8px; font-weight: 700; cursor: pointer;">
                    Baca & Tampilkan Form
                </button>
            </form>
        </div>
    @else
        {{-- HASIL PREVIEW --}}
        <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <a href="{{ route('admin.database.import') }}" class="cyberpunk-btn cyberpunk-btn-secondary" style="padding: 8px 15px; background: #1A2332; color: white; text-decoration: none; border-radius: 6px; font-size: 0.85rem;">
                    <i class="fas fa-arrow-left"></i> Upload File Lain
                </a>
                <span style="margin-left: 15px; color: #8492A6; font-size: 0.85rem;">File: <strong>{{ $fileName }}</strong></span>
            </div>

            {{-- TOMBOL SIMPAN KE DATABASE --}}
            <form action="{{ route('admin.database.store') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" onclick="return confirm('Proses ini akan memakan waktu beberapa saat tergantung jumlah data. Lanjutkan menyimpan {{ number_format($total) }} data ke sistem?');" style="padding: 10px 20px; background: linear-gradient(135deg, #10B981 0%, #059669 100%); color: white; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);">
                    <i class="fas fa-save mr-2"></i> Simpan Permanen ke Database
                </button>
            </form>
        </div>

        @foreach($parsedData as $tableName => $data)
            <div style="background: white; border-radius: 12px; border: 1px solid #E8ECF1; box-shadow: 0 4px 15px rgba(0,0,0,0.02); margin-bottom: 30px; overflow: hidden;">
                <div style="background: #F8FAFC; padding: 15px 20px; border-bottom: 1px solid #E8ECF1; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; color: #1A2332; font-weight: 700; font-size: 1.1rem;">
                        <i class="fas fa-table" style="color: #00D4AA;"></i> Isi Lengkap Tabel: {{ strtoupper($tableName) }}
                    </h3>
                    <span style="background: #E0E7FF; color: #3730A3; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold;">
                        Total: {{ number_format($total) }} Baris Data
                    </span>
                </div>

                <div style="overflow-x: auto; padding: 20px;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 2px solid #E8ECF1;">
                                @foreach($data['columns'] as $columnName)
                                    <th style="padding: 12px; font-size: 0.8rem; text-transform: uppercase; {{ $columnName == '_gelombang' ? 'color: #5B7FFF;' : 'color: #8492A6;' }}">
                                        {{ str_replace('_', ' ', $columnName) }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['rows'] as $row)
                            <tr style="border-bottom: 1px solid #F1F5F9;">
                                @foreach($data['columns'] as $columnName)
                                    <td style="padding: 10px;">
                                        @if($columnName === '_gelombang')
                                            <div style="padding: 6px 10px; border: 1px solid #5B7FFF; border-radius: 4px; background: #E0E7FF; color: #3730A3; font-weight: bold; text-align: center; min-width: 110px;">
                                                {{ $row[$columnName] ?? '-' }}
                                            </div>
                                        @else
                                            <input type="text" readonly value="{{ $row[$columnName] ?? '-' }}" style="padding: 6px 10px; border: 1px solid #E8ECF1; border-radius: 4px; background: #F8FAFC; width: 100%; min-width: 120px; color: #1A2332;">
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                {{-- CYBERPUNK PAGINATION --}}
                @if(isset($paginator) && $paginator->hasPages())
                <div style="padding: 20px; background: #F8FAFC; border-top: 1px solid #E8ECF1; display: flex; flex-direction: column; align-items: center; gap: 10px;">
                    <div style="font-size: 0.85rem; color: #8492A6;">
                        Menampilkan <strong>{{ $paginator->firstItem() }}</strong> sampai <strong>{{ $paginator->lastItem() }}</strong> dari <strong>{{ number_format($paginator->total()) }}</strong> data
                    </div>
                    
                    <div style="display: flex; gap: 5px; flex-wrap: wrap; justify-content: center;">
                        {{-- Prev Button --}}
                        @if ($paginator->onFirstPage())
                            <span style="padding: 8px 12px; background: #E8ECF1; color: #8492A6; border-radius: 6px; font-size: 0.85rem; cursor: not-allowed;">&laquo; Prev</span>
                        @else
                            <a href="{{ $paginator->previousPageUrl() }}" style="padding: 8px 12px; background: white; border: 1px solid #E8ECF1; color: #5B7FFF; border-radius: 6px; text-decoration: none; font-size: 0.85rem; transition: 0.2s;">&laquo; Prev</a>
                        @endif

                        {{-- Page Numbers (Simplified for layout) --}}
                        @foreach ($paginator->links()->elements[0] as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span style="padding: 8px 15px; background: #5B7FFF; color: white; border-radius: 6px; font-weight: bold; font-size: 0.85rem;">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" style="padding: 8px 15px; background: white; border: 1px solid #E8ECF1; color: #1A2332; border-radius: 6px; text-decoration: none; font-size: 0.85rem; transition: 0.2s;">{{ $page }}</a>
                            @endif
                        @endforeach

                        {{-- Next Button --}}
                        @if ($paginator->hasMorePages())
                            <a href="{{ $paginator->nextPageUrl() }}" style="padding: 8px 12px; background: white; border: 1px solid #E8ECF1; color: #5B7FFF; border-radius: 6px; text-decoration: none; font-size: 0.85rem; transition: 0.2s;">Next &raquo;</a>
                        @else
                            <span style="padding: 8px 12px; background: #E8ECF1; color: #8492A6; border-radius: 6px; font-size: 0.85rem; cursor: not-allowed;">Next &raquo;</span>
                        @endif
                    </div>
                </div>
                @endif
                {{-- END PAGINATION --}}

            </div>
        @endforeach

    @endif
</div>
@endsection