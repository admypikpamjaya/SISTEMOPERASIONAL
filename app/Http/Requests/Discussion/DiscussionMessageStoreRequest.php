<?php

namespace App\Http\Requests\Discussion;

use Illuminate\Foundation\Http\FormRequest;

class DiscussionMessageStoreRequest extends FormRequest
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
            'channel_id' => ['required', 'integer', 'exists:discussion_channels,id'],
            'message' => ['nullable', 'string', 'max:5000', 'required_without_all:attachment,voice_note'],
            'attachment' => [
                'nullable',
                'file',
                'max:10240',
                'required_without_all:message,voice_note',
                'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,csv,txt,zip,rar',
            ],
            'voice_note' => [
                'nullable',
                'file',
                'max:10240',
                'required_without_all:message,attachment',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (!$value instanceof \Illuminate\Http\UploadedFile) {
                        return;
                    }

                    $mime = strtolower((string) $value->getMimeType());
                    $extension = strtolower((string) $value->getClientOriginalExtension());
                    $allowedExtensions = ['webm', 'ogg', 'oga', 'mp3', 'wav', 'wave', 'm4a', 'aac', 'mp4', '3gp', 'amr'];

                    if (str_starts_with($mime, 'audio/')) {
                        return;
                    }

                    if (in_array($extension, $allowedExtensions, true)) {
                        return;
                    }

                    $fail('Format voice note tidak didukung.');
                },
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $message = trim((string) $this->input('message', ''));

        $this->merge([
            'message' => $message === '' ? null : $message,
        ]);
    }

    public function messages(): array
    {
        return [
            'message.required_without_all' => 'Isi pesan, upload file, atau kirim voice note.',
            'attachment.required_without_all' => 'Isi pesan, upload file, atau kirim voice note.',
            'voice_note.required_without_all' => 'Isi pesan, upload file, atau kirim voice note.',
            'attachment.max' => 'Ukuran file maksimal 10 MB.',
            'attachment.mimes' => 'Format file tidak didukung.',
            'voice_note.max' => 'Ukuran voice note maksimal 10 MB.',
        ];
    }
}
