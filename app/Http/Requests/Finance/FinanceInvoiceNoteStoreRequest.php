<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class FinanceInvoiceNoteStoreRequest extends FormRequest
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
            'note' => 'required|string|max:5000',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'note' => trim((string) $this->input('note', '')),
        ]);
    }

    public function messages(): array
    {
        return [
            'note.required' => 'Catatan wajib diisi.',
            'note.max' => 'Catatan maksimal 5000 karakter.',
        ];
    }
}
