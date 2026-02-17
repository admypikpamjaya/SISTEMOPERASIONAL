<?php

namespace App\Http\Requests\Finance;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class FinanceReportIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'period_type' => 'nullable|string|in:ALL,DAILY,MONTHLY,YEARLY',
            'report_date' => 'nullable|date',
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|digits:4|between:1900,2100',
            'report_type' => 'nullable|string|in:DAILY,MONTHLY,YEARLY',
            'comparison_type' => 'nullable|string|in:NONE,PREVIOUS_PERIOD,SAME_PERIOD_LAST_YEAR,SPECIFIC_DATE',
            'comparison_offset' => 'nullable|integer|min:1|max:36',
            'comparison_date' => 'nullable|date|required_if:comparison_type,SPECIFIC_DATE',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    protected function prepareForValidation(): void
    {
        $periodType = strtoupper((string) $this->input('period_type', 'MONTHLY'));
        if ($this->filled('report_type') && !$this->filled('period_type')) {
            $periodType = strtoupper((string) $this->input('report_type'));
        }
        $comparisonType = strtoupper((string) $this->input('comparison_type', 'NONE'));
        $reportDate = $this->input('report_date');
        if ($periodType === 'DAILY' && empty($reportDate)) {
            $reportDate = now()->toDateString();
        }

        $payload = [
            'period_type' => $periodType,
            'comparison_type' => $comparisonType,
            'report_date' => $reportDate,
        ];

        if ($periodType === 'DAILY' && !empty($reportDate)) {
            $date = Carbon::parse((string) $reportDate);
            $payload['year'] = (int) $date->year;
            $payload['month'] = (int) $date->month;
        }

        if ($periodType === 'YEARLY') {
            $payload['month'] = null;
        }

        if ($periodType === 'ALL') {
            $payload['report_date'] = null;
            $payload['year'] = null;
            $payload['month'] = null;
            $payload['comparison_type'] = 'NONE';
            $payload['comparison_offset'] = null;
            $payload['comparison_date'] = null;
        }

        if ($comparisonType !== 'PREVIOUS_PERIOD') {
            $payload['comparison_offset'] = null;
        }

        if ($comparisonType !== 'SPECIFIC_DATE') {
            $payload['comparison_date'] = null;
        }

        $this->merge($payload);
    }

    public function messages(): array
    {
        return [
            'period_type.in' => 'Tipe periode harus ALL, DAILY, MONTHLY, atau YEARLY.',
            'report_date.date' => 'Format tanggal laporan tidak valid.',
            'comparison_type.in' => 'Tipe perbandingan tidak valid.',
            'comparison_offset.min' => 'Offset perbandingan minimal 1 periode.',
            'comparison_offset.max' => 'Offset perbandingan maksimal 36 periode.',
            'comparison_date.required_if' => 'Tanggal perbandingan wajib diisi untuk mode tanggal.',
            'comparison_date.date' => 'Tanggal perbandingan tidak valid.',
        ];
    }
}
