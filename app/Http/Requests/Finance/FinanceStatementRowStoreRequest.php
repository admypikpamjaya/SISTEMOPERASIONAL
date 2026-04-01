<?php

namespace App\Http\Requests\Finance;

use App\Models\FinanceStatementBatch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class FinanceStatementRowStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'statement_type' => [
                'required',
                'string',
                Rule::in([
                    FinanceStatementBatch::TYPE_BALANCE_SHEET,
                    FinanceStatementBatch::TYPE_PROFIT_LOSS,
                ]),
            ],
            'batch_id' => 'nullable|uuid|exists:finance_statement_batches,id',
            'section_key' => 'required|string|max:60',
            'section_label' => 'nullable|string|max:120',
            'group_label' => 'nullable|string|max:255',
            'account_code' => 'nullable|string|max:64',
            'account_name' => 'required|string|max:255',
            'finance_type' => 'nullable|string|max:60',
            'amount' => 'required|numeric',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'statement_type' => strtoupper(trim((string) $this->input('statement_type', ''))),
            'section_key' => strtolower(trim((string) $this->input('section_key', ''))),
            'section_label' => trim((string) $this->input('section_label', '')) ?: null,
            'group_label' => trim((string) $this->input('group_label', '')) ?: null,
            'account_code' => strtoupper(trim((string) $this->input('account_code', ''))) ?: null,
            'account_name' => trim((string) $this->input('account_name', '')),
            'finance_type' => strtoupper(trim((string) $this->input('finance_type', ''))) ?: null,
            'amount' => $this->input('amount', 0),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $statementType = (string) $this->input('statement_type');
            $sectionKey = (string) $this->input('section_key');

            $validSections = match ($statementType) {
                FinanceStatementBatch::TYPE_BALANCE_SHEET => ['liabilitas', 'piutang', 'kas', 'aset', 'other'],
                FinanceStatementBatch::TYPE_PROFIT_LOSS => ['income', 'expense'],
                default => [],
            };

            if (!in_array($sectionKey, $validSections, true)) {
                $validator->errors()->add('section_key', 'Kategori baris tidak cocok dengan jenis laporan yang dipilih.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'statement_type.required' => 'Jenis laporan wajib dipilih.',
            'statement_type.in' => 'Jenis laporan tidak valid.',
            'batch_id.uuid' => 'Batch laporan tidak valid.',
            'batch_id.exists' => 'Batch laporan tidak ditemukan.',
            'section_key.required' => 'Kategori laporan wajib dipilih.',
            'section_key.max' => 'Kategori laporan terlalu panjang.',
            'section_label.max' => 'Label kategori maksimal 120 karakter.',
            'group_label.max' => 'Label grup maksimal 255 karakter.',
            'account_code.max' => 'Kode akun maksimal 64 karakter.',
            'account_name.required' => 'Nama baris wajib diisi.',
            'account_name.max' => 'Nama baris maksimal 255 karakter.',
            'finance_type.max' => 'Tipe finance maksimal 60 karakter.',
            'amount.required' => 'Nominal wajib diisi.',
            'amount.numeric' => 'Nominal harus berupa angka.',
        ];
    }
}
