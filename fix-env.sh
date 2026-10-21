#!/bin/bash

echo "🔄 Memulai proses perbaikan environment..."

# 1. Clear semua cache Laravel
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo "✅ Cache cleared"

# 2. Cek file .env
if [ ! -f .env ]; then
    echo "❌ File .env tidak ditemukan!"
    exit 1
fi

echo "📁 File .env ditemukan"

# 3. Cek permission
chmod 644 .env
echo "🔧 Permission diatur ke 644"

# 4. Restart PHP-FPM jika perlu
# sudo systemctl restart php8.1-fpm

# 5. Test environment
echo "🧪 Testing environment variables..."
php artisan tinker --execute="echo 'BNI_CLIENT_ID: ' . env('BNI_CLIENT_ID', 'NOT_FOUND') . PHP_EOL;"

# 6. Buat config cache baru
php artisan config:cache

echo "🎉 Proses selesai! Silakan test registrasi lagi."