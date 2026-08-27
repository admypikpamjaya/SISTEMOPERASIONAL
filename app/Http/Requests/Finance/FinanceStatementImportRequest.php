<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinanceStatementImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'category_id' => [
                'required',
                'uuid',
                Rule::exists('finance_categories', 'id')->where('status', 'active'),
            ],
            'audit_status' => ['nullable', Rule::in(['UNAUDITED', 'PRE_AUDIT', 'AUDITED'])],
            'batch_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:5000',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'batch_name' => trim((string) $this->input('batch_name', '')) ?: null,
            'category_id' => trim((string) $this->input('category_id', '')),
            'notes' => trim((string) $this->input('notes', '')) ?: null,
        ]);
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File Excel laporan wajib dipilih.',
            'file.file' => 'File import tidak valid.',
            'file.mimes' => 'Format file harus xlsx, xls, atau csv.',
            'category_id.required' => 'Kategori finance wajib dipilih.',
            'category_id.exists' => 'Kategori finance disembunyikan atau tidak ditemukan.',
            'batch_name.max' => 'Nama batch maksimal 255 karakter.',
            'notes.max' => 'Catatan import maksimal 5000 karakter.',
        ];
    }
}
