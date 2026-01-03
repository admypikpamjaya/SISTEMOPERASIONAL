<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class AuthenticationRequest extends FormRequest
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
            'username' => 'required|string',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Field username wajib diisi.',
            'username.string' => 'Field username harus berupa string.',

            'password.required' => 'Field password wajib diisi.',
            'password.string' => 'Field password harus berupa string.',

            'remember_me.boolean' => 'Field remember me harus berupa boolean.'
        ];
    }
}
