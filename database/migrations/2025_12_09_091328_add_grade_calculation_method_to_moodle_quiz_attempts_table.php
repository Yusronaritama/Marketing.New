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
        Schema::table('moodle_quiz_attempts', function (Blueprint $table) {
            $table->string('grade_calculation_method', 50)
                  ->nullable()
                  ->after('grade_updated_at')
                  ->comment('Metode perhitungan grade: review_grade, percentage_calculated, percentage_string_parsed, sumgrades_fallback, other_field, default_zero');
            
            // Optional: Tambahkan kolom untuk menyimpan formula jika perlu
            $table->text('grade_calculation_details')
                  ->nullable()
                  ->after('grade_calculation_method')
                  ->comment('Detail perhitungan grade dalam format JSON');
            
            // Optional: Tambahkan kolom untuk raw percentage jika perlu
            $table->decimal('raw_percentage', 5, 2)
                  ->nullable()
                  ->after('percentage')
                  ->comment('Persentase mentah sebelum di-round');
            
            // Index untuk performa query
            $table->index(['grade_calculation_method', 'quiz_id'], 'idx_calculation_method_quiz');
            $table->index(['grade_calculation_method', 'moodle_user_id'], 'idx_calculation_method_user');
        });
        
        // Jika ingin mengisi nilai default untuk data yang sudah ada
        DB::table('moodle_quiz_attempts')->update([
            'grade_calculation_method' => 'sumgrades_fallback',
            'grade_calculation_details' => json_encode(['note' => 'Diisi secara otomatis saat migration'])
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('moodle_quiz_attempts', function (Blueprint $table) {
            $table->dropIndex('idx_calculation_method_quiz');
            $table->dropIndex('idx_calculation_method_user');
            
            $table->dropColumn([
                'grade_calculation_method',
                'grade_calculation_details',
                'raw_percentage'
            ]);
        });
    }
};