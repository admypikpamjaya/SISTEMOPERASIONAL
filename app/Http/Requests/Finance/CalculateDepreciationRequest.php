<?php

namespace App\Http\Requests\Finance;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Throwable;

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
            'acquisition_cost' => 'nullable|numeric|min:0',
            'period_start' => 'required|date_format:Y-m',
            'period_end' => 'required|date_format:Y-m',
        ];
    }

    public function messages(): array
    {
        return [
            'asset_id.required' => 'Asset wajib disertakan.',
            'asset_id.exists' => 'Asset tidak ditemukan.',
            'acquisition_cost.numeric' => 'Nilai perolehan harus berupa angka.',
            'acquisition_cost.min' => 'Nilai perolehan minimal 0.',
            'period_start.required' => 'Periode dari wajib diisi.',
            'period_start.date_format' => 'Format periode dari tidak valid.',
            'period_end.required' => 'Periode sampai wajib diisi.',
            'period_end.date_format' => 'Format periode sampai tidak valid.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $periodStart = $this->parsePeriodMonth($this->input('period_start'));
            $periodEnd = $this->parsePeriodMonth($this->input('period_end'));

            if ($periodStart === null || $periodEnd === null) {
                return;
            }

            if ($periodEnd->lt($periodStart)) {
                $validator->errors()->add(
                    'period_end',
                    'Periode sampai harus sama atau setelah periode dari.'
                );
            }
        });
    }

    private function parsePeriodMonth(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat(
                'Y-m',
                trim($value),
                config('app.timezone')
            )->startOfMonth();
        } catch (Throwable) {
            return null;
        }
    }
}
