<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class MemberRequest
 * @package App\Http\Requests
 */
class MemberRequest extends FormRequest
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
        return [
            'email' => [
                'nullable', 'string', 'email', 'max:255'
            ],
            'roles' => [
                'required', 'array', 'min:1'
            ],
            'billing_rate' => [
                'required', 'numeric', 'between:0,999.99'
            ],
            'billing_currency' => [
                'required', 'numeric'
            ],
            'billing_type' => [
                'required', 'string'
            ],
            'projects' => [
                'nullable', 'array'
            ],
        ];
    }
}
