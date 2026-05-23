<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SendMaintenanceNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'manual_recipients' => trim((string) $this->input('manual_recipients', '')),
        ]);
    }

    public function rules(): array
    {
        return [
            'manual_recipients' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $invalidRecipients = collect($this->parseRecipients())
                ->filter(static fn (string $recipient): bool => filter_var($recipient, FILTER_VALIDATE_EMAIL) === false)
                ->values()
                ->all();

            if ($invalidRecipients !== []) {
                $validator->errors()->add(
                    'manual_recipients',
                    'Ada email manual yang tidak valid: ' . implode(', ', $invalidRecipients)
                );
            }
        });
    }

    public function manualRecipients(): array
    {
        return collect($this->parseRecipients())
            ->filter(static fn (string $recipient): bool => filter_var($recipient, FILTER_VALIDATE_EMAIL) !== false)
            ->values()
            ->all();
    }

    public function messages(): array
    {
        return [
            'manual_recipients.max' => 'Daftar email manual terlalu panjang.',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function parseRecipients(): array
    {
        $rawValue = (string) $this->input('manual_recipients', '');
        if ($rawValue === '') {
            return [];
        }

        $parts = preg_split('/[\s,;\r\n]+/', $rawValue) ?: [];

        return collect($parts)
            ->map(static fn ($recipient): string => trim((string) $recipient))
            ->filter(static fn (string $recipient): bool => $recipient !== '')
            ->unique(static fn (string $recipient): string => strtolower($recipient))
            ->values()
            ->all();
    }
}
