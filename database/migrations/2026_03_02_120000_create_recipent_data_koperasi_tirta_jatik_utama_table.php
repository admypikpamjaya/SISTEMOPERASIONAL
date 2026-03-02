<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipent_data_koperasi_tirta_jatik_utama', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_karyawan');
            $table->string('instansi')->nullable();
            $table->string('nama_wali')->nullable();
            $table->string('wa_karyawan')->nullable();
            $table->string('email_karyawan')->nullable();
            $table->text('catatan')->nullable();
            $table->string('source')->nullable();
            $table->boolean('is_valid')->default(false);
            $table->text('validation_error')->nullable();
            $table->timestamps();

            $table->index('instansi');
            $table->index('wa_karyawan');
            $table->index('email_karyawan');
            $table->index('is_valid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipent_data_koperasi_tirta_jatik_utama');
    }
};

