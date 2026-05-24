<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('last_import_file_name')->nullable()->after('qr_code_path');
            $table->timestamp('last_imported_at')->nullable()->after('last_import_file_name');

            $table->index('last_import_file_name');
            $table->index('last_imported_at');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex(['last_import_file_name']);
            $table->dropIndex(['last_imported_at']);
            $table->dropColumn(['last_import_file_name', 'last_imported_at']);
        });
    }
};
