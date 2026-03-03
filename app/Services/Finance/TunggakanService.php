<?php

namespace App\Services\Finance;

use App\DataTransferObjects\BlastPayload;
use App\Jobs\Blast\QueueBlastDeliveryJob;
use App\Models\BlastLog;
use App\Models\BlastMessage;
use App\Models\BlastMessageTemplate;
use App\Models\BlastRecipient;
use App\Models\BlastTarget;
use App\Models\TunggakanImportBatch;
use App\Models\TunggakanBlastLog;
use App\Models\TunggakanRecord;
use App\Services\Blast\TemplateRenderer;
use App\Services\Blast\TunggakanMessageContextService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

class TunggakanService
{
    public function __construct(
        private readonly TemplateRenderer $templateRenderer,
        private readonly TunggakanMessageContextService $tunggakanContextService
    ) {}

    /**
     * @var array<string, array<int, array{source:string,id:string}>>
     */
    private array $recipientIndexByFull = [];

    /**
     * @var array<string, array<int, array{source:string,id:string}>>
     */
    private array $recipientIndexByName = [];

    /**
     * @param array{
     *   no_urut?: int|null,
     *   kelas?: string|null,
     *   nama_murid: string,
     *   no_telepon?: string|null,
     *   bulan: string,
     *   nilai: int|float|string
     * } $payload
     */
    public function createManualRecord(array $payload, ?string $actorId): TunggakanRecord
    {
        return DB::transaction(function () use ($payload, $actorId): TunggakanRecord {
            $batch = TunggakanImportBatch::query()->create([
                'source_type' => 'manual',
                'source_reference' => 'manual-finance-form',
                'notes' => 'Input manual data tunggakan dari halaman Finance.',
                'imported_by' => $actorId,
                'total_rows' => 0,
                'matched_rows' => 0,
                'unmatched_rows' => 0,
            ]);

            $record = $this->createRecord(
                batchId: (string) $batch->id,
                row: [
                    'no_urut' => $payload['no_urut'] ?? null,
                    'kelas' => $payload['kelas'] ?? null,
                    'nama_murid' => $payload['nama_murid'],
                    'no_telepon' => $payload['no_telepon'] ?? null,
                    'bulan' => $payload['bulan'],
                    'nilai' => $payload['nilai'],
                ],
                actorId: $actorId,
                rawPayload: [
                    'source' => 'manual',
                ]
            );

            $this->refreshBatchStatsById((string) $batch->id);

            return $record;
        });
    }

    /**
     * @param array{
     *   no_urut?: int|null,
     *   kelas?: string|null,
     *   nama_murid: string,
     *   no_telepon?: string|null,
     *   bulan: string,
     *   nilai: int|float|string
     * } $payload
     */
    public function updateRecord(
        TunggakanRecord $record,
        array $payload,
        ?string $actorId
    ): TunggakanRecord {
        $namaMurid = trim((string) ($payload['nama_murid'] ?? $record->nama_murid));
        $kelas = isset($payload['kelas'])
            ? trim((string) $payload['kelas'])
            : trim((string) $record->kelas);
        $noTelepon = isset($payload['no_telepon'])
            ? $this->normalizeStoredPhone((string) $payload['no_telepon'])
            : $this->normalizeStoredPhone((string) $record->no_telepon);
        $bulan = trim((string) ($payload['bulan'] ?? $record->bulan));
        $nilai = $this->parseNominal($payload['nilai'] ?? $record->nilai);
        $noUrut = $payload['no_urut'] ?? $record->no_urut;

        $match = $this->matchRecipient($namaMurid, $kelas !== '' ? $kelas : null);
        $match = $this->enhanceMatchNotesWithPhone(
            $match,
            $noTelepon
        );

        $record->update([
            'no_urut' => $noUrut !== null ? (int) $noUrut : null,
            'kelas' => $kelas !== '' ? $kelas : null,
            'nama_murid' => $namaMurid,
            'no_telepon' => $noTelepon,
            'bulan' => $bulan !== '' ? $bulan : '-',
            'nilai' => $nilai,
            'recipient_source' => $match['recipient_source'],
            'recipient_id' => $match['recipient_id'],
            'match_status' => $match['match_status'],
            'match_notes' => $match['match_notes'],
            'updated_by' => $actorId,
        ]);

        $this->refreshBatchStatsById($record->batch_id);

        return $record->fresh();
    }

    public function deleteRecord(TunggakanRecord $record): void
    {
        $batchId = $record->batch_id;
        $record->delete();

        $this->refreshBatchStatsById($batchId);
    }

    /**
     * @return array{deleted_records:int,deleted_batches:int}
     */
    public function deleteAllRecords(): array
    {
        return DB::transaction(function (): array {
            $deletedRecords = (int) TunggakanRecord::query()->count();
            $deletedBatches = (int) TunggakanImportBatch::query()->count();

            TunggakanRecord::query()->delete();
            TunggakanImportBatch::query()->delete();

            return [
                'deleted_records' => $deletedRecords,
                'deleted_batches' => $deletedBatches,
            ];
        });
    }

