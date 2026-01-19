<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlastRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel' => ['required', 'in:email,whatsapp'],
            'message' => ['required', 'string'],
            'target'  => ['required', 'array'],
            'target.*'=> ['required', 'integer'],
        ];
    }
}
