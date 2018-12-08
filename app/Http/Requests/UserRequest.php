<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UserRequest
 * @package App\Http\Requests
 */
class UserRequest extends FormRequest
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
        $uniqueUserRule = Rule::unique('users', 'email');
        if ($this->user) {
            $uniqueUserRule = $uniqueUserRule->ignore($this->user);
        }

        $requiredStringRules = [
            'required', 'string', 'max:255'
        ];

        return [
            'firstname' => $requiredStringRules,
            'lastname' => $requiredStringRules,
            'email' => [
                'required', 'string', 'email', 'max:255', $uniqueUserRule
            ],
            'password' => [
                'nullable', 'string', 'min:6', 'confirmed'
            ]
        ];
    }
}
