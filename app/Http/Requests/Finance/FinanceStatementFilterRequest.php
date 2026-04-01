<?php

namespace App\Http\Requests\Finance;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class FinanceStatementFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period_type' => 'nullable|string|in:ALL,DAILY,MONTHLY,YEARLY',
            'report_date' => 'nullable|date',
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|digits:4|between:1900,2100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'start_month' => 'nullable|integer|between:1,12',
            'end_month' => 'nullable|integer|between:1,12',
            'start_year' => 'nullable|integer|digits:4|between:1900,2100',
            'end_year' => 'nullable|integer|digits:4|between:1900,2100',
            'account_code' => 'nullable|string|max:100',
            'search' => 'nullable|string|max:255',
            'statement_source' => 'nullable|string|in:balance_sheet,profit_loss,general_ledger',
            'statement_data_source' => 'nullable|string|in:system,imported',
            'statement_batch_id' => 'nullable|uuid',
            'ledger_source' => 'nullable|string|in:system,imported',
            'ledger_batch_id' => 'nullable|uuid',
            'selected_ids' => 'nullable|array',
            'selected_ids.*' => 'integer|min:1',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    protected function prepareForValidation(): void
    {
        $statementDataSource = strtolower(trim((string) $this->input('statement_data_source', 'system')));
        $ledgerSource = strtolower(trim((string) $this->input('ledger_source', 'system')));
        $hasExplicitPeriodInput = $this->filled('period_type')
            || $this->filled('report_date')
            || $this->filled('month')
            || $this->filled('year')
            || $this->filled('start_date')
            || $this->filled('end_date')
            || $this->filled('start_month')
            || $this->filled('end_month')
            || $this->filled('start_year')
            || $this->filled('end_year');

        $hasImportedSource = $statementDataSource === 'imported' || $ledgerSource === 'imported';

        $defaultPeriodType = $hasImportedSource && !$hasExplicitPeriodInput
            ? 'ALL'
            : 'MONTHLY';

        $periodType = strtoupper((string) $this->input('period_type', $defaultPeriodType));
        $referenceDate = $this->resolveDefaultStatementDate();
        $currentYear = (int) $referenceDate->year;
        $currentMonth = (int) $referenceDate->month;
        $legacyReportDate = $this->input('report_date');
        $legacyMonth = $this->input('month');
        $legacyYear = $this->input('year');

        $startDateInput = $this->input('start_date', $legacyReportDate);
        $endDateInput = $this->input('end_date', $legacyReportDate);
        $startMonthInput = $this->input('start_month', $legacyMonth);
        $endMonthInput = $this->input('end_month', $legacyMonth);
        $startYearInput = $this->input('start_year', $legacyYear);
        $endYearInput = $this->input('end_year', $legacyYear);

        $payload = [
            'period_type' => $periodType,
            'search' => $this->filled('search') ? trim((string) $this->input('search')) : null,
            'statement_source' => $this->filled('statement_source')
                ? strtolower(trim((string) $this->input('statement_source')))
                : null,
            'statement_data_source' => $statementDataSource,
            'statement_batch_id' => $this->filled('statement_batch_id')
                ? trim((string) $this->input('statement_batch_id'))
                : null,
            'ledger_source' => $ledgerSource,
            'ledger_batch_id' => $this->filled('ledger_batch_id')
                ? trim((string) $this->input('ledger_batch_id'))
                : null,
        ];

        if ($periodType === 'DAILY') {
            $startDate = !empty($startDateInput)
                ? Carbon::parse((string) $startDateInput)->startOfDay()
                : $referenceDate->copy()->startOfDay();
            $endDate = !empty($endDateInput)
                ? Carbon::parse((string) $endDateInput)->startOfDay()
                : $startDate->copy();

            if ($startDate->gt($endDate)) {
                [$startDate, $endDate] = [$endDate, $startDate];
            }

            $payload = array_merge($payload, [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'report_date' => $startDate->toDateString(),
                'year' => (int) $startDate->year,
                'month' => (int) $startDate->month,
                'start_year' => (int) $startDate->year,
                'start_month' => (int) $startDate->month,
                'end_year' => (int) $endDate->year,
                'end_month' => (int) $endDate->month,
            ]);
        }

        if ($periodType === 'MONTHLY') {
            $startYear = is_numeric($startYearInput) ? (int) $startYearInput : $currentYear;
            $startMonth = is_numeric($startMonthInput) ? (int) $startMonthInput : $currentMonth;
            $endYear = is_numeric($endYearInput) ? (int) $endYearInput : $startYear;
            $endMonth = is_numeric($endMonthInput) ? (int) $endMonthInput : $startMonth;

            $startPeriod = Carbon::create($startYear, $startMonth, 1)->startOfMonth();
            $endPeriod = Carbon::create($endYear, $endMonth, 1)->startOfMonth();

            if ($startPeriod->gt($endPeriod)) {
                [$startPeriod, $endPeriod] = [$endPeriod, $startPeriod];
            }

            $payload = array_merge($payload, [
                'start_date' => $startPeriod->toDateString(),
                'end_date' => $endPeriod->copy()->endOfMonth()->toDateString(),
                'report_date' => null,
                'month' => (int) $startPeriod->month,
                'year' => (int) $startPeriod->year,
                'start_month' => (int) $startPeriod->month,
                'start_year' => (int) $startPeriod->year,
                'end_month' => (int) $endPeriod->month,
                'end_year' => (int) $endPeriod->year,
            ]);
        }

        if ($periodType === 'YEARLY') {
            $startYear = is_numeric($startYearInput)
                ? (int) $startYearInput
                : (is_numeric($legacyYear) ? (int) $legacyYear : $currentYear);
            $endYear = is_numeric($endYearInput) ? (int) $endYearInput : $startYear;

            if ($startYear > $endYear) {
                [$startYear, $endYear] = [$endYear, $startYear];
            }

            $payload = array_merge($payload, [
                'start_date' => Carbon::create($startYear, 1, 1)->startOfYear()->toDateString(),
                'end_date' => Carbon::create($endYear, 1, 1)->endOfYear()->toDateString(),
                'report_date' => null,
                'month' => null,
                'year' => $startYear,
                'start_month' => 1,
                'start_year' => $startYear,
                'end_month' => 12,
                'end_year' => $endYear,
            ]);
        }

        if ($periodType === 'ALL') {
            $payload = array_merge($payload, [
                'report_date' => null,
                'month' => null,
                'year' => null,
                'start_date' => null,
                'end_date' => null,
                'start_month' => null,
                'end_month' => null,
                'start_year' => null,
                'end_year' => null,
            ]);
        }

        $this->merge($payload);
    }

    private function resolveDefaultStatementDate(): Carbon
    {
        $latestPostedAccountingDate = DB::table('finance_invoices')
            ->where('status', 'POSTED')
            ->max('accounting_date');

        if (!empty($latestPostedAccountingDate)) {
            return Carbon::parse((string) $latestPostedAccountingDate)->startOfDay();
        }

        return now()->startOfDay();
    }

    public function messages(): array
    {
        return [
            'period_type.in' => 'Tipe periode harus ALL, DAILY, MONTHLY, atau YEARLY.',
            'report_date.date' => 'Format tanggal tidak valid.',
            'start_date.date' => 'Format tanggal mulai tidak valid.',
            'end_date.date' => 'Format tanggal selesai tidak valid.',
            'month.between' => 'Bulan harus antara 1 sampai 12.',
            'start_month.between' => 'Bulan mulai harus antara 1 sampai 12.',
            'end_month.between' => 'Bulan selesai harus antara 1 sampai 12.',
            'year.digits' => 'Tahun harus 4 digit.',
            'year.between' => 'Tahun harus antara 1900 sampai 2100.',
            'start_year.digits' => 'Tahun mulai harus 4 digit.',
            'start_year.between' => 'Tahun mulai harus antara 1900 sampai 2100.',
            'end_year.digits' => 'Tahun selesai harus 4 digit.',
            'end_year.between' => 'Tahun selesai harus antara 1900 sampai 2100.',
            'account_code.max' => 'Kode akun maksimal 100 karakter.',
            'search.max' => 'Pencarian maksimal 255 karakter.',
            'statement_source.in' => 'Sumber laporan tidak valid.',
            'statement_data_source.in' => 'Sumber data laporan tidak valid.',
            'statement_batch_id.uuid' => 'Batch laporan tidak valid.',
            'ledger_source.in' => 'Sumber buku besar tidak valid.',
            'ledger_batch_id.uuid' => 'Batch buku besar tidak valid.',
            'selected_ids.array' => 'Daftar item terpilih tidak valid.',
            'selected_ids.*.integer' => 'Item terpilih tidak valid.',
        ];
    }
}
