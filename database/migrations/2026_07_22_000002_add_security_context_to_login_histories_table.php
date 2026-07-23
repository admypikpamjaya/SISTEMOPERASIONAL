<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('login_histories', function (Blueprint $table) {
            $table->string('browser', 80)->nullable()->after('user_agent');
            $table->string('platform', 80)->nullable()->after('browser');
            $table->string('device', 80)->nullable()->after('platform');
            $table->string('location_summary', 191)->nullable()->after('device');
            $table->string('country', 80)->nullable()->after('location_summary');
            $table->string('region', 120)->nullable()->after('country');
            $table->string('city', 120)->nullable()->after('region');
            $table->decimal('latitude', 10, 7)->nullable()->after('city');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('login_histories', function (Blueprint $table) {
            $table->dropColumn([
                'browser',
                'platform',
                'device',
                'location_summary',
                'country',
                'region',
                'city',
                'latitude',
                'longitude',
            ]);
        });
    }
};
