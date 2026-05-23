<?php

namespace App\Http\Requests\Report;

use App\Services\Report\MaintenanceNotificationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreMaintenanceNotificationRecipientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->filled('name')
                ? trim((string) $this->input('name'))
                : null,
            'email' => strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'unique:maintenance_notification_recipients,email'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (
                strtolower((string) $this->input('email')) === strtolower(MaintenanceNotificationService::MASTER_RECIPIENT)
            ) {
                $validator->errors()->add(
                    'email',
                    'Email master sudah aktif otomatis dan tidak perlu ditambahkan lagi.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.max' => 'Nama penerima maksimal 120 karakter.',
            'email.required' => 'Email penerima wajib diisi.',
            'email.email' => 'Format email penerima tidak valid.',
            'email.max' => 'Email penerima maksimal 255 karakter.',
            'email.unique' => 'Email penerima tersebut sudah terdaftar.',
        ];
    }
}
