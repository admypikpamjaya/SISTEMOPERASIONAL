<?php

namespace App\Http\Requests\Report;

use App\Enums\Report\Maintenance\AssetMaintenanceReportStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMaintenanceReportStatusRequest extends FormRequest
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
            'id' => 'required|string|exists:maintenance_logs,id',
            'status' => ['required', Rule::enum(AssetMaintenanceReportStatus::class)]
        ];
    }

    public function messages(): array 
    {
        return [
            'id.required' => 'Field id wajib disertakan',
            'id.string' => 'Field id harus berupa string',
            'id.exists' => 'Laporan tidak ditemukan',

            'status.required' => 'Field status wajib disertakan',
            'status.*' => 'Status tidak valid'
        ];
    }
}