    /**
     * @return array{
     *   candidate_records:int,
     *   candidate_recipients:int,
     *   processed_recipients:int,
     *   sent_recipients:int,
     *   failed_recipients:int,
     *   skipped_recipients:int,
     *   targets_total:int,
     *   sent_targets:int,
     *   failed_targets:int,
     *   queued_targets:int,
     *   blast_message_id:string|null
     * }
     */
    public function blastWhatsappFromTunggakan(
        ?string $templateId,
        ?string $actorId
    ): array {
        $actorId = trim((string) $actorId);
        if ($actorId === '') {
            throw new RuntimeException('User login tidak ditemukan untuk proses blasting.');
        }

        $templateContent = $this->resolveWhatsappTemplateContent($templateId);

        $candidateRecords = TunggakanRecord::query()
            ->whereIn('blast_status', ['draft', 'failed'])
            ->where(function ($query): void {
                $query->where(function ($matchQuery): void {
                    $matchQuery->where('match_status', 'matched')
                        ->where('recipient_source', 'siswa')
                        ->whereNotNull('recipient_id');
                })->orWhereNotNull('no_telepon');
            })
            ->orderBy('updated_at')
            ->get();

        if ($candidateRecords->isEmpty()) {
            return [
                'candidate_records' => 0,
                'candidate_recipients' => 0,
                'processed_recipients' => 0,
                'sent_recipients' => 0,
                'failed_recipients' => 0,
                'skipped_recipients' => 0,
                'targets_total' => 0,
                'sent_targets' => 0,
                'failed_targets' => 0,
                'queued_targets' => 0,
                'blast_message_id' => null,
            ];
        }

        $groups = [];
        foreach ($candidateRecords as $record) {
            $normalizedDirectPhone = $this->normalizeWhatsappTarget($record->no_telepon);

            if ($normalizedDirectPhone !== null) {
                $groupKey = 'phone:' . $normalizedDirectPhone;
                if (!isset($groups[$groupKey])) {
                    $groups[$groupKey] = [
                        'mode' => 'phone',
                        'phone' => $normalizedDirectPhone,
                        'recipient_id' => null,
                        'records' => collect(),
                    ];
                }

                $groups[$groupKey]['records']->push($record);
                continue;
            }

            if (
                $record->match_status === 'matched'
                && $record->recipient_source === 'siswa'
                && !empty($record->recipient_id)
            ) {
                $groupKey = 'recipient:' . (string) $record->recipient_id;
                if (!isset($groups[$groupKey])) {
                    $groups[$groupKey] = [
                        'mode' => 'recipient',
                        'phone' => null,
                        'recipient_id' => (string) $record->recipient_id,
                        'records' => collect(),
                    ];
                }

                $groups[$groupKey]['records']->push($record);
            }
        }

        $recipientIds = collect($groups)
            ->filter(fn (array $group): bool => $group['mode'] === 'recipient')
            ->map(fn (array $group): string => (string) $group['recipient_id'])
            ->filter(fn (string $value): bool => trim($value) !== '')
            ->unique()
            ->values()
            ->all();

        $recipientMap = BlastRecipient::query()
            ->whereIn('id', $recipientIds)
            ->where('is_valid', true)
            ->get()
            ->keyBy('id');

        $summary = [
            'candidate_records' => (int) $candidateRecords->count(),
            'candidate_recipients' => count($groups),
            'processed_recipients' => 0,
            'sent_recipients' => 0,
            'failed_recipients' => 0,
            'skipped_recipients' => 0,
            'targets_total' => 0,
            'sent_targets' => 0,
            'failed_targets' => 0,
            'queued_targets' => 0,
            'blast_message_id' => null,
        ];

        $blastMessage = null;
        $groupLogDetails = [];

        foreach ($groups as $groupKey => $group) {
            /** @var Collection<int, TunggakanRecord> $recipientRecords */
            $recipientRecords = $group['records'];
            $recipientRecordIds = $recipientRecords
                ->pluck('id')
                ->map(fn ($value) => (string) $value)
                ->all();

            $mode = (string) $group['mode'];
            $directPhone = (string) ($group['phone'] ?? '');
            $recipient = null;
            $targets = [];

            if ($mode === 'recipient') {
                $recipient = $recipientMap->get((string) $group['recipient_id']);
                if ($recipient === null) {
                    TunggakanRecord::query()
                        ->whereIn('id', $recipientRecordIds)
                        ->update([
                            'blast_status' => 'failed',
                            'blasted_at' => now(),
                            'updated_by' => $actorId,
                        ]);

                    $summary['skipped_recipients']++;
                    $summary['failed_recipients']++;
                    $groupLogDetails[] = [
                        'group' => $groupKey,
                        'mode' => $mode,
                        'status' => 'skipped',
                        'reason' => 'Recipient siswa tidak ditemukan.',
                        'record_count' => count($recipientRecordIds),
                    ];
                    continue;
                }

                $targets = $this->collectWhatsappTargets($recipient);
            } elseif ($directPhone !== '') {
                $targets = [$directPhone];
                $recipient = $this->buildPseudoRecipientForDirectPhone($directPhone, $recipientRecords);
            }

            if ($targets === [] || $recipient === null) {
                TunggakanRecord::query()
                    ->whereIn('id', $recipientRecordIds)
                    ->update([
                        'blast_status' => 'failed',
                        'blasted_at' => now(),
                        'match_notes' => 'No telepon tidak valid untuk blasting langsung.',
                        'updated_by' => $actorId,
                    ]);

                $summary['skipped_recipients']++;
                $summary['failed_recipients']++;
                $groupLogDetails[] = [
                    'group' => $groupKey,
                    'mode' => $mode,
                    'status' => 'skipped',
                    'reason' => 'Target WhatsApp tidak valid.',
                    'record_count' => count($recipientRecordIds),
                ];
                continue;
            }

            $messageBody = $this->templateRenderer->render(
                $templateContent,
                $recipient,
                $this->buildTunggakanBlastContext($recipient, $recipientRecords)
            );

            if ($blastMessage === null) {
                $blastMessage = BlastMessage::query()->create([
                    'channel' => 'WHATSAPP',
                    'subject' => null,
                    'message' => $templateContent,
                    'meta' => [
                        'source' => 'finance.tunggakan',
                        'template_id' => $templateId,
                        'recipient_scope' => 'siswa+direct_phone',
                        'triggered_at' => now('Asia/Jakarta')->toIso8601String(),
                    ],
                    'campaign_status' => 'QUEUED',
                    'priority' => 'normal',
                    'created_by' => $actorId,
                ]);

                $summary['blast_message_id'] = (string) $blastMessage->id;
            }

            TunggakanRecord::query()
                ->whereIn('id', $recipientRecordIds)
                ->update([
                    'blast_status' => 'queued',
                    'updated_by' => $actorId,
                ]);

            $summary['processed_recipients']++;

            $logIds = [];
            $sentTargetCount = 0;
            $failedTargetCount = 0;
            $queuedTargetCount = 0;

            foreach ($targets as $target) {
                $blastTarget = BlastTarget::query()->create([
                    'blast_message_id' => (string) $blastMessage->id,
                    'target' => $target,
                ]);

                $blastLog = BlastLog::query()->create([
                    'blast_message_id' => (string) $blastMessage->id,
                    'blast_target_id' => (int) $blastTarget->id,
                    'status' => 'PENDING',
                    'message_snapshot' => $messageBody,
                    'response' => null,
                    'error_message' => null,
                    'attempt' => 0,
                    'sent_at' => null,
                ]);

                $payload = new BlastPayload($messageBody);
                $payload->setMeta('channel', 'WHATSAPP');
                $payload->setMeta('sent_by', $actorId);
                $payload->setMeta('recipient_source', $mode === 'recipient' ? 'siswa' : 'direct_phone');
                $payload->setMeta('recipient_id', $mode === 'recipient' ? (string) $recipient->id : null);
                $payload->setMeta('tunggakan_group_key', $groupKey);
                $payload->setMeta('blast_log_id', (int) $blastLog->id);
                $payload->setMeta('blast_message_id', (string) $blastMessage->id);
                $payload->setMeta('queue_name', (string) config('blast.queues.whatsapp.normal', 'blast-whatsapp-normal'));
                $payload->setMeta('retry_attempts', 1);
                $payload->setMeta('retry_backoff_seconds', (array) config('blast.retry.backoff_seconds', [30, 120, 300]));

                app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatchSync(
                    new QueueBlastDeliveryJob(
                        'WHATSAPP',
                        $target,
                        null,
                        $payload
                    )
                );

                $latestLog = BlastLog::query()->find($blastLog->id);
                $latestStatus = strtoupper((string) ($latestLog?->status ?? 'FAILED'));

                if ($latestStatus === 'SENT') {
                    $sentTargetCount++;
                } elseif ($latestStatus === 'PENDING') {
                    $queuedTargetCount++;
                } else {
                    $failedTargetCount++;
                }

                $logIds[] = (int) $blastLog->id;
                $summary['targets_total']++;
            }

            $summary['sent_targets'] += $sentTargetCount;
            $summary['failed_targets'] += $failedTargetCount;
            $summary['queued_targets'] += $queuedTargetCount;

            $finalStatus = 'failed';
            if ($sentTargetCount > 0) {
                $finalStatus = 'sent';
                $summary['sent_recipients']++;
            } elseif ($queuedTargetCount > 0) {
                $finalStatus = 'queued';
            } else {
                $summary['failed_recipients']++;
            }

            $lastLogId = $logIds !== [] ? (int) end($logIds) : null;
            TunggakanRecord::query()
                ->whereIn('id', $recipientRecordIds)
                ->update([
                    'blast_status' => $finalStatus,
                    'blasted_at' => now(),
                    'last_blast_log_id' => $lastLogId,
                    'updated_by' => $actorId,
                ]);

            $groupLogDetails[] = [
                'group' => $groupKey,
                'mode' => $mode,
                'status' => $finalStatus,
                'targets' => $targets,
                'sent_targets' => $sentTargetCount,
                'failed_targets' => $failedTargetCount,
                'queued_targets' => $queuedTargetCount,
                'record_count' => count($recipientRecordIds),
            ];
        }

        TunggakanBlastLog::query()->create([
            'blast_message_id' => $summary['blast_message_id'],
            'triggered_by' => $actorId,
            'total_candidate_records' => $summary['candidate_records'],
            'total_candidate_groups' => $summary['candidate_recipients'],
            'total_processed_groups' => $summary['processed_recipients'],
            'total_sent_groups' => $summary['sent_recipients'],
            'total_failed_groups' => $summary['failed_recipients'],
            'total_skipped_groups' => $summary['skipped_recipients'],
            'total_targets' => $summary['targets_total'],
            'total_sent_targets' => $summary['sent_targets'],
            'total_failed_targets' => $summary['failed_targets'],
            'total_queued_targets' => $summary['queued_targets'],
            'details' => [
                'source' => 'finance.tunggakan',
                'group_logs' => $groupLogDetails,
            ],
        ]);

        return $summary;
    }

