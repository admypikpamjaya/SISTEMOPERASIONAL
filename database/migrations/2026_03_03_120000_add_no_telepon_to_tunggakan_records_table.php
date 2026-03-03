<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tunggakan_records', function (Blueprint $table) {
            $table->string('no_telepon')->nullable()->after('nama_murid');
            $table->index('no_telepon');
        });
    }

    public function down(): void
    {
        Schema::table('tunggakan_records', function (Blueprint $table) {
            $table->dropIndex(['no_telepon']);
            $table->dropColumn('no_telepon');
        });
    }
};

