<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blast_recipients', function (Blueprint $table): void {
            if (!Schema::hasColumn('blast_recipients', 'education_level')) {
                $table->string('education_level', 30)->nullable()->after('kelas');
                $table->index('education_level');
            }

            if (!Schema::hasColumn('blast_recipients', 'academic_year')) {
                $table->string('academic_year', 20)->nullable()->after('education_level');
                $table->index('academic_year');
            }

            if (!Schema::hasColumn('blast_recipients', 'student_status')) {
                $table->string('student_status', 30)->default('active')->after('academic_year');
                $table->index('student_status');
            }
        });

        DB::table('blast_recipients')
            ->select(['id', 'kelas'])
            ->whereNull('education_level')
            ->orderBy('id')
            ->chunk(500, function ($recipients): void {
                foreach ($recipients as $recipient) {
                    $className = strtoupper(trim((string) ($recipient->kelas ?? '')));
                    $educationLevel = null;

                    foreach (['SMK', 'SMA', 'SMP', 'SD', 'TK'] as $level) {
                        if (str_contains($className, $level)) {
                            $educationLevel = $level;
                            break;
                        }
                    }

                    if ($educationLevel === null && preg_match('/^(1|2|3|4|5|6)([^0-9]|$)/', $className) === 1) {
                        $educationLevel = 'SD';
                    } elseif ($educationLevel === null && preg_match('/^(7|8|9)([^0-9]|$)/', $className) === 1) {
                        $educationLevel = 'SMP';
                    } elseif ($educationLevel === null && preg_match('/^(10|11|12|X|XI|XII)([^A-Z0-9]|$)/', $className) === 1) {
                        $educationLevel = 'SMA';
                    }

                    if ($educationLevel !== null) {
                        DB::table('blast_recipients')
                            ->where('id', $recipient->id)
                            ->update(['education_level' => $educationLevel]);
                    }
                }
            });

        if (!Schema::hasTable('blast_recipient_class_histories')) {
            Schema::create('blast_recipient_class_histories', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('recipient_id')
                    ->constrained('blast_recipients')
                    ->cascadeOnDelete();
                $table->string('previous_class')->nullable();
                $table->string('new_class')->nullable();
                $table->string('previous_education_level', 30)->nullable();
                $table->string('new_education_level', 30)->nullable();
                $table->string('previous_academic_year', 20)->nullable();
                $table->string('new_academic_year', 20)->nullable();
                $table->string('previous_status', 30)->nullable();
                $table->string('new_status', 30)->nullable();
                $table->string('change_type', 30)->default('group_update');
                $table->text('notes')->nullable();
                $table->foreignUuid('changed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['recipient_id', 'created_at'], 'brch_recipient_created_idx');
                $table->index(['new_academic_year', 'new_class'], 'brch_year_class_idx');
            });
        }

        $historyIndexes = collect(Schema::getIndexes('blast_recipient_class_histories'))
            ->pluck('name');
        if (!$historyIndexes->contains('brch_year_class_idx')) {
            Schema::table('blast_recipient_class_histories', function (Blueprint $table): void {
                $table->index(['new_academic_year', 'new_class'], 'brch_year_class_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('blast_recipient_class_histories');

        Schema::table('blast_recipients', function (Blueprint $table): void {
            foreach (['education_level', 'academic_year', 'student_status'] as $column) {
                if (Schema::hasColumn('blast_recipients', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