    /**
     * @return array{
     *   inserted:int,
     *   skipped:int,
     *   matched:int,
     *   unmatched:int,
     *   multiple:int,
     *   not_found_in_student_db:int,
     *   batch_id:string
     * }
     */
    public function importFromExcel(
        string $path,
        string $originalName,
        ?string $actorId
    ): array {
        if (!file_exists($path)) {
            throw new RuntimeException('File Excel tidak ditemukan.');
        }

        return DB::transaction(function () use ($path, $originalName, $actorId): array {
            $batch = TunggakanImportBatch::query()->create([
                'source_type' => 'excel',
                'source_reference' => $originalName,
                'notes' => 'Import Excel tunggakan dari modul Finance.',
                'imported_by' => $actorId,
                'total_rows' => 0,
                'matched_rows' => 0,
                'unmatched_rows' => 0,
            ]);

            $inserted = 0;
            $skipped = 0;
            $matched = 0;
            $unmatched = 0;
            $multiple = 0;
            $notFoundInStudentDb = 0;

            $allSheetRows = $this->loadSheetRows($path);
            foreach ($allSheetRows as $rows) {
                if (empty($rows)) {
                    continue;
                }

                [$headerMap, $headerIndex] = $this->resolveHeaderMap($rows);

                foreach ($rows as $rowIndex => $row) {
                    if (!is_array($row) || $this->isRowEmpty($row)) {
                        continue;
                    }

                    if ($rowIndex === $headerIndex || $this->isLikelyHeaderRow($row)) {
                        continue;
                    }

                    $parsed = $this->parseExcelRow($row, $headerMap);
                    if ($parsed === null) {
                        $skipped++;
                        continue;
                    }

                    $record = $this->createRecord(
                        batchId: (string) $batch->id,
                        row: $parsed,
                        actorId: $actorId,
                        rawPayload: [
                            'source' => 'excel',
                            'original_file' => $originalName,
                            'sheet_row' => $rowIndex + 1,
                        ]
                    );

                    $inserted++;
                    if ($record->match_status === 'matched') {
                        $matched++;
                    } elseif ($record->match_status === 'multiple') {
                        $multiple++;
                        $unmatched++;
                    } else {
                        $unmatched++;
                        if (
                            trim((string) $record->no_telepon) === ''
                            && str_contains(
                                strtolower((string) $record->match_notes),
                                'tidak ditemukan di database siswa'
                            )
                        ) {
                            $notFoundInStudentDb++;
                        }
                    }
                }
            }

            $batch->update([
                'total_rows' => $inserted,
                'matched_rows' => $matched,
                'unmatched_rows' => $unmatched,
            ]);

            return [
                'inserted' => $inserted,
                'skipped' => $skipped,
                'matched' => $matched,
                'unmatched' => $unmatched,
                'multiple' => $multiple,
                'not_found_in_student_db' => $notFoundInStudentDb,
                'batch_id' => (string) $batch->id,
            ];
        });
    }

