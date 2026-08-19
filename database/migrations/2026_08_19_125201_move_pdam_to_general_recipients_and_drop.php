<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('blast_pdam_recipients')) {
            $pdamRecords = DB::table('blast_pdam_recipients')->get();

            foreach ($pdamRecords as $record) {
                DB::table('blast_general_recipients')->insert([
                    'id' => $record->id,
                    'nama' => $record->nama_lengkap,
                    'instansi' => $record->instansi_pekerjaan,
                    'whatsapp' => $record->nomor_telpon,
                    'email' => $record->email,
                    'sertifikat' => $record->sertifikat,
                    'catatan' => 'Timestamp: ' . $record->timestamp_excel,
                    'source' => 'excel:pdam',
                    'event_name' => 'PDAM',
                    'is_valid' => true,
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at,
                ]);
            }

            Schema::dropIfExists('blast_pdam_recipients');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Down migration can't easily restore the table unless we recreate it and move data back
        // For simplicity, we just recreate the empty table structure
        if (!Schema::hasTable('blast_pdam_recipients')) {
            Schema::create('blast_pdam_recipients', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('timestamp_excel')->nullable();
                $table->string('nama_lengkap');
                $table->string('instansi_pekerjaan')->nullable();
                $table->string('nomor_telpon');
                $table->string('email')->nullable();
                $table->string('sertifikat')->nullable();
                $table->timestamps();
            });
        }
    }
};
