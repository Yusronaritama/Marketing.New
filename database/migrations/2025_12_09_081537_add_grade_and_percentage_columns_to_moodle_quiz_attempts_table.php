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
        if (!Schema::hasTable('moodle_quiz_attempts')) {
            Schema::create('moodle_quiz_attempts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('attempt_id')->nullable();
                $table->unsignedBigInteger('moodle_user_id')->nullable();
                $table->unsignedBigInteger('quiz_id')->nullable();
                $table->string('state')->nullable();
                $table->decimal('sumgrades', 8, 2)->nullable();
                $table->timestamp('timestart')->nullable();
                $table->timestamp('timefinish')->nullable();
                $table->timestamp('attempted_at')->nullable();
                $table->json('attempt_data')->nullable();
                $table->boolean('is_manual_edit')->default(false);
                $table->timestamps();
            });
        }

        Schema::table('moodle_quiz_attempts', function (Blueprint $table) {
            // Check jika kolom sudah ada sebelum menambahkannya
            if (!Schema::hasColumn('moodle_quiz_attempts', 'grade')) {
                $table->decimal('grade', 8, 2)->nullable()->after('sumgrades');
            }
            
            if (!Schema::hasColumn('moodle_quiz_attempts', 'max_grade')) {
                $table->decimal('max_grade', 8, 2)->nullable()->after('grade');
            }
            
            if (!Schema::hasColumn('moodle_quiz_attempts', 'percentage')) {
                $table->decimal('percentage', 5, 2)->nullable()->after('max_grade');
            }
            
            if (!Schema::hasColumn('moodle_quiz_attempts', 'is_passed')) {
                $table->boolean('is_passed')->default(false)->after('percentage');
            }
            
            if (!Schema::hasColumn('moodle_quiz_attempts', 'score')) {
                $table->decimal('score', 8, 2)->nullable()->after('is_passed');
            }
            
            if (!Schema::hasColumn('moodle_quiz_attempts', 'total_questions')) {
                $table->integer('total_questions')->nullable()->after('score');
            }
            
            if (!Schema::hasColumn('moodle_quiz_attempts', 'correct_answers')) {
                $table->integer('correct_answers')->nullable()->after('total_questions');
            }
            
            if (!Schema::hasColumn('moodle_quiz_attempts', 'wrong_answers')) {
                $table->integer('wrong_answers')->nullable()->after('correct_answers');
            }
            
            if (!Schema::hasColumn('moodle_quiz_attempts', 'grade_updated_at')) {
                $table->timestamp('grade_updated_at')->nullable()->after('wrong_answers');
            }
            
            if (!Schema::hasColumn('moodle_quiz_attempts', 'review_data')) {
                $table->json('review_data')->nullable()->after('attempt_data');
            }
            
            if (!Schema::hasColumn('moodle_quiz_attempts', 'duration')) {
                $table->integer('duration')->nullable()->after('timefinish');
            }
            
            // Tambahkan indeks untuk performa query
            $table->index(['quiz_id', 'grade'], 'idx_quiz_grade');
            $table->index(['quiz_id', 'is_passed'], 'idx_quiz_passed');
            $table->index(['quiz_id', 'percentage'], 'idx_quiz_percentage');
            $table->index(['moodle_user_id', 'quiz_id'], 'idx_user_quiz');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('moodle_quiz_attempts', function (Blueprint $table) {
            // Hapus indeks terlebih dahulu
            $table->dropIndex('idx_quiz_grade');
            $table->dropIndex('idx_quiz_passed');
            $table->dropIndex('idx_quiz_percentage');
            $table->dropIndex('idx_user_quiz');
            
            // Hapus kolom jika ada
            if (Schema::hasColumn('moodle_quiz_attempts', 'grade')) {
                $table->dropColumn('grade');
            }
            
            if (Schema::hasColumn('moodle_quiz_attempts', 'max_grade')) {
                $table->dropColumn('max_grade');
            }
            
            if (Schema::hasColumn('moodle_quiz_attempts', 'percentage')) {
                $table->dropColumn('percentage');
            }
            
            if (Schema::hasColumn('moodle_quiz_attempts', 'is_passed')) {
                $table->dropColumn('is_passed');
            }
            
            if (Schema::hasColumn('moodle_quiz_attempts', 'score')) {
                $table->dropColumn('score');
            }
            
            if (Schema::hasColumn('moodle_quiz_attempts', 'total_questions')) {
                $table->dropColumn('total_questions');
            }
            
            if (Schema::hasColumn('moodle_quiz_attempts', 'correct_answers')) {
                $table->dropColumn('correct_answers');
            }
            
            if (Schema::hasColumn('moodle_quiz_attempts', 'wrong_answers')) {
                $table->dropColumn('wrong_answers');
            }
            
            if (Schema::hasColumn('moodle_quiz_attempts', 'grade_updated_at')) {
                $table->dropColumn('grade_updated_at');
            }
            
            if (Schema::hasColumn('moodle_quiz_attempts', 'review_data')) {
                $table->dropColumn('review_data');
            }
            
            if (Schema::hasColumn('moodle_quiz_attempts', 'duration')) {
                $table->dropColumn('duration');
            }
        });
    }
};