    /**
     * @return array{
     *   inserted:int,
     *   skipped:int,
     *   matched:int,
     *   unmatched:int,
     *   multiple:int,
     *   batch_id:string
     * }
     */
    public function syncFromRecipientDatabase(
        string $bulan,
        ?string $actorId
    ): array {
        $bulan = trim($bulan);
        if ($bulan === '') {
            $bulan = now('Asia/Jakarta')->locale('id')->translatedFormat('F Y');
        }

        return DB::transaction(function () use ($bulan, $actorId): array {
            $batch = TunggakanImportBatch::query()->create([
                'source_type' => 'database',
                'source_reference' => 'blast_recipients',
                'notes' => 'Sinkronisasi data tunggakan dari database recipient siswa.',
                'imported_by' => $actorId,
                'total_rows' => 0,
                'matched_rows' => 0,
                'unmatched_rows' => 0,
            ]);

            $inserted = 0;
            $skipped = 0;
            $matched = 0;

            $sequence = 1;

            $studentRecipients = BlastRecipient::query()
                ->where('is_valid', true)
                ->orderBy('nama_siswa')
                ->get(['id', 'nama_siswa', 'kelas']);

            foreach ($studentRecipients as $recipient) {
                if (
                    $this->alreadyExistsForPeriod(
                        recipientSource: 'siswa',
                        recipientId: (string) $recipient->id,
                        bulan: $bulan
                    )
                ) {
                    $skipped++;
                    continue;
                }

                $this->createRecord(
                    batchId: (string) $batch->id,
                    row: [
                        'no_urut' => $sequence++,
                        'kelas' => (string) $recipient->kelas,
                        'nama_murid' => (string) $recipient->nama_siswa,
                        'no_telepon' => (string) ($recipient->wa_wali ?: $recipient->wa_wali_2),
                        'bulan' => $bulan,
                        'nilai' => 0,
                    ],
                    actorId: $actorId,
                    rawPayload: [
                        'source' => 'database:blast_recipients',
                        'recipient_source' => 'siswa',
                        'recipient_id' => (string) $recipient->id,
                    ],
                    forcedMatch: [
                        'recipient_source' => 'siswa',
                        'recipient_id' => (string) $recipient->id,
                        'match_status' => 'matched',
                        'match_notes' => 'Matched direct dari recipient DB siswa.',
                    ]
                );

                $inserted++;
                $matched++;
            }

            $batch->update([
                'total_rows' => $inserted,
                'matched_rows' => $matched,
                'unmatched_rows' => 0,
            ]);

            return [
                'inserted' => $inserted,
                'skipped' => $skipped,
                'matched' => $matched,
                'unmatched' => 0,
                'multiple' => 0,
                'batch_id' => (string) $batch->id,
            ];
        });
    }

