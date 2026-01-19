<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BillingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => ['required', 'exists:parent_users,id'],
            'amount'    => ['required', 'numeric', 'min:0'],
            'due_date'  => ['required', 'date'],
        ];
    }
}
