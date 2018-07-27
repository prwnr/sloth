<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ClientRequest
 * @package App\Http\Requests
 */
class ClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $requiredStringRules = [
            'required', 'string', 'max:255'
        ];

        return [
            'company_name' => $requiredStringRules,
            'street' => $requiredStringRules,
            'zip' => $requiredStringRules,
            'country' => $requiredStringRules,
            'city' => $requiredStringRules,
            'vat' => $requiredStringRules,
            'fullname' => $requiredStringRules,
            'email' => [
                'required', 'string', 'email', 'max:255'
            ],
            'billing_rate' => [
                'required', 'numeric', 'between:0,999.99'
            ],
            'billing_currency' => [
                'required', 'numeric'
            ],
            'billing_type' => [
                'required', 'string'
            ]
        ];
    }
}
