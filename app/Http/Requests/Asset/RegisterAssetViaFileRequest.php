<?php

namespace App\Http\Requests\Asset;

use App\Enums\Asset\AssetCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterAssetViaFileRequest extends FormRequest
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
            'category' => ['required', Rule::enum(AssetCategory::class)],
            'file' => ['required', 'file', 'mimes:csv']
        ];
    }

    public function messages()
    {
        return [
            'category.required' => 'Kategori tidak boleh kosong',
            'category.*' => 'Kategori tidak valid',

            'file.required' => 'File tidak boleh kosong',
            'file.file' => 'File harus berupa file',
            'file.mimes' => 'File harus berupa file CSV'
        ];
    }
}
