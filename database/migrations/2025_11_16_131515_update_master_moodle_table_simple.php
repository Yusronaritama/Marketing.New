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
        Schema::table('master_moodle', function (Blueprint $table) {
            // Tambahkan kolom hanya jika belum ada
            if (!$this->columnExists('master_moodle', 'last_sync_at')) {
                $table->timestamp('last_sync_at')->nullable()->after('updated_at');
            }
            
            if (!$this->columnExists('master_moodle', 'data_uploaded')) {
                $table->boolean('data_uploaded')->default(false)->after('last_sync_at');
            }
            
            if (!$this->columnExists('master_moodle', 'last_upload_at')) {
                $table->timestamp('last_upload_at')->nullable()->after('data_uploaded');
            }
            
            if (!$this->columnExists('master_moodle', 'moodle_data')) {
                $table->json('moodle_data')->nullable()->after('last_upload_at');
            }
            
            if (!$this->columnExists('master_moodle', 'sync_notes')) {
                $table->text('sync_notes')->nullable()->after('moodle_data');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_moodle', function (Blueprint $table) {
            $table->dropColumn([
                'last_sync_at',
                'data_uploaded',
                'last_upload_at',
                'moodle_data',
                'sync_notes'
            ]);
        });
    }

    /**
     * Check if column exists without using Doctrine
     */
    private function columnExists($table, $column): bool
    {
        $schema = Schema::getConnection()->getSchemaBuilder();
        $columns = $schema->getColumnListing($table);
        
        return in_array($column, $columns);
    }
};