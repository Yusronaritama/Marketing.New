<!DOCTYPE html>
<html>
<head>
    <title>Informasi Akun - Universitas Buana Perjuangan Karawang</title>
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
            padding: 25px 20px; 
            text-align: center; 
            border-bottom: 4px solid #dc2626;
        }
        .logo {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .sub-logo {
            font-size: 12px;
            opacity: 0.9;
        }
        .content { 
            padding: 30px; 
            background: white;
        }
        .info-box { 
            background: #f0f9ff; 
            border-left: 4px solid #1e40af; 
            padding: 20px; 
            margin: 20px 0; 
            border-radius: 6px;
            border: 1px solid #e0f2fe;
        }
        .account-detail {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
        }
        .button { 
            background: linear-gradient(135deg, #1e40af, #1e3a8a);
            color: white; 
            padding: 14px 32px; 
            text-decoration: none; 
            border-radius: 6px; 
            display: inline-block; 
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
            border: none;
        }
        .button:hover {
            background: linear-gradient(135deg, #1e3a8a, #1e40af);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
        }
        .footer { 
            text-align: center; 
            margin-top: 25px; 
            font-size: 12px; 
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
            font-size: 13px;
            border-left: 4px solid #dc2626;
        }
        .highlight {
            background: #1e40af;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
        }
        .detail-item {
            margin: 8px 0;
            padding: 5px 0;
        }
        .detail-label {
            font-weight: bold;
            color: #1e3a8a;
            display: inline-block;
            width: 120px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">UNIVERSITAS BUANA PERJUANGAN KARAWANG</div>
            <div class="sub-logo">Sistem Informasi Mahasiswa</div>
            <h1 style="margin: 15px 0 0 0; font-size: 24px; font-weight: 400; color: white;">
                Informasi Akun Mahasiswa
            </h1>
        </div>
        
        <div class="content">
            <p>Halo <strong style="color: #1e3a8a;">{{ $name }}</strong>,</p>
            
            <p>Berikut adalah informasi akun Anda di Sistem Informasi Mahasiswa Universitas Buana Perjuangan Karawang:</p>
            
            <div class="info-box">
                <h3 style="margin-top: 0; color: #1e40af; font-size: 16px;">🎓 Detail Akun Mahasiswa</h3>
                
                <div class="account-detail">
                    <div class="detail-item">
                        <span class="detail-label">Nama:</span>
                        <span>{{ $name }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">TRX ID:</span>
                        <span class="highlight">{{ $trx_id }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Virtual Account:</span>
                        <span class="highlight">{{ $va_number }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Status:</span>
                        <span style="color: #16a34a;">● Akun Aktif</span>
                    </div>
                </div>
            </div>

            <div class="security-note">
                <strong>🔐 KEAMANAN AKUN:</strong><br>
                Untuk keamanan akun Anda, kami tidak mengirimkan password melalui email. 
                Silakan reset password Anda dengan mengklik tombol di bawah ini.
            </div>

            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ $resetUrl }}" class="button">
                    <span style="color: white;"> Reset Password</span>
                </a>
            </div>

            <div style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: 6px; padding: 15px; margin: 20px 0;">
                <h4 style="margin: 0 0 10px 0; color: #d97706; font-size: 14px;">📋 Informasi Penting</h4>
                <p style="margin: 5px 0; font-size: 13px;">
                    <strong>⏰ Masa Berlaku:</strong> Link reset password akan kadaluarsa dalam <strong>24 jam</strong>
                </p>
                <p style="margin: 5px 0; font-size: 13px;">
                    <strong>🎓 Sistem:</strong> Sistem Informasi Mahasiswa UBP Karawang
                </p>
                <p style="margin: 5px 0; font-size: 13px;">
                    <strong>📞 Bantuan:</strong> Hubungi IT Support jika mengalami kendala
                </p>
            </div>

            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 15px; margin: 20px 0;">
                <h4 style="margin: 0 0 10px 0; color: #16a34a; font-size: 14px;">💡 Tips Keamanan</h4>
                <ul style="margin: 0; padding-left: 20px; font-size: 13px;">
                    <li>Gunakan password yang kuat dan unik</li>
                    <li>Jangan bagikan informasi akun kepada siapapun</li>
                    <li>Segera ganti password jika merasa ada aktivitas mencurigakan</li>
                </ul>
            </div>

            <p style="font-size: 13px; color: #6b7280;">
                <strong>Perhatian:</strong> Jika Anda tidak meminta reset password, abaikan email ini dan 
                pastikan akun Anda dalam keadaan aman.
            </p>
            
            <p>Terima kasih,</p>
            <p style="margin-top: 5px;">
                <strong>Tim IT Support<br>
                Universitas Buana Perjuangan Karawang</strong>
            </p>
        </div>
        
        <div class="footer">
            <p style="margin: 0 0 8px 0; font-weight: bold; color: #1e3a8a;">
                UNIVERSITAS BUANA PERJUANGAN KARAWANG
            </p>
            <p style="margin: 0 0 10px 0; font-size: 11px; color: #6b7280;">
                Jl. HS. Ronggo Waluyo, Puseurjaya, Kec. Telukjambe Tim., Kabupaten Karawang, Jawa Barat 41361<br>
                Email: it.support@ubpkarawang.ac.id | Telp: (0267) 1234567
            </p>
            <p style="margin: 0; font-size: 11px; color: #9ca3af;">
                Email ini dikirim secara otomatis. Mohon tidak membalas email ini.<br>
                &copy; {{ date('Y') }} Universitas Buana Perjuangan Karawang. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>