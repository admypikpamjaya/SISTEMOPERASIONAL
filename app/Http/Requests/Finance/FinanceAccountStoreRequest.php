<?php

namespace App\Http\Requests\Finance;

use App\Models\FinanceAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinanceAccountStoreRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:64', Rule::unique('finance_accounts', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(FinanceAccount::allowedTypes())],
            'class_no' => ['required', 'integer', Rule::in(FinanceAccount::classOrder())],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $isActive = filter_var(
            $this->input('is_active', false),
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );

        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code', ''))),
            'name' => trim((string) $this->input('name', '')),
            'type' => strtoupper(trim((string) $this->input('type', ''))),
            'class_no' => is_numeric($this->input('class_no'))
                ? (int) $this->input('class_no')
                : $this->input('class_no'),
            'is_active' => (bool) $isActive,
        ]);
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode akun wajib diisi.',
            'code.max' => 'Kode akun maksimal 64 karakter.',
            'code.unique' => 'Kode akun sudah digunakan.',
            'name.required' => 'Nama akun wajib diisi.',
            'name.max' => 'Nama akun maksimal 255 karakter.',
            'type.required' => 'Jenis akun wajib dipilih.',
            'type.in' => 'Jenis akun tidak valid.',
            'class_no.required' => 'No klasifikasi kiri wajib dipilih.',
            'class_no.integer' => 'No klasifikasi kiri tidak valid.',
            'class_no.in' => 'No klasifikasi kiri tidak tersedia.',
        ];
    }
}
