<?php

namespace App\Http\Requests\Asset;

use App\Enums\Asset\AssetCategory;
use App\Enums\Asset\AssetUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditAssetRequest extends FormRequest
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
            'id' => 'required|string',
            'category' => ['required', Rule::enum(AssetCategory::class)],
            'account_code' => ['required', 'unique:assets,account_code,' . $this->id],
            'asset_serial_number' => ['nullable', 'unique:assets,serial_number,' . $this->id],
            'unit' => ['required', Rule::enum(AssetUnit::class)],
            'location' => 'required|string',
            'purchase_year'=> ['nullable', 'string'],
            'detail' => ['nullable', 'array', Rule::requiredIf($useAcLikeDetail)],
        ];
    }

    public function messages(): array 
    {
        return [
            'id.required' => 'Field id wajib disertakan',

            'category.required' => 'Field kategori wajib diisi',
            'category.*' => 'Kategori tidak valid',

            'account_code.required' => 'Field kode akun wajib diisi',
            'account_code.unique' => 'Kode akun sudah digunakan',

            'asset_serial_number.unique' => 'Nomor serial sudah digunakan',

            'unit.required' => 'Field unit wajib diisi',
            'unit.*' => 'Unit tidak valid',

            'location.required' => 'Field lokasi wajib diisi',

            'purchase_year.string' => 'Tahun pembelian harus berupa teks.',

            'detail.required' => 'Field detail wajib diisi'
        ];
    }
}
