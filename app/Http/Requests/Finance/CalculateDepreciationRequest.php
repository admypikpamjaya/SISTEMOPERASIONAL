<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class CalculateDepreciationRequest extends FormRequest
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
            'asset_id' => 'required|string|exists:assets,id',
            'acquisition_cost' => 'required|numeric|min:0',
            'useful_life_months' => 'required|integer|min:1',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|digits:4|between:1900,2100',
        ];
    }

    public function messages(): array
    {
        return [
            'asset_id.required' => 'Asset wajib disertakan.',
            'asset_id.exists' => 'Asset tidak ditemukan.',
            'acquisition_cost.required' => 'Nilai perolehan wajib diisi.',
            'acquisition_cost.numeric' => 'Nilai perolehan harus berupa angka.',
            'acquisition_cost.min' => 'Nilai perolehan minimal 0.',
            'useful_life_months.required' => 'Umur aset (bulan) wajib diisi.',
            'useful_life_months.integer' => 'Umur aset (bulan) harus bilangan bulat.',
            'useful_life_months.min' => 'Umur aset (bulan) minimal 1.',
            'month.required' => 'Bulan wajib diisi.',
            'month.integer' => 'Bulan harus berupa angka.',
            'month.between' => 'Bulan harus antara 1 sampai 12.',
            'year.required' => 'Tahun wajib diisi.',
            'year.integer' => 'Tahun harus berupa angka.',
            'year.digits' => 'Tahun harus 4 digit.',
            'year.between' => 'Tahun tidak valid.',
        ];
    }
}
