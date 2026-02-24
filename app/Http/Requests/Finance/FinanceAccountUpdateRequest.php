<?php

namespace App\Http\Requests\Finance;

use Illuminate\Validation\Rule;

class FinanceAccountUpdateRequest extends FinanceAccountStoreRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $accountParam = $this->route('account');
        $accountId = is_object($accountParam)
            ? (string) data_get($accountParam, 'id', '')
            : (string) $accountParam;

        return [
            'code' => [
                'required',
                'string',
                'max:64',
                Rule::unique('finance_accounts', 'code')->ignore($accountId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:64'],
            'class_no' => ['required', 'integer', 'between:1,255'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
