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
            if (!Schema::hasColumn('moodle_quiz_attempts', 'grade_calculation_details')) {
                $table->text('grade_calculation_details')
                      ->nullable()
                      ->after('grade_calculation_method')
                      ->comment('Detail perhitungan grade dalam format JSON');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('moodle_quiz_attempts', function (Blueprint $table) {
            $table->dropColumn('grade_calculation_details');
        });
    }
};