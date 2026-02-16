<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GenerateProfitLossReportRequest extends FormRequest
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
            'report_type' => 'required|string|in:MONTHLY,YEARLY',
            'year' => 'required|integer|digits:4|between:1900,2100',
            'month' => 'nullable|integer|between:1,12|required_if:report_type,MONTHLY',
            'opening_balance' => 'required|numeric',
            'entries' => 'required|array|min:1',
            'entries.*.type' => 'required|string|in:INCOME,EXPENSE',
            'entries.*.line_code' => 'required|string|max:64|distinct',
            'entries.*.line_label' => 'required|string|max:255',
            'entries.*.description' => 'nullable|string|max:500',
            'entries.*.amount' => 'required|numeric|min:0',
            'entries.*.is_depreciation' => 'nullable|boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        $reportType = strtoupper((string) $this->input('report_type'));
        $entries = array_map(
            static function ($entry): array {
                $entry = is_array($entry) ? $entry : [];
                $description = isset($entry['description']) ? trim((string) $entry['description']) : null;

                return [
                    'type' => strtoupper((string) ($entry['type'] ?? '')),
                    'line_code' => isset($entry['line_code']) ? (string) $entry['line_code'] : null,
                    'line_label' => isset($entry['line_label']) ? (string) $entry['line_label'] : null,
                    'description' => $description === '' ? null : $description,
                    'amount' => $entry['amount'] ?? null,
                    'is_depreciation' => (bool) ($entry['is_depreciation'] ?? false),
                ];
            },
            (array) $this->input('entries', [])
        );

        $this->merge([
            'report_type' => $reportType,
            'opening_balance' => $this->input('opening_balance', 0),
            'entries' => $entries,
        ]);

        if ($reportType === 'YEARLY') {
            $this->merge(['month' => null]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator): void {
            foreach ((array) $this->input('entries', []) as $index => $entry) {
                $type = strtoupper((string) ($entry['type'] ?? ''));
                $isDepreciation = (bool) ($entry['is_depreciation'] ?? false);

                if ($type === 'INCOME' && $isDepreciation) {
                    $validator->errors()->add(
                        "entries.$index.is_depreciation",
                        'Baris INCOME tidak boleh ditandai sebagai penyusutan.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'report_type.required' => 'Tipe laporan wajib dipilih.',
            'report_type.in' => 'Tipe laporan harus MONTHLY atau YEARLY.',
            'year.required' => 'Tahun wajib diisi.',
            'year.digits' => 'Tahun harus 4 digit.',
            'year.between' => 'Tahun tidak valid.',
            'month.required_if' => 'Bulan wajib diisi untuk laporan bulanan.',
            'month.between' => 'Bulan harus antara 1 sampai 12.',
            'opening_balance.required' => 'Saldo awal wajib diisi.',
            'opening_balance.numeric' => 'Saldo awal harus berupa angka.',
            'entries.required' => 'Minimal satu baris laba-rugi wajib diisi.',
            'entries.min' => 'Minimal satu baris laba-rugi wajib diisi.',
            'entries.*.type.required' => 'Jenis akun wajib diisi.',
            'entries.*.type.in' => 'Jenis akun harus INCOME atau EXPENSE.',
            'entries.*.line_code.required' => 'Kode akun wajib diisi.',
            'entries.*.line_code.distinct' => 'Kode akun tidak boleh duplikat dalam satu laporan.',
            'entries.*.line_label.required' => 'Nama akun wajib diisi.',
            'entries.*.description.max' => 'Keterangan maksimal 500 karakter.',
            'entries.*.amount.required' => 'Nominal wajib diisi.',
            'entries.*.amount.numeric' => 'Nominal harus berupa angka.',
            'entries.*.amount.min' => 'Nominal minimal 0.',
        ];
    }
}
