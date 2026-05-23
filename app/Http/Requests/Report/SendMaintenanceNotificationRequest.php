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
        $selectedDashboardRecipientIds = $this->input('selected_dashboard_recipient_ids', []);
        if (!is_array($selectedDashboardRecipientIds)) {
            $selectedDashboardRecipientIds = [$selectedDashboardRecipientIds];
        }

        $this->merge([
            'manual_recipients' => trim((string) $this->input('manual_recipients', '')),
            'selected_dashboard_recipient_ids' => collect($selectedDashboardRecipientIds)
                ->map(static fn ($recipientId): string => trim((string) $recipientId))
                ->filter(static fn (string $recipientId): bool => $recipientId !== '')
                ->unique()
                ->values()
                ->all(),
        ]);
    }

    public function rules(): array
    {
        return [
            'manual_recipients' => ['nullable', 'string', 'max:2000'],
            'selected_dashboard_recipient_ids' => ['nullable', 'array'],
            'selected_dashboard_recipient_ids.*' => ['string', 'exists:maintenance_notification_recipients,id'],
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

    public function selectedDashboardRecipientIds(): array
    {
        return collect($this->input('selected_dashboard_recipient_ids', []))
            ->map(static fn ($recipientId): string => trim((string) $recipientId))
            ->filter(static fn (string $recipientId): bool => $recipientId !== '')
            ->values()
            ->all();
    }

    public function messages(): array
    {
        return [
            'manual_recipients.max' => 'Daftar email manual terlalu panjang.',
            'selected_dashboard_recipient_ids.array' => 'Daftar email dashboard yang dipilih tidak valid.',
            'selected_dashboard_recipient_ids.*.exists' => 'Ada email dashboard yang dipilih tetapi sudah tidak tersedia.',
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
