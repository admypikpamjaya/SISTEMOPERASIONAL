<?php

namespace App\Http\Requests\Finance;

use App\Models\FinanceGeneralLedgerEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class FinanceGeneralLedgerEntryStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'batch_id' => 'nullable|uuid|exists:finance_general_ledger_batches,id',
            'row_type' => 'required|string|in:' . FinanceGeneralLedgerEntry::ROW_TYPE_OPENING . ',' . FinanceGeneralLedgerEntry::ROW_TYPE_ENTRY,
            'entry_date' => 'required|date',
            'account_code' => 'required|string|max:64',
            'account_name' => 'required|string|max:255',
            'transaction_no' => 'nullable|string|max:120',
            'communication' => 'nullable|string|max:255',
            'partner_name' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:20',
            'label' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'analytic_distribution' => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric',
            'debit' => 'nullable|numeric|min:0',
            'credit' => 'nullable|numeric|min:0',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'row_type' => strtoupper(trim((string) $this->input('row_type', FinanceGeneralLedgerEntry::ROW_TYPE_ENTRY))),
            'account_code' => strtoupper(trim((string) $this->input('account_code', ''))),
            'account_name' => trim((string) $this->input('account_name', '')),
            'transaction_no' => trim((string) $this->input('transaction_no', '')) ?: null,
            'communication' => trim((string) $this->input('communication', '')) ?: null,
            'partner_name' => trim((string) $this->input('partner_name', '')) ?: null,
            'currency' => strtoupper(trim((string) $this->input('currency', ''))) ?: 'IDR',
            'label' => trim((string) $this->input('label', '')) ?: null,
            'reference' => trim((string) $this->input('reference', '')) ?: null,
            'analytic_distribution' => trim((string) $this->input('analytic_distribution', '')) ?: null,
            'opening_balance' => $this->input('opening_balance', 0),
            'debit' => $this->input('debit', 0),
            'credit' => $this->input('credit', 0),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $rowType = (string) $this->input('row_type', FinanceGeneralLedgerEntry::ROW_TYPE_ENTRY);
            $openingBalance = round((float) $this->input('opening_balance', 0), 2);
            $debit = round((float) $this->input('debit', 0), 2);
            $credit = round((float) $this->input('credit', 0), 2);

            if ($rowType === FinanceGeneralLedgerEntry::ROW_TYPE_OPENING) {
                if ($debit !== 0.0 || $credit !== 0.0) {
                    $validator->errors()->add('debit', 'Baris saldo awal tidak boleh berisi debit atau kredit.');
                }

                return;
            }

            if ($openingBalance !== 0.0) {
                $validator->errors()->add('opening_balance', 'Opening balance hanya dipakai untuk baris saldo awal.');
            }

            if ($debit > 0 && $credit > 0) {
                $validator->errors()->add('debit', 'Baris buku besar tidak boleh berisi debit dan kredit sekaligus.');
            }

            if ($debit <= 0 && $credit <= 0) {
                $validator->errors()->add('debit', 'Minimal salah satu debit atau kredit harus diisi.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'batch_id.uuid' => 'Batch buku besar tidak valid.',
            'batch_id.exists' => 'Batch buku besar tidak ditemukan.',
            'row_type.required' => 'Jenis baris wajib dipilih.',
            'row_type.in' => 'Jenis baris tidak valid.',
            'entry_date.required' => 'Tanggal buku besar wajib diisi.',
            'entry_date.date' => 'Tanggal buku besar tidak valid.',
            'account_code.required' => 'Kode akun wajib diisi.',
            'account_code.max' => 'Kode akun maksimal 64 karakter.',
            'account_name.required' => 'Nama akun wajib diisi.',
            'account_name.max' => 'Nama akun maksimal 255 karakter.',
            'transaction_no.max' => 'Nomor transaksi maksimal 120 karakter.',
            'communication.max' => 'Komunikasi maksimal 255 karakter.',
            'partner_name.max' => 'Rekanan maksimal 255 karakter.',
            'currency.max' => 'Mata uang maksimal 20 karakter.',
            'label.max' => 'Uraian maksimal 255 karakter.',
            'reference.max' => 'Referensi maksimal 255 karakter.',
            'analytic_distribution.max' => 'Analitik maksimal 255 karakter.',
            'opening_balance.numeric' => 'Opening balance harus berupa angka.',
            'debit.numeric' => 'Debit harus berupa angka.',
            'debit.min' => 'Debit tidak boleh negatif.',
            'credit.numeric' => 'Kredit harus berupa angka.',
            'credit.min' => 'Kredit tidak boleh negatif.',
        ];
    }
}
