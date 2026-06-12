<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('finance_categories')) {
            return;
        }

        if (!Schema::hasColumn('finance_categories', 'category_type')) {
            Schema::table('finance_categories', function (Blueprint $table): void {
                $table->string('category_type', 20)
                    ->default('single')
                    ->after('status')
                    ->index();
            });
        }

        if (!Schema::hasColumn('finance_categories', 'source_type')) {
            Schema::table('finance_categories', function (Blueprint $table): void {
                $table->string('source_type', 20)
                    ->default('custom')
                    ->after('category_type')
                    ->index();
            });
        }

        if (!Schema::hasColumn('finance_categories', 'sort_order')) {
            Schema::table('finance_categories', function (Blueprint $table): void {
                $table->unsignedInteger('sort_order')
                    ->default(0)
                    ->after('source_type')
                    ->index();
            });
        }

        if (!Schema::hasTable('finance_category_members')) {
            Schema::create('finance_category_members', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('parent_category_id')
                    ->constrained('finance_categories')
                    ->cascadeOnDelete();
                $table->foreignUuid('member_category_id')
                    ->constrained('finance_categories')
                    ->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['parent_category_id', 'member_category_id'], 'finance_category_members_unique');
                $table->index('member_category_id');
            });
        }

        $now = now();
        $staticNames = ['TK', 'SD', 'SMP', 'SMA', 'SMK', 'Yayasan'];
        DB::table('finance_categories')
            ->whereIn('name', $staticNames)
            ->update([
                'category_type' => 'single',
                'source_type' => 'static',
                'updated_at' => $now,
            ]);

        DB::table('finance_categories')
            ->whereNotIn('name', $staticNames)
            ->whereNull('category_type')
            ->update([
                'category_type' => 'single',
                'source_type' => 'custom',
                'updated_at' => $now,
            ]);

        $this->upsertStaticGroup('TK + SD', ['TK', 'SD'], 'Kategori gabungan TK dan SD.');
        $this->upsertStaticGroup('SMA + SMK', ['SMA', 'SMK'], 'Kategori gabungan SMA dan SMK.');
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_category_members');

        if (!Schema::hasTable('finance_categories')) {
            return;
        }

        Schema::table('finance_categories', function (Blueprint $table): void {
            foreach (['sort_order', 'source_type', 'category_type'] as $column) {
                if (Schema::hasColumn('finance_categories', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * @param array<int, string> $memberNames
     */
    private function upsertStaticGroup(string $name, array $memberNames, string $description): void
    {
        $members = DB::table('finance_categories')
            ->whereIn('name', $memberNames)
            ->pluck('id', 'name');

        if ($members->count() !== count($memberNames)) {
            return;
        }

        $now = now();
        $category = DB::table('finance_categories')
            ->where('name', $name)
            ->first();

        if ($category === null) {
            $categoryId = (string) Str::uuid();
            DB::table('finance_categories')->insert([
                'id' => $categoryId,
                'name' => $name,
                'description' => $description,
                'status' => 'active',
                'category_type' => 'group',
                'source_type' => 'static',
                'sort_order' => 0,
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $categoryId = (string) $category->id;
            DB::table('finance_categories')
                ->where('id', $categoryId)
                ->update([
                    'description' => $category->description ?? $description,
                    'status' => $category->status ?: 'active',
                    'category_type' => 'group',
                    'source_type' => 'static',
                    'updated_at' => $now,
                ]);
        }

        foreach ($memberNames as $memberName) {
            $memberId = (string) $members[$memberName];
            $exists = DB::table('finance_category_members')
                ->where('parent_category_id', $categoryId)
                ->where('member_category_id', $memberId)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('finance_category_members')->insert([
                'id' => (string) Str::uuid(),
                'parent_category_id' => $categoryId,
                'member_category_id' => $memberId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
