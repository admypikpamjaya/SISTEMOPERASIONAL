<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcement_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('announcement_logs', 'track_token')) {
                $table->string('track_token')->nullable()->unique()->after('response');
            }

            if (!Schema::hasColumn('announcement_logs', 'opened_at')) {
                $table->timestamp('opened_at')->nullable()->after('track_token');
            }

            if (!Schema::hasColumn('announcement_logs', 'open_count')) {
                $table->unsignedInteger('open_count')->default(0)->after('opened_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('announcement_logs', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('announcement_logs', 'open_count')) {
                $columns[] = 'open_count';
            }

            if (Schema::hasColumn('announcement_logs', 'opened_at')) {
                $columns[] = 'opened_at';
            }

            if (Schema::hasColumn('announcement_logs', 'track_token')) {
                $columns[] = 'track_token';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
