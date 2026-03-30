<?php

namespace App\Http\Requests\Finance;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

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
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    protected function prepareForValidation(): void
    {
        $periodType = strtoupper((string) $this->input('period_type', 'MONTHLY'));
        $reportDate = $this->input('report_date');
        $currentYear = (int) now()->year;
        $currentMonth = (int) now()->month;

        if ($periodType === 'DAILY' && empty($reportDate)) {
            $reportDate = now()->toDateString();
        }

        $payload = [
            'period_type' => $periodType,
            'report_date' => $reportDate,
        ];

        if ($periodType === 'DAILY' && !empty($reportDate)) {
            $date = Carbon::parse((string) $reportDate);
            $payload['year'] = (int) $date->year;
            $payload['month'] = (int) $date->month;
        }

        if ($periodType === 'MONTHLY') {
            $payload['year'] = is_numeric($this->input('year'))
                ? (int) $this->input('year')
                : $currentYear;
            $payload['month'] = is_numeric($this->input('month'))
                ? (int) $this->input('month')
                : $currentMonth;
        }

        if ($periodType === 'YEARLY') {
            $payload['year'] = is_numeric($this->input('year'))
                ? (int) $this->input('year')
                : $currentYear;
            $payload['month'] = null;
        }

        if ($periodType === 'ALL') {
            $payload['report_date'] = null;
            $payload['month'] = null;
            $payload['year'] = null;
        }

        $this->merge($payload);
    }

    public function messages(): array
    {
        return [
            'period_type.in' => 'Tipe periode harus ALL, DAILY, MONTHLY, atau YEARLY.',
            'report_date.date' => 'Format tanggal tidak valid.',
            'month.between' => 'Bulan harus antara 1 sampai 12.',
            'year.digits' => 'Tahun harus 4 digit.',
            'year.between' => 'Tahun harus antara 1900 sampai 2100.',
        ];
    }
}
