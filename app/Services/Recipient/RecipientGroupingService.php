<?php

namespace App\Services\Recipient;

use App\Models\BlastRecipient;
use App\Models\BlastRecipientClassHistory;
use Illuminate\Support\Facades\DB;

class RecipientGroupingService
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_UNASSIGNED = 'unassigned';
    public const STATUS_GRADUATED = 'graduated';
    public const STATUS_ALUMNI = 'alumni';
    public const STATUS_INACTIVE = 'inactive';

    /**
     * @return array<string, string>
     */
    public function educationLevelOptions(): array
    {
        return [
            'TK' => 'TK',
            'SD' => 'SD',
            'SMP' => 'SMP',
            'SMA' => 'SMA',
            'SMK' => 'SMK',
            'OTHER' => __('app.blast.other_education_level'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => __('app.blast.status_active'),
            self::STATUS_UNASSIGNED => __('app.blast.status_unassigned'),
            self::STATUS_GRADUATED => __('app.blast.status_graduated'),
            self::STATUS_ALUMNI => __('app.blast.status_alumni'),
            self::STATUS_INACTIVE => __('app.blast.status_inactive'),
        ];
    }

    public function inferEducationLevel(?string $className): ?string
    {
        $normalized = strtoupper(trim((string) $className));
        if ($normalized === '') {
            return null;
        }

        foreach (['SMK', 'SMA', 'SMP', 'SD', 'TK'] as $level) {
            if (str_contains($normalized, $level)) {
                return $level;
            }
        }

        if (preg_match('/^(1|2|3|4|5|6)([^0-9]|$)/', $normalized) === 1) {
            return 'SD';
        }

        if (preg_match('/^(7|8|9)([^0-9]|$)/', $normalized) === 1) {
            return 'SMP';
        }

        if (preg_match('/^(10|11|12|X|XI|XII)([^A-Z0-9]|$)/', $normalized) === 1) {
            return 'SMA';
        }

        return 'OTHER';
    }

    public function currentAcademicYear(): string
    {
        $year = (int) now()->year;
        $startYear = (int) now()->month >= 7 ? $year : $year - 1;

        return $startYear . '/' . ($startYear + 1);
    }

    /**
     * @param array<int, string> $recipientIds
     * @param array{kelas?:string|null,education_level?:string|null,academic_year?:string|null,student_status?:string|null,notes?:string|null} $target
     */
    public function moveRecipients(array $recipientIds, array $target, ?string $actorId): int
    {
        $recipientIds = array_values(array_unique(array_filter($recipientIds)));
        if ($recipientIds === []) {
            return 0;
        }

        return DB::transaction(function () use ($recipientIds, $target, $actorId): int {
            $recipients = BlastRecipient::query()
                ->whereIn('id', $recipientIds)
                ->lockForUpdate()
                ->get();

            $updatedCount = 0;

            foreach ($recipients as $recipient) {
                $targetClass = $this->nullableValue($target['kelas'] ?? null);
                $targetEducationLevel = $this->nullableValue($target['education_level'] ?? null);
                $newClass = $targetClass ?? $recipient->kelas;
                $newEducationLevel = $targetEducationLevel
                    ?? ($targetClass !== null ? $this->inferredLevelForMove($newClass, $recipient->education_level) : null)
                    ?? $recipient->education_level
                    ?? $this->inferEducationLevel($newClass);
                $newAcademicYear = $this->nullableValue($target['academic_year'] ?? null)
                    ?? $recipient->academic_year;
                $newStatus = $this->nullableValue($target['student_status'] ?? null)
                    ?? $recipient->student_status
                    ?? self::STATUS_ACTIVE;

                if (
                    $newClass === $recipient->kelas
                    && $newEducationLevel === $recipient->education_level
                    && $newAcademicYear === $recipient->academic_year
                    && $newStatus === $recipient->student_status
                ) {
                    continue;
                }

                BlastRecipientClassHistory::query()->create([
                    'recipient_id' => (string) $recipient->id,
                    'previous_class' => $recipient->kelas,
                    'new_class' => $newClass,
                    'previous_education_level' => $recipient->education_level,
                    'new_education_level' => $newEducationLevel,
                    'previous_academic_year' => $recipient->academic_year,
                    'new_academic_year' => $newAcademicYear,
                    'previous_status' => $recipient->student_status,
                    'new_status' => $newStatus,
                    'change_type' => $newClass !== $recipient->kelas ? 'class_move' : 'group_update',
                    'notes' => $this->nullableValue($target['notes'] ?? null),
                    'changed_by' => $actorId,
                ]);

                $recipient->update([
                    'kelas' => $newClass,
                    'education_level' => $newEducationLevel,
                    'academic_year' => $newAcademicYear,
                    'student_status' => $newStatus,
                ]);

                $updatedCount++;
            }

            return $updatedCount;
        });
    }

    private function inferredLevelForMove(?string $className, ?string $currentLevel): ?string
    {
        $inferredLevel = $this->inferEducationLevel($className);

        if ($inferredLevel === 'OTHER' && $currentLevel !== null && trim($currentLevel) !== '') {
            return $currentLevel;
        }

        return $inferredLevel;
    }

    private function nullableValue(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
