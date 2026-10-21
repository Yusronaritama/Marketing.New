<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('slik_billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('order_id')->unique();        // ID unik dari sistem kita (misal: PMB-UUID)
            $table->string('virtual_account')->nullable(); // Nomor VA yang kita dapat dari SLIK
            $table->decimal('amount', 15, 2);            // Jumlah tagihan
            $table->string('status')->default('pending');  // pending, paid, failed
            
            // Kolom ini akan diisi oleh Webhook (dari kode Registrasi_ukt)
            $table->string('payment_ntb')->nullable();   // Nomor referensi bank
            $table->datetime('datetime_payment')->nullable(); // Waktu lunas

            // Kolom ini untuk debugging, sangat penting
            $table->json('slik_request_payload')->nullable(); // Data JSON yang kita KIRIM
            $table->json('slik_response_data')->nullable(); // Data JSON yang kita TERIMA

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slik_billings');
    }
};
