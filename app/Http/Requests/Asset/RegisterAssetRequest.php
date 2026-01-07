<?php

namespace App\Http\Requests\Asset;

use App\Enums\Asset\AssetCategory;
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
        return [
            'category' => ['required', Rule::enum(AssetCategory::class)],
            'account_code' => ['required', 'unique:assets,account_code'],
            'asset_serial_number' => ['nullable', 'unique:assets,serial_number'],
            'location' => ['required', 'string'],
            'purchase_year'=> ['nullable', 'integer', 'min:2000', 'max:' . date('Y')],
            'detail' => ['required', 'array']
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

            'location.required' => 'Lokasi tidak boleh kosong',

            'purchase_year.integer' => 'Tahun pembelian harus berupa angka',
            'purchase_year.min' => 'Tahun pembelian minimal 2000',
            'purchase_year.max' => 'Tahun pembelian maksimal ' . date('Y'),

            'detail.required' => 'Detail tidak boleh kosong',
            'detail.*' => 'Detail tidak valid'
        ];
    }
}
