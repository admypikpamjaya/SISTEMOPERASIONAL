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
            'working_description' => 'required|string',
            'pic' => 'required|string',
            'cost' => 'required|numeric',
            'evidence_photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120'
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
            'working_description.required' => 'Deskripsi Pengerjaan tidak boleh kosong',

            'pic.required' => 'PIC tidak boleh kosong',

            'cost.required' => 'Biaya tidak boleh kosong',
            'cost.numeric' => 'Biaya harus berupa angka',

            'evidence_photo.required' => 'Foto bukti pengerjaan tidak boleh kosong',
            'evidence_photo.image' => 'Foto bukti pengerjaan harus berupa gambar yang valid',
            'evidence_photo.mimes' => 'Foto bukti pengerjaan harus berupa file JPEG, PNG, JPG, atau WEBP',
            'evidence_photo.max' => 'Foto bukti pengerjaan maksimal 5MB'
        ];
    }
}
