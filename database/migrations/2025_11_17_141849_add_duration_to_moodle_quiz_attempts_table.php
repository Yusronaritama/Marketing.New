<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::table('moodle_quiz_attempts', function (Blueprint $table) {
        // $table->integer('duration')->nullable()->after('timefinish')->comment('Duration in seconds');
    });
}

public function down()
{
    Schema::table('moodle_quiz_attempts', function (Blueprint $table) {
        // $table->dropColumn('duration');
    });
}
};
