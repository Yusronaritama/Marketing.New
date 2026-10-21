<!DOCTYPE html>
<html>
<head>
    <title>Reset Password - Universitas Buana Perjuangan Karawang</title>
    <style>
        body { 
            font-family: 'Arial', sans-serif; 
            line-height: 1.6; 
            color: #333; 
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #e0e0e0;
        }
        .header { 
            background: linear-gradient(135deg, #1e3a8a, #1e40af);
            color: white; 
            padding: 30px 20px; 
            text-align: center; 
            border-bottom: 4px solid #dc2626;
        }
        .logo {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #ffffff;
        }
        .sub-logo {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 15px;
        }
        .content { 
            padding: 35px 30px; 
        }
        .info-box { 
            background: #f0f9ff; 
            border-left: 4px solid #1e40af; 
            padding: 20px; 
            margin: 25px 0; 
            border-radius: 6px;
            border: 1px solid #e0f2fe;
        }
        .button { 
            background: linear-gradient(135deg, #1e40af, #1e3a8a);
            color: white; 
            padding: 14px 32px; 
            text-decoration: none; 
            border-radius: 6px; 
            display: inline-block; 
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .button:hover {
            background: linear-gradient(135deg, #1e3a8a, #1e40af);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
        }
        .footer { 
            text-align: center; 
            margin-top: 30px; 
            font-size: 13px; 
            color: #6b7280;
            padding: 20px;
            border-top: 1px solid #e5e7eb;
            background: #f8fafc;
        }
        .security-note {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            font-size: 14px;
            border-left: 4px solid #dc2626;
        }
        .steps {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #16a34a;
        }
        .step {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            padding: 8px 0;
        }
        .step-number {
            background: #1e40af;
            color: white;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .contact-info {
            background: #fffbeb;
            border: 1px solid #fef3c7;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #d97706;
        }
        .univ-colors {
            display: flex;
            margin: 15px 0;
            gap: 10px;
        }
        .color-swatch {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .blue { background: #1e40af; }
        .dark-blue { background: #1e3a8a; }
        .red { background: #dc2626; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">UNIVERSITAS BUANA PERJUANGAN KARAWANG</div>
            <div class="sub-logo">UBP KARAWANG</div>
            <h1 style="margin: 0; font-size: 26px; font-weight: 400;">Reset Password Akun</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9; font-size: 14px;">Sistem Informasi Mahasiswa</p>
        </div>
        
        <div class="content">
            <p>Halo <strong>{{ $user->name ?? 'Mahasiswa' }}</strong>,</p>
            
            <p>Kami menerima permintaan untuk mereset password akun Sistem Informasi Mahasiswa Universitas Buana Perjuangan Karawang. Berikut adalah link untuk mereset password Anda:</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $resetUrl }}" class="button" >
                    <span style="color: white;">Reset Password</span>
                </a>
            </div>

            <div class="steps">
                <h3 style="margin-top: 0; color: #16a34a; font-size: 16px;">📝 Langkah Reset Password:</h3>
                <div class="step">
                    <div class="step-number">1</div>
                    <span>Klik tombol "Reset Password Saya" di atas</span>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <span>Masukkan password baru Anda (minimal 8 karakter)</span>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <span>Konfirmasi password baru Anda</span>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <span>Login kembali dengan password baru</span>
                </div>
            </div>

            <div class="security-note">
                <strong>🛡️ PERHATIAN KEAMANAN:</strong> 
                <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                    <li>Jika Anda tidak merasa meminta reset password, segera abaikan email ini</li>
                    <li>Jangan bagikan link reset password kepada siapapun</li>
                    <li>Pastikan password baru Anda kuat dan unik</li>
                </ul>
            </div>

            <div class="info-box">
                <h3 style="margin-top: 0; color: #1e40af; font-size: 16px;">📋 Informasi Penting</h3>
                <p><strong>⏰ Masa Berlaku:</strong> Link reset password akan kadaluarsa dalam <strong>60 menit</strong></p>
                <p><strong>🎓 Akun Terkait:</strong> Sistem Informasi Mahasiswa UBP Karawang</p>
                <p><strong>📧 Email:</strong> {{ $user->email ?? 'Tidak tersedia' }}</p>
            </div>

            <p>Terima kasih atas perhatiannya,</p>
            <p style="margin-top: 5px;">
                <strong>Tim IT Support<br>
                Universitas Buana Perjuangan Karawang</strong>
            </p>
        </div>
        
        <div class="footer">
            <p style="margin: 0 0 10px 0;">
                <strong>UNIVERSITAS BUANA PERJUANGAN KARAWANG</strong><br>
                Jl. HS. Ronggo Waluyo, Puseurjaya, Kec. Telukjambe Tim.,<br>
                Kabupaten Karawang, Jawa Barat 41361
            </p>
            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                Email ini dikirim secara otomatis. Mohon tidak membalas email ini.<br>
                &copy; {{ date('Y') }} Universitas Buana Perjuangan Karawang. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>