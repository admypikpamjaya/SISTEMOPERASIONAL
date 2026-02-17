<?php

namespace App\Http\Requests\Asset;

use App\Enums\Asset\AssetCategory;
use App\Enums\Asset\AssetUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterAssetRequest extends FormRequest
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
        $category = strtoupper((string) $this->input('category'));
        $useAcLikeDetail = in_array($category, ['AC', 'OTHER'], true);

        return [
            'category' => ['required', Rule::enum(AssetCategory::class)],
            'account_code' => ['required', 'unique:assets,account_code'],
            'asset_serial_number' => ['nullable', 'unique:assets,serial_number'],
            'unit' => ['required', Rule::enum(AssetUnit::class)],
            'location' => ['required', 'string'],
            'purchase_year'=> ['nullable', 'string'],
            'detail' => ['nullable', 'array', Rule::requiredIf($useAcLikeDetail)],
        ];
    }

    public function messages(): array 
    {
        return [
            'category.required' => 'Kategori tidak boleh kosong',
            'category.*' => 'Kategori tidak valid',

            'account_code.required' => 'Kode akun tidak boleh kosong',
            'account_code.unique' => 'Kode akun sudah terdaftar',

            'asset_serial_number.unique' => 'Nomor serial sudah terdaftar',

            'unit.required' => 'Unit tidak boleh kosong',
            'unit.*' => 'Unit tidak valid',

            'location.required' => 'Lokasi tidak boleh kosong',

            'purchase_year.string' => 'Tahun pembelian harus berupa teks',

            'detail.required' => 'Detail tidak boleh kosong',
            'detail.*' => 'Detail tidak valid'
        ];
    }
}
