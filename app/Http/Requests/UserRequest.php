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
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $uniqueUserRule = Rule::unique('users', 'email'); //TODO make email unique per Team
        if ($this->user) {
            $uniqueUserRule = $uniqueUserRule->ignore($this->user->id);
        }

        $requiredStringRules = [
            'required', 'string', 'max:255'
        ];

        return [
            'firstname' => $requiredStringRules,
            'skin' => $requiredStringRules,
            'lastname' => $requiredStringRules,
            'email' => [
                'required', 'string', 'email', 'max:255', $uniqueUserRule
            ],
            'password' => [
                'nullable', 'string', 'min:6', 'confirmed'
            ],'nullable|string|min:6|confirmed'
        ];
    }
}
