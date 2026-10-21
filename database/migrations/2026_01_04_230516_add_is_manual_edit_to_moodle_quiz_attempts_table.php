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
    Schema::table('moodle_quiz_attempts', function (Blueprint $table) {
        if (!Schema::hasColumn('moodle_quiz_attempts', 'is_manual_edit')) {
            $table->boolean('is_manual_edit')->default(false)->after('is_passed');
        }
    });
}

public function down()
{
    Schema::table('moodle_quiz_attempts', function (Blueprint $table) {
        if (Schema::hasColumn('moodle_quiz_attempts', 'is_manual_edit')) {
            $table->dropColumn('is_manual_edit');
        }
    });
}
};
