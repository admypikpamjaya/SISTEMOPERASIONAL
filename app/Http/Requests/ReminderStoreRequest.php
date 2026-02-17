<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReminderStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'type' => strtoupper((string) $this->input('type', 'GENERAL')),
            'announcement_id' => $this->input('announcement_id') === '' ? null : $this->input('announcement_id'),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'remind_at' => ['required', 'date'],
            'alert_before_minutes' => ['required', 'integer', 'min:1', 'max:10080'],
            'type' => ['required', 'in:GENERAL,ANNOUNCEMENT'],
            'announcement_id' => ['nullable', 'exists:announcements,id'],
        ];
    }
}
