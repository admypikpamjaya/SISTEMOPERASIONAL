<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class GenerateReportRequest extends FormRequest
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
            'period_id' => 'required|string|exists:finance_periods,id',
            'reconciliation_snapshot_id' => 'required|string|exists:finance_reconciliation_snapshots,id',
            'report_type' => 'required|string|in:MONTHLY,YEARLY',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|digits:4|between:1900,2100',
            'total_income' => 'required|numeric|min:0',
            'total_expense' => 'required|numeric|min:0',
            'total_depreciation' => 'required|numeric|min:0',
            'net_result' => 'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'period_id.required' => 'Periode wajib disertakan.',
            'period_id.exists' => 'Periode tidak ditemukan.',
            'reconciliation_snapshot_id.required' => 'Snapshot rekonsiliasi wajib disertakan.',
            'reconciliation_snapshot_id.exists' => 'Snapshot rekonsiliasi tidak ditemukan.',
            'report_type.required' => 'Tipe laporan wajib diisi.',
            'report_type.in' => 'Tipe laporan harus MONTHLY atau YEARLY.',
            'month.required' => 'Bulan wajib diisi.',
            'month.integer' => 'Bulan harus berupa angka.',
            'month.between' => 'Bulan harus antara 1 sampai 12.',
            'year.required' => 'Tahun wajib diisi.',
            'year.integer' => 'Tahun harus berupa angka.',
            'year.digits' => 'Tahun harus 4 digit.',
            'year.between' => 'Tahun tidak valid.',
            'total_income.required' => 'Total income wajib diisi.',
            'total_income.numeric' => 'Total income harus berupa angka.',
            'total_income.min' => 'Total income minimal 0.',
            'total_expense.required' => 'Total expense wajib diisi.',
            'total_expense.numeric' => 'Total expense harus berupa angka.',
            'total_expense.min' => 'Total expense minimal 0.',
            'total_depreciation.required' => 'Total depreciation wajib diisi.',
            'total_depreciation.numeric' => 'Total depreciation harus berupa angka.',
            'total_depreciation.min' => 'Total depreciation minimal 0.',
            'net_result.required' => 'Net result wajib diisi.',
            'net_result.numeric' => 'Net result harus berupa angka.',
        ];
    }
}
