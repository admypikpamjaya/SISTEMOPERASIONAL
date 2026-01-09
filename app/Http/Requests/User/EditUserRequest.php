<?php

namespace App\Http\Requests\User;

use App\Enums\User\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditUserRequest extends FormRequest
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
            'id' => 'required|string',
            'name' => 'required|string',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->id)],
            'role' => ['required', Rule::enum(UserRole::class)]
        ];
    }

    public function messages(): array 
    {
        return [
            'id.required' => 'Field id wajib disertakan',

            'name.required' => 'Field nama wajib diisi',

            'email.required' => 'Field email wajib diisi',
            'email.email' => 'Field email harus berupa email.',
            'email.unique' => 'Email sudah digunakan',

            'role.required' => 'Field role wajib diisi',
            'role.*' => 'Role tidak valid'
        ];
    }
}
