<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class ProjectRequest
 * @package App\Http\Requests
 */
class ProjectRequest extends FormRequest
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
        $uniqueProjectRule = Rule::unique('projects', 'code'); //TODO make code unique per Team
        if ($this->project) {
            $uniqueProjectRule = $uniqueProjectRule->ignore($this->project->id);
        }

        return [
            'name' => [
                'required', 'string', 'max:255',
            ],
            'code' => [
                'required', 'string', 'max:4', $uniqueProjectRule
            ],
            'budget' => [
                'required', 'numeric', 'between:0,' . Project::MAX_BILLING_RATE
            ],
            'budget_currency' => [
                'required', 'numeric'
            ],
            'budget_period' => [
                'required', 'string'
            ],
            'client' => [
                'required', 'numeric'
            ],
            'billing_rate' => [
                'required', 'numeric', 'between:0,' . Project::MAX_BILLING_RATE
            ],
            'billing_currency' => [
                'required', 'numeric'
            ],
            'billing_type' => [
                'required', 'string'
            ],
            'users' => [
                'nullabe', 'array'
            ],
            'tasks' => [
                'array'
            ]
        ];
    }
}