    private function alreadyExistsForPeriod(
        string $recipientSource,
        string $recipientId,
        string $bulan
    ): bool {
        return TunggakanRecord::query()
            ->where('recipient_source', $recipientSource)
            ->where('recipient_id', $recipientId)
            ->where('bulan', $bulan)
            ->exists();
    }

    /**
     * @param array{
     *   no_urut:int|null,
     *   kelas:string|null,
     *   nama_murid:string,
     *   no_telepon?:string|null,
     *   bulan:string,
     *   nilai:int|float|string
     * } $row
     * @param array<string,mixed> $rawPayload
     * @param array{
     *   recipient_source:string|null,
     *   recipient_id:string|null,
     *   match_status:string,
     *   match_notes:string|null
     * }|null $forcedMatch
     */
    private function createRecord(
        string $batchId,
        array $row,
        ?string $actorId,
        array $rawPayload,
        ?array $forcedMatch = null
    ): TunggakanRecord {
        $namaMurid = trim((string) ($row['nama_murid'] ?? ''));
        $kelas = isset($row['kelas']) ? trim((string) $row['kelas']) : null;
        $noTelepon = $this->normalizeStoredPhone((string) ($row['no_telepon'] ?? ''));
        $bulan = trim((string) ($row['bulan'] ?? ''));
        $nilai = $this->parseNominal($row['nilai'] ?? 0);

        $match = $forcedMatch ?? $this->matchRecipient($namaMurid, $kelas);
        $match = $this->enhanceMatchNotesWithPhone(
            $match,
            $noTelepon
        );

        return TunggakanRecord::query()->create([
            'batch_id' => $batchId,
            'no_urut' => $row['no_urut'] ?? null,
            'kelas' => $kelas !== '' ? $kelas : null,
            'nama_murid' => $namaMurid,
            'no_telepon' => $noTelepon,
            'bulan' => $bulan !== '' ? $bulan : '-',
            'nilai' => $nilai,
            'recipient_source' => $match['recipient_source'],
            'recipient_id' => $match['recipient_id'],
            'match_status' => $match['match_status'],
            'match_notes' => $match['match_notes'],
            'blast_status' => 'draft',
            'raw_payload' => $rawPayload,
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);
    }

    /**
     * @return array{
     *   recipient_source:string|null,
     *   recipient_id:string|null,
     *   match_status:string,
     *   match_notes:string|null
     * }
     */
    private function matchRecipient(string $namaMurid, ?string $kelas): array
    {
        $this->buildRecipientIndexesIfNeeded();

        $nameKey = $this->normalizeText($namaMurid);
        $classKey = $this->normalizeText($kelas ?? '');

        if ($nameKey === '') {
            return [
                'recipient_source' => null,
                'recipient_id' => null,
                'match_status' => 'unmatched',
                'match_notes' => 'Nama murid kosong sehingga tidak dapat dicocokkan.',
            ];
        }

        if ($classKey !== '') {
            $fullKey = $nameKey . '|' . $classKey;
            $fullCandidates = $this->recipientIndexByFull[$fullKey] ?? [];

            if (count($fullCandidates) === 1) {
                return [
                    'recipient_source' => $fullCandidates[0]['source'],
                    'recipient_id' => $fullCandidates[0]['id'],
                    'match_status' => 'matched',
                    'match_notes' => 'Matched by nama murid + kelas.',
                ];
            }

            if (count($fullCandidates) > 1) {
                return [
                    'recipient_source' => null,
                    'recipient_id' => null,
                    'match_status' => 'multiple',
                    'match_notes' => 'Ditemukan lebih dari satu recipient dengan nama + kelas yang sama.',
                ];
            }
        }

        $nameCandidates = $this->recipientIndexByName[$nameKey] ?? [];
        if (count($nameCandidates) === 1) {
            return [
                'recipient_source' => $nameCandidates[0]['source'],
                'recipient_id' => $nameCandidates[0]['id'],
                'match_status' => 'matched',
                'match_notes' => 'Matched by nama murid.',
            ];
        }

        if (count($nameCandidates) > 1) {
            return [
                'recipient_source' => null,
                'recipient_id' => null,
                'match_status' => 'multiple',
                'match_notes' => 'Nama murid cocok ke lebih dari satu recipient. Perlu review manual.',
            ];
        }

        return [
            'recipient_source' => null,
            'recipient_id' => null,
            'match_status' => 'unmatched',
            'match_notes' => 'Data siswa tidak ditemukan di database siswa.',
        ];
    }

    private function buildRecipientIndexesIfNeeded(): void
    {
        if (!empty($this->recipientIndexByName)) {
            return;
        }

        $students = BlastRecipient::query()
            ->where('is_valid', true)
            ->get(['id', 'nama_siswa', 'kelas']);

        foreach ($students as $student) {
            $nameKey = $this->normalizeText((string) $student->nama_siswa);
            $classKey = $this->normalizeText((string) $student->kelas);
            if ($nameKey === '') {
                continue;
            }

            $entry = [
                'source' => 'siswa',
                'id' => (string) $student->id,
            ];

            $this->recipientIndexByName[$nameKey] ??= [];
            $this->recipientIndexByName[$nameKey][] = $entry;

            if ($classKey !== '') {
                $fullKey = $nameKey . '|' . $classKey;
                $this->recipientIndexByFull[$fullKey] ??= [];
                $this->recipientIndexByFull[$fullKey][] = $entry;
            }
        }
    }

    private function refreshBatchStatsById(?string $batchId): void
    {
        $batchId = trim((string) $batchId);
        if ($batchId === '') {
            return;
        }

        $batch = TunggakanImportBatch::query()->find($batchId);
        if ($batch === null) {
            return;
        }

        $total = TunggakanRecord::query()
            ->where('batch_id', $batchId)
            ->count();

        $matched = TunggakanRecord::query()
            ->where('batch_id', $batchId)
            ->where('match_status', 'matched')
            ->count();

        $batch->update([
            'total_rows' => $total,
            'matched_rows' => $matched,
            'unmatched_rows' => max(0, $total - $matched),
        ]);
    }

    /**
     * @param array<int, array<int, mixed>> $rows
     * @return array{0: array<string, int>, 1: int}
     */
    private function resolveHeaderMap(array $rows): array
    {
        $mergedMap = [];
        $headerIndex = -1;

        foreach ($rows as $index => $row) {
            if (!is_array($row) || $this->isRowEmpty($row)) {
                continue;
            }

            $map = [];
            foreach ($row as $colIndex => $value) {
                $canonical = $this->canonicalHeader((string) $value);
                if ($canonical === null || array_key_exists($canonical, $map)) {
                    continue;
                }

                $map[$canonical] = $colIndex;
            }

            if (empty($map)) {
                if ($headerIndex !== -1 && $index > ($headerIndex + 3)) {
                    break;
                }

                continue;
            }

            if ($headerIndex === -1) {
                $headerIndex = $index;
            }

            foreach ($map as $key => $colIndex) {
                if (!array_key_exists($key, $mergedMap)) {
                    $mergedMap[$key] = $colIndex;
                }
            }

            if (
                array_key_exists('nama_murid', $mergedMap)
                && array_key_exists('bulan', $mergedMap)
                && array_key_exists('nilai', $mergedMap)
            ) {
                break;
            }

            if ($index > ($headerIndex + 3)) {
                break;
            }
        }

        if (!empty($mergedMap)) {
            return [$mergedMap, $headerIndex];
        }

        return [[], $headerIndex];
    }

    /**
     * @param array<int, mixed> $row
     * @param array<string, int> $headerMap
     * @return array{no_urut:int|null,kelas:string|null,nama_murid:string,no_telepon:string|null,bulan:string,nilai:float}|null
     */
    private function parseExcelRow(array $row, array $headerMap): ?array
    {
        $noUrutRaw = $this->resolveCell($row, $headerMap, 'no', 0);
        $kelas = $this->resolveCell($row, $headerMap, 'kelas', 1);
        $namaMurid = $this->resolveCell($row, $headerMap, 'nama_murid', 2);
        $bulan = $this->resolveCell($row, $headerMap, 'bulan', 3);
        $nilaiRaw = $this->resolveCell($row, $headerMap, 'nilai', 4);
        $noTelepon = $this->resolveCell($row, $headerMap, 'no_telepon', 5);

        $namaMurid = trim((string) $namaMurid);
        $bulan = trim((string) $bulan);
        $nilai = $this->parseNominal($nilaiRaw);

        if ($namaMurid === '' && $bulan === '' && $nilai <= 0) {
            return null;
        }

        if ($namaMurid === '' || $bulan === '') {
            return null;
        }

        return [
            'no_urut' => $noUrutRaw !== null && trim((string) $noUrutRaw) !== ''
                ? (int) preg_replace('/\D+/', '', (string) $noUrutRaw)
                : null,
            'kelas' => $kelas !== null && trim((string) $kelas) !== '' ? trim((string) $kelas) : null,
            'nama_murid' => $namaMurid,
            'no_telepon' => $noTelepon !== null ? trim((string) $noTelepon) : null,
            'bulan' => $bulan,
            'nilai' => $nilai,
        ];
    }

    /**
     * @param array<int, mixed> $row
     * @param array<string, int> $headerMap
     */
    private function resolveCell(
        array $row,
        array $headerMap,
        string $field,
        int $fallbackIndex
    ): ?string {
        $index = $headerMap[$field] ?? $fallbackIndex;
        $value = $row[$index] ?? null;
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function canonicalHeader(string $header): ?string
    {
        $normalized = strtolower(trim($header));
        $normalized = preg_replace('/[^a-z0-9]+/', '', $normalized) ?? '';

        if ($normalized === '') {
            return null;
        }

        if (in_array($normalized, ['no', 'nomor'], true)) {
            return 'no';
        }

        if (str_starts_with($normalized, 'kelas') || $normalized === 'class') {
            return 'kelas';
        }

        if (
            in_array($normalized, ['namamurid', 'namasiswa', 'murid', 'siswa'], true) ||
            str_contains($normalized, 'namamurid') ||
            str_contains($normalized, 'namasiswa')
        ) {
            return 'nama_murid';
        }

        if (
            in_array($normalized, ['bulan', 'period', 'periode'], true) ||
            str_contains($normalized, 'bulan')
        ) {
            return 'bulan';
        }

        if (
            in_array($normalized, ['nilai', 'nominal', 'jumlah', 'tagihan'], true) ||
            str_contains($normalized, 'nominal') ||
            str_contains($normalized, 'nilai') ||
            str_contains($normalized, 'tagihan')
        ) {
            return 'nilai';
        }

        if (
            in_array($normalized, ['notelepon', 'telp', 'telepon', 'nomorwa', 'wa', 'whatsapp', 'hp', 'nohp'], true) ||
            str_contains($normalized, 'telepon') ||
            str_contains($normalized, 'nomorwa') ||
            str_contains($normalized, 'whatsapp') ||
            str_contains($normalized, 'nohp')
        ) {
            return 'no_telepon';
        }

        return null;
    }

    /**
     * @param array<int, mixed> $row
     */
    private function isLikelyHeaderRow(array $row): bool
    {
        $joined = strtolower(implode(' ', array_map(
            fn ($value) => trim((string) $value),
            $row
        )));

        $joined = preg_replace('/\s+/', ' ', $joined) ?? $joined;

        if ($joined === '') {
            return false;
        }

        return str_contains($joined, 'nama murid')
            || str_contains($joined, 'nama siswa')
            || str_contains($joined, 'tunggakan')
            || str_contains($joined, 'bulan')
            || str_contains($joined, 'nilai')
            || str_contains($joined, 'telepon')
            || str_contains($joined, 'whatsapp');
    }

    /**
     * @param array<int, mixed> $row
     */
    private function isRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($value === null) {
                continue;
            }

            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<int, array<int, array<int, mixed>>>
     */
    private function loadSheetRows(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $allRows = [];

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $allRows[] = $sheet->toArray(null, true, true, false);
        }

        return $allRows;
    }

    /**
     * @param mixed $value
     */
    private function parseNominal($value): float
    {
        if ($value === null) {
            return 0.0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return 0.0;
        }

        // Hilangkan prefix mata uang umum agar tidak menyisakan "." di depan angka (contoh: "Rp. 1000000").
        $raw = preg_replace('/\b(rp|idr)\.?\s*/i', '', $raw) ?? $raw;

        $cleaned = str_replace(' ', '', $raw);
        $cleaned = preg_replace('/[^0-9,.\-]/', '', $cleaned) ?? '';
        $cleaned = ltrim($cleaned, '.,');

        if ($cleaned === '' || $cleaned === '-' || $cleaned === '.' || $cleaned === ',') {
            return 0.0;
        }

        if (str_contains($cleaned, ',') && str_contains($cleaned, '.')) {
            $cleaned = str_replace('.', '', $cleaned);
            $cleaned = str_replace(',', '.', $cleaned);
        } elseif (str_contains($cleaned, ',')) {
            $cleaned = str_replace('.', '', $cleaned);
            $cleaned = str_replace(',', '.', $cleaned);
        } else {
            $dotCount = substr_count($cleaned, '.');
            if ($dotCount > 1) {
                $cleaned = str_replace('.', '', $cleaned);
            } elseif ($dotCount === 1) {
                $parts = explode('.', $cleaned);
                if (isset($parts[1]) && strlen($parts[1]) === 3) {
                    $cleaned = str_replace('.', '', $cleaned);
                }
            }
        }

        return is_numeric($cleaned) ? (float) $cleaned : 0.0;
    }

    private function normalizeText(string $value): string
    {
        $normalized = strtolower(trim($value));
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;

        return $normalized;
    }

    private function resolveWhatsappTemplateContent(?string $templateId): string
    {
        $templateId = trim((string) $templateId);

        if ($templateId !== '') {
            $selectedTemplate = BlastMessageTemplate::query()
                ->where('id', $templateId)
                ->where('channel', 'whatsapp')
                ->where('is_active', true)
                ->first();

            if ($selectedTemplate === null) {
                throw new RuntimeException('Template WhatsApp tidak ditemukan atau tidak aktif.');
            }

            return trim((string) $selectedTemplate->content);
        }

        $defaultTemplate = BlastMessageTemplate::query()
            ->where('channel', 'whatsapp')
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN name = 'Tunggakan WA Otomatis' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->first();

        if ($defaultTemplate !== null) {
            return trim((string) $defaultTemplate->content);
        }

        return "Yth. Bapak/Ibu {nama_wali},\n\n"
            . "Informasi tunggakan untuk {nama_siswa} ({kelas}):\n"
            . "- Periode: {bulan_tunggakan}\n"
            . "- Nilai periode: {nilai_tunggakan_rupiah}\n"
            . "- Total tunggakan: {total_tunggakan_rupiah}\n"
            . "- Tagihan: {tagihan_rupiah}\n\n"
            . "Mohon kesediaannya untuk segera melakukan pembayaran. Terima kasih.";
    }

    /**
     * @param Collection<int, TunggakanRecord> $records
     * @return array<string, mixed>
     */
    private function buildTunggakanBlastContext(
        BlastRecipient $recipient,
        Collection $records
    ): array {
        $tagihan = (float) $records->sum(
            fn (TunggakanRecord $record): float => (float) $record->nilai
        );

        $bulanTunggakan = $records->pluck('bulan')
            ->map(fn ($value): string => trim((string) $value))
            ->filter(fn (string $value): bool => $value !== '')
            ->unique()
            ->values()
            ->implode(', ');

        $context = $this->tunggakanContextService->resolveForRecipient($recipient);

        $totalTunggakan = (float) ($context['total_tunggakan'] ?? 0);
        if ($totalTunggakan <= 0) {
            $totalTunggakan = $tagihan;
        }

        return array_merge($context, [
            'bulan_tunggakan' => $bulanTunggakan !== '' ? $bulanTunggakan : '-',
            'nilai_tunggakan' => $tagihan,
            'nilai_tunggakan_rupiah' => $this->formatRupiah($tagihan),
            'total_tunggakan' => $totalTunggakan,
            'total_tunggakan_rupiah' => $this->formatRupiah($totalTunggakan),
            'tagihan' => $tagihan,
            'tagihan_rupiah' => $this->formatRupiah($tagihan),
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function collectWhatsappTargets(BlastRecipient $recipient): array
    {
        $targets = [];

        foreach ([$recipient->wa_wali, $recipient->wa_wali_2] as $candidate) {
            $normalized = $this->normalizeWhatsappTarget($candidate);
            if ($normalized === null) {
                continue;
            }

            $targets[$normalized] = $normalized;
        }

        return array_values($targets);
    }

    private function normalizeWhatsappTarget(?string $target): ?string
    {
        if ($target === null) {
            return null;
        }

        $normalized = preg_replace('/\D+/', '', trim($target)) ?? '';
        if ($normalized === '') {
            return null;
        }

        if (Str::startsWith($normalized, '0')) {
            $normalized = '62' . substr($normalized, 1);
        } elseif (Str::startsWith($normalized, '8')) {
            $normalized = '62' . $normalized;
        }

        if (!Str::startsWith($normalized, '62')) {
            return null;
        }

        $length = strlen($normalized);
        if ($length < 10 || $length > 15) {
            return null;
        }

        return $normalized;
    }

    private function formatRupiah(float $amount): string
    {
        return 'Rp ' . number_format(round($amount), 0, ',', '.');
    }

    /**
     * @param array{
     *   recipient_source:string|null,
     *   recipient_id:string|null,
     *   match_status:string,
     *   match_notes:string|null
     * } $match
     * @return array{
     *   recipient_source:string|null,
     *   recipient_id:string|null,
     *   match_status:string,
     *   match_notes:string|null
     * }
     */
    private function enhanceMatchNotesWithPhone(array $match, ?string $noTelepon): array
    {
        $rawPhone = trim((string) $noTelepon);
        $hasPhone = $rawPhone !== '';
        $isPhoneValid = $hasPhone && $this->normalizeWhatsappTarget($rawPhone) !== null;

        if ($match['match_status'] === 'unmatched') {
            if ($hasPhone && $isPhoneValid) {
                $match['match_notes'] = 'Data siswa tidak ditemukan di database siswa. No telepon tersedia untuk blast langsung.';
            } elseif ($hasPhone && !$isPhoneValid) {
                $match['match_notes'] = 'Data siswa tidak ditemukan di database siswa. No telepon tidak valid.';
            } else {
                $match['match_notes'] = 'Data siswa tidak ditemukan di database siswa.';
            }
        }

        return $match;
    }

    private function normalizeStoredPhone(string $phone): ?string
    {
        $normalized = trim($phone);
        return $normalized === '' ? null : $normalized;
    }

    /**
     * @param Collection<int, TunggakanRecord> $records
     */
    private function buildPseudoRecipientForDirectPhone(
        string $normalizedPhone,
        Collection $records
    ): BlastRecipient {
        $first = $records->first();

        $names = $records->pluck('nama_murid')
            ->map(fn ($value): string => trim((string) $value))
            ->filter(fn (string $value): bool => $value !== '')
            ->unique()
            ->values();

        $classes = $records->pluck('kelas')
            ->map(fn ($value): string => trim((string) $value))
            ->filter(fn (string $value): bool => $value !== '')
            ->unique()
            ->values();

        return new BlastRecipient([
            'id' => '',
            'nama_siswa' => $names->isNotEmpty() ? $names->implode(', ') : (string) ($first?->nama_murid ?? 'Siswa'),
            'kelas' => $classes->isNotEmpty() ? $classes->implode(', ') : '-',
            'nama_wali' => 'Bapak/Ibu Wali',
            'wa_wali' => $normalizedPhone,
            'wa_wali_2' => null,
            'email_wali' => null,
            'source' => 'siswa',
            'is_valid' => true,
        ]);
    }
}
