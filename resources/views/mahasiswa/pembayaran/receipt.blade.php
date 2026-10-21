<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pembayaran - {{ $bill->order_id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Space+Mono:wght@400;700&display=swap');
        
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; }
        .font-mono { font-family: 'Space Mono', monospace; }
        
        /* CSS KHUSUS PRINT */
        @media print {
            body { background: white; -webkit-print-color-adjust: exact; margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .shadow-xl { box-shadow: none !important; }
            .border { border-color: #000 !important; }
            .bg-slate-50 { background-color: #f9fafb !important; }
            .bg-slate-800, .bg-slate-900 { background-color: #1e293b !important; color: white !important; }
            .bg-emerald-600 { background-color: #059669 !important; color: white !important; }
            
            /* Paksa Layout Desktop saat Print Kertas A4 */
            .md\:flex-row { flex-direction: row !important; }
            .md\:text-right { text-align: right !important; }
            .md\:text-left { text-align: left !important; }
            .md\:items-start { align-items: flex-start !important; }
            .md\:items-end { align-items: flex-end !important; }
            
            .print-container { width: 100%; max-width: 100%; margin: 0; border: none; border-radius: 0; }
        }
    </style>
</head>
<body class="flex flex-col items-center min-h-screen p-0 md:p-6 pb-24 md:pb-6">

    {{-- BUTTONS (Floating di bawah untuk Mobile, Di atas untuk Desktop) --}}
    <div class="fixed bottom-0 left-0 w-full bg-white/90 backdrop-blur border-t border-slate-200 p-4 md:static md:w-auto md:bg-transparent md:border-none md:p-0 md:mb-6 z-50 no-print flex justify-center gap-3">
        <a href="{{ route('mahasiswa.dashboard') }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold py-3 px-6 rounded-full shadow-sm transition transform active:scale-95 flex items-center justify-center gap-2 text-sm">
            <i class="fas fa-arrow-left"></i> <span class="hidden md:inline">Kembali</span>
        </a>
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-full shadow-lg transition transform active:scale-95 flex items-center gap-2 text-sm">
            <i class="fas fa-print"></i> Cetak Bukti
        </button>
    </div>

    {{-- RECEIPT CONTAINER --}}
    <div class="print-container bg-white w-full max-w-3xl shadow-none md:shadow-xl rounded-none md:rounded-2xl overflow-hidden relative min-h-screen md:min-h-0">
        
        {{-- WATERMARK LUNAS --}}
        @if($bill->status == 'paid' || $bill->status == 'lunas')
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 pointer-events-none opacity-10 border-4 md:border-8 border-emerald-600 text-emerald-600 px-4 md:px-10 py-2 md:py-4 text-6xl md:text-9xl font-black uppercase -rotate-12 select-none whitespace-nowrap z-0">
            PAID
        </div>
        @endif

        {{-- HEADER --}}
        <div class="bg-slate-900 text-white p-6 md:p-8 relative z-10">
            <div class="flex flex-col md:flex-row justify-between items-center md:items-start gap-6 md:gap-0">
                
                {{-- Kiri: Logo --}}
                <div class="text-center md:text-left">
                    <div class="flex flex-col md:flex-row items-center gap-3 mb-3 md:mb-4">
                        <img src="{{ asset('images/logo_ubp.png') }}" class="h-10 md:h-12 w-auto bg-white rounded p-1" alt="Logo UBP">
                        <div>
                            <h1 class="text-base md:text-lg font-bold leading-tight">UNIVERSITAS BUANA<br>PERJUANGAN KARAWANG</h1>
                        </div>
                    </div>
                    <p class="text-[10px] md:text-xs text-slate-400 max-w-xs mx-auto md:mx-0">
                        Jl. HS.Ronggo Waluyo, Puseurjaya, Telukjambe Timur, Karawang, Jawa Barat 41361
                    </p>
                </div>

                {{-- Kanan: Judul Receipt --}}
                <div class="text-center md:text-right w-full md:w-auto border-t border-slate-700 pt-4 md:border-none md:pt-0">
                    <h2 class="text-2xl md:text-3xl font-black uppercase tracking-widest text-emerald-400">RECEIPT</h2>
                    <p class="font-mono text-xs md:text-sm text-slate-400 mt-1">#{{ $bill->trx_id ?? $bill->order_id }}</p>
                    
                    <div class="mt-3 md:mt-4 inline-block bg-slate-800 px-4 py-2 rounded border border-slate-700">
                        <p class="text-[9px] md:text-[10px] text-slate-400 uppercase tracking-wider mb-1">Tanggal Pembayaran</p>
                        <p class="font-mono font-bold text-xs md:text-sm">
                            {{ $bill->updated_at ? \Carbon\Carbon::parse($bill->updated_at)->format('d F Y, H:i') : '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- BODY --}}
        <div class="p-5 md:p-8 relative z-10">
            
            {{-- INFO MAHASISWA & TAGIHAN --}}
            <div class="flex flex-col md:flex-row justify-between gap-6 md:gap-8 mb-8 border-b border-slate-200 pb-8">
                {{-- Kiri --}}
                <div class="flex-1 text-center md:text-left">
                    <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Diterima Dari</h3>
                    <p class="text-base md:text-lg font-bold text-slate-900 uppercase">{{ $bill->customer_name }}</p>
                    <p class="text-xs md:text-sm text-slate-600">{{ $user->email }}</p>
                    <p class="text-xs md:text-sm text-slate-600 mt-1">{{ $bill->phone ?? $user->no_hp }}</p>
                </div>

                {{-- Kanan --}}
                <div class="flex-1 text-center md:text-right bg-slate-50 p-4 rounded-lg md:bg-transparent md:p-0">
                    <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Metode Pembayaran</h3>
                    <p class="text-base md:text-lg font-bold text-slate-900">{{ $metode }}</p>
                    <p class="text-xs md:text-sm text-slate-600 capitalize">Via {{ $bill->payment_channel ?? 'Virtual Account' }}</p>
                    <p class="font-mono text-xs md:text-sm text-slate-500 mt-2 md:mt-1 bg-white md:bg-slate-100 border md:border-none px-2 py-1 rounded inline-block">
                        VA: {{ $bill->virtual_account }}
                    </p>
                </div>
            </div>

            {{-- TABEL RINCIAN --}}
            <div class="mb-8">
                <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Rincian Pembayaran</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[300px]">
                        <thead>
                            <tr class="border-b-2 border-slate-800">
                                <th class="py-2 md:py-3 text-xs md:text-sm font-black text-slate-800 uppercase">Deskripsi</th>
                                <th class="py-2 md:py-3 text-xs md:text-sm font-black text-slate-800 uppercase text-right">Tenor</th>
                                <th class="py-2 md:py-3 text-xs md:text-sm font-black text-slate-800 uppercase text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-slate-200">
                                <td class="py-3 md:py-4 text-slate-700 font-medium text-xs md:text-base pr-2">
                                    Pembayaran UKT
                                    <span class="block text-[10px] text-slate-500 mt-1 truncate max-w-[150px] md:max-w-none">Ref: {{ $bill->order_id }}</span>
                                </td>
                                <td class="py-3 md:py-4 text-slate-600 text-right capitalize text-xs md:text-base">{{ $bill->tenor_pembayaran ?? 'Reguler' }}</td>
                                <td class="py-3 md:py-4 text-slate-900 font-bold text-right font-mono text-xs md:text-base whitespace-nowrap">Rp {{ number_format($bill->amount, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="pt-4 md:pt-6 text-right text-xs md:text-sm font-bold text-slate-500 uppercase tracking-widest">Total</td>
                                <td class="pt-4 md:pt-6 text-right text-lg md:text-2xl font-black text-slate-900 font-mono">Rp {{ number_format($bill->amount, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- FOOTER SIGNATURE --}}
            <div class="flex flex-col md:flex-row justify-between items-center md:items-end mt-8 md:mt-12 pt-8 md:pt-12 border-t md:border-none border-dashed border-slate-300 gap-8 md:gap-0">
                <div class="text-[10px] md:text-xs text-slate-400 max-w-sm text-center md:text-left order-2 md:order-1">
                    <p class="mb-1"><strong>Catatan:</strong></p>
                    <p>Bukti pembayaran ini sah dan diterbitkan secara elektronik oleh sistem PMB Universitas Buana Perjuangan Karawang.</p>
                </div>
                <div class="text-center order-1 md:order-2">
                    {{-- QR Code --}}
                    <div class="mb-2 flex justify-center opacity-80">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ $bill->trx_id }}" alt="QR Validation" class="w-16 h-16 md:w-20 md:h-20">
                    </div>
                    <p class="text-[9px] md:text-[10px] font-bold text-slate-400 uppercase tracking-widest">Authorized Signature</p>
                    <p class="text-xs font-bold text-slate-800 mt-1">Finance Dept.</p>
                </div>
            </div>

        </div>
        
        {{-- DECORATION BOTTOM --}}
        <div class="h-2 w-full bg-slate-900 mt-4 md:mt-0"></div>
    </div>
    
    {{-- Spacer untuk tombol floating di mobile --}}
    <div class="h-20 md:hidden no-print"></div>

</body>
</html>