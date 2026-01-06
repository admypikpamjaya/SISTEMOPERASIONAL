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
            'username' => ['required', 'string', Rule::unique('users', 'username')->ignore($this->id)],
            'role' => ['required', Rule::enum(UserRole::class)]
        ];
    }

    public function messages(): array 
    {
        return [
            'id.required' => 'Field id wajib disertakan',

            'name.required' => 'Field nama wajib diisi',

            'username.required' => 'Field username wajib diisi',
            'username.unique' => 'Username sudah digunakan',

            'role.required' => 'Field role wajib diisi',
            'role.*' => 'Role tidak valid'
        ];
    }
}
