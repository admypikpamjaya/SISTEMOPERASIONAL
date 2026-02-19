<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class FinanceInvoiceStoreRequest extends FormRequest
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
            'action' => 'nullable|string|in:save_draft,post',
            'accounting_date' => 'required|date',
            'entry_type' => 'required|string|in:INCOME,EXPENSE',
            'journal_name' => 'required|string|max:255',
            'reference' => 'nullable|string|max:255',
            'initial_note' => 'nullable|string|max:5000',
            'items' => 'required|array|min:1',
            'items.*.asset_category' => 'nullable|string|max:120',
            'items.*.account_code' => 'required|string|max:64',
            'items.*.partner_name' => 'nullable|string|max:255',
            'items.*.label' => 'required|string|max:255',
            'items.*.analytic_distribution' => 'nullable|string|max:255',
            'items.*.debit' => 'nullable|numeric|min:0',
            'items.*.credit' => 'nullable|numeric|min:0',
        ];
    }

    protected function prepareForValidation(): void
    {
        $items = array_values(
            array_map(
                static function ($item): array {
                    $item = is_array($item) ? $item : [];

                    return [
                        'asset_category' => isset($item['asset_category']) && trim((string) $item['asset_category']) !== ''
                            ? trim((string) $item['asset_category'])
                            : null,
                        'account_code' => isset($item['account_code'])
                            ? trim((string) $item['account_code'])
                            : '',
                        'partner_name' => isset($item['partner_name']) && trim((string) $item['partner_name']) !== ''
                            ? trim((string) $item['partner_name'])
                            : null,
                        'label' => isset($item['label']) ? trim((string) $item['label']) : '',
                        'analytic_distribution' => isset($item['analytic_distribution']) && trim((string) $item['analytic_distribution']) !== ''
                            ? trim((string) $item['analytic_distribution'])
                            : null,
                        'debit' => $item['debit'] ?? 0,
                        'credit' => $item['credit'] ?? 0,
                    ];
                },
                (array) $this->input('items', [])
            )
        );

        $this->merge([
            'action' => strtolower((string) $this->input('action', 'save_draft')),
            'entry_type' => strtoupper((string) $this->input('entry_type')),
            'journal_name' => trim((string) $this->input('journal_name', '')),
            'reference' => trim((string) $this->input('reference', '')) ?: null,
            'initial_note' => trim((string) $this->input('initial_note', '')) ?: null,
            'items' => $items,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $items = (array) $this->input('items', []);
            $action = strtolower((string) $this->input('action', 'save_draft'));

            $totalDebit = 0.0;
            $totalCredit = 0.0;

            foreach ($items as $index => $item) {
                $debit = (float) ($item['debit'] ?? 0);
                $credit = (float) ($item['credit'] ?? 0);

                $totalDebit += $debit;
                $totalCredit += $credit;

                if ($debit > 0 && $credit > 0) {
                    $validator->errors()->add(
                        "items.$index.debit",
                        'Baris jurnal tidak boleh berisi debit dan kredit sekaligus.'
                    );
                }

                if ($debit <= 0 && $credit <= 0) {
                    $validator->errors()->add(
                        "items.$index.debit",
                        'Minimal salah satu debit atau kredit harus diisi.'
                    );
                }
            }

            if ($action === 'post') {
                if ($totalDebit <= 0 || $totalCredit <= 0) {
                    $validator->errors()->add(
                        'items',
                        'Untuk merekam jurnal, total debit dan kredit harus lebih dari 0.'
                    );
                }

                if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                    $validator->errors()->add(
                        'items',
                        'Untuk merekam jurnal, total debit dan kredit harus seimbang.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'accounting_date.required' => 'Tanggal akuntansi wajib diisi.',
            'accounting_date.date' => 'Tanggal akuntansi tidak valid.',
            'entry_type.required' => 'Jenis transaksi wajib dipilih.',
            'entry_type.in' => 'Jenis transaksi harus INCOME atau EXPENSE.',
            'journal_name.required' => 'Nama jurnal wajib diisi.',
            'journal_name.max' => 'Nama jurnal maksimal 255 karakter.',
            'reference.max' => 'Referensi maksimal 255 karakter.',
            'initial_note.max' => 'Catatan awal maksimal 5000 karakter.',
            'items.required' => 'Minimal satu item jurnal wajib diisi.',
            'items.min' => 'Minimal satu item jurnal wajib diisi.',
            'items.*.account_code.required' => 'Akun wajib diisi pada setiap baris.',
            'items.*.label.required' => 'Label wajib diisi pada setiap baris.',
            'items.*.debit.numeric' => 'Debit harus berupa angka.',
            'items.*.debit.min' => 'Debit tidak boleh negatif.',
            'items.*.credit.numeric' => 'Kredit harus berupa angka.',
            'items.*.credit.min' => 'Kredit tidak boleh negatif.',
        ];
    }
}
