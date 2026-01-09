<?php

namespace App\Http\Requests\auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|confirmed'
        ];
    }

    public function messages(): array 
    {
        return [
            'token.required' => 'Field token wajib disertakan',
            'token.string' => 'Field token harus berupa string',

            'email.required' => 'Field email wajib diisi',
            'email.email' => 'Field email harus berupa email.',
            'email.exists' => 'Email tidak valid',

            'password.required' => 'Field password wajib diisi',
            'password.string' => 'Field password harus berupa string',
            'password.confirmed' => 'Password tidak cocok'
        ];
    }
}
