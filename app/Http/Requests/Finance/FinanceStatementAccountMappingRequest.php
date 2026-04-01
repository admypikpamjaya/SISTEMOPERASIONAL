<?php

namespace App\Http\Requests\Finance;

use App\Models\FinanceAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinanceStatementAccountMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $allowedTypes = collect(FinanceAccount::manualStatementTypeOptions())
            ->flatMap(static fn (array $options): array => array_keys($options))
            ->values()
            ->all();

        return [
            'account_code' => ['required', 'string', 'max:64'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'statement_type' => ['required', 'string', Rule::in($allowedTypes)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'account_code' => strtoupper(trim((string) $this->input('account_code', ''))),
            'account_name' => trim((string) $this->input('account_name', '')),
            'statement_type' => strtoupper(trim((string) $this->input('statement_type', ''))),
        ]);
    }

    public function messages(): array
    {
        return [
            'account_code.required' => 'Kode akun wajib diisi.',
            'account_code.max' => 'Kode akun maksimal 64 karakter.',
            'account_name.max' => 'Nama akun maksimal 255 karakter.',
            'statement_type.required' => 'Kategori laporan wajib dipilih.',
            'statement_type.in' => 'Kategori laporan yang dipilih tidak valid.',
        ];
    }
}
