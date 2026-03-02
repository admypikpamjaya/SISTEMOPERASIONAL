<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tunggakan_records', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('batch_id')->nullable();
            $table->foreign('batch_id')
                ->references('id')
                ->on('tunggakan_import_batches')
                ->cascadeOnDelete();

            $table->unsignedInteger('no_urut')->nullable();
            $table->string('kelas')->nullable();
            $table->string('nama_murid');
            $table->string('bulan');
            $table->decimal('nilai', 15, 2)->default(0);

            $table->enum('recipient_source', ['siswa', 'karyawan'])->nullable();
            $table->uuid('recipient_id')->nullable();

            $table->enum('match_status', ['matched', 'unmatched', 'multiple', 'manual'])->default('unmatched');
            $table->text('match_notes')->nullable();

            $table->enum('blast_status', ['draft', 'queued', 'sent', 'failed'])->default('draft');
            $table->timestamp('blasted_at')->nullable();

            $table->unsignedBigInteger('last_blast_log_id')->nullable();
            $table->foreign('last_blast_log_id')
                ->references('id')
                ->on('blast_logs')
                ->nullOnDelete();

            $table->json('raw_payload')->nullable();

            $table->uuid('created_by')->nullable();
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->uuid('updated_by')->nullable();
            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['recipient_source', 'recipient_id']);
            $table->index(['nama_murid', 'kelas']);
            $table->index(['blast_status', 'match_status']);
            $table->index('batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tunggakan_records');
    }
};
