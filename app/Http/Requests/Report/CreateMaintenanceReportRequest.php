<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class CreateMaintenanceReportRequest extends FormRequest
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
            'worker_name' => 'required|string',
            'working_date' => 'required|date',
            'issue_description' => 'required|string',
            'working_description' => 'required|string'
        ];
    }

    public function messages(): array 
    {
        return [
            'asset_id.required' => 'ID aset wajib disertakan',
            'asset_id.exists' => 'Aset tidak ditemukan',

            'worker_name.required' => 'Nama Pekerja tidak boleh kosong',
            
            'working_date.required' => 'Tanggal Pengerjaan tidak boleh kosong',
            'working_date.date' => 'Tanggal Pengerjaan tidak valid',

            'issue_description.required' => 'Masalah Aset tidak boleh kosong',
            'working_description.required' => 'Deskripsi Pengerjaan tidak boleh kosong'
        ];
    }
}
