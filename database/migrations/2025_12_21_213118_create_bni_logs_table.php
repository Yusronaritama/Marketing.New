<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('bni_logs', function (Blueprint $table) {
        $table->id();
        $table->string('va_number')->nullable();
        $table->json('raw_data'); // Menyimpan JSON utuh dari BNI
        $table->json('decrypted_data'); // Menyimpan hasil dekripsi
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bni_logs');
    }
};
