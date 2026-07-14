<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DEFAULT_EVENT_NAME = 'webinar sosialiasai smk ypik pam jaya';

    public function up(): void
    {
        if (!Schema::hasTable('blast_general_recipients')) {
            return;
        }

        Schema::table('blast_general_recipients', function (Blueprint $table): void {
            if (!Schema::hasColumn('blast_general_recipients', 'event_name')) {
                $table->string('event_name')->nullable()->after('sertifikat');
                $table->index('event_name');
            }
        });

        if (Schema::hasColumn('blast_general_recipients', 'event_name')) {
            DB::table('blast_general_recipients')
                ->where(function ($query): void {
                    $query->whereNull('event_name')
                        ->orWhere('event_name', '');
                })
                ->where('source', 'like', 'excel:penerima_umum%')
                ->update(['event_name' => self::DEFAULT_EVENT_NAME]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('blast_general_recipients')) {
            return;
        }

        Schema::table('blast_general_recipients', function (Blueprint $table): void {
            if (Schema::hasColumn('blast_general_recipients', 'event_name')) {
                $table->dropIndex(['event_name']);
                $table->dropColumn('event_name');
            }
        });
    }
};
