<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE finance_accounts MODIFY type VARCHAR(64) NOT NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE finance_accounts ALTER COLUMN type TYPE VARCHAR(64) USING type::text');
            return;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        $allowedTypes = [
            'PIUTANG',
            'PASIVA_TERKINI',
            'HUTANG',
            'HUTANG_TIDAK_LANCAR',
            'EKUITAS',
            'PENGHASILAN',
            'PENGELUARAN',
            'PENGHASILAN_LAINNYA',
            'PENGHASILAN_TAHUN_TERKINI',
        ];

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $quotedTypes = implode("','", $allowedTypes);
            DB::statement("UPDATE finance_accounts SET type = 'PENGELUARAN' WHERE type NOT IN ('{$quotedTypes}')");
            DB::statement("ALTER TABLE finance_accounts MODIFY type ENUM('{$quotedTypes}') NOT NULL");
            return;
        }

        if ($driver === 'pgsql') {
            $enumName = 'finance_accounts_type_enum';
            $quotedTypes = implode("','", $allowedTypes);
            DB::statement("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = '{$enumName}') THEN CREATE TYPE {$enumName} AS ENUM ('{$quotedTypes}'); END IF; END $$;");
            DB::statement("UPDATE finance_accounts SET type = 'PENGELUARAN' WHERE type NOT IN ('{$quotedTypes}')");
            DB::statement("ALTER TABLE finance_accounts ALTER COLUMN type TYPE {$enumName} USING type::{$enumName}");
        }
    }
};
