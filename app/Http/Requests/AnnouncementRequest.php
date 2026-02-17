<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // role check di phase 5.4
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'message'     => ['required', 'string'],
            'attachment'  => ['nullable', 'file', 'max:2048'],
            'channels'    => ['nullable', 'array'],
            'channels.*'  => ['in:email,whatsapp'],
            'reminder_id' => ['nullable', 'integer', 'exists:reminders,id'],
        ];
    }
}
