<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
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
        $uniqueProjectRule = Rule::unique('projects')->where(function (Builder $query) {
            $data = $this->validationData();
            $teamId = Auth::user()->team_id;

            return $query->where('code', $data['code'] ?? '')->where('team_id', $teamId);
        });

        if ($this->project) {
            $uniqueProjectRule = $uniqueProjectRule->ignore($this->project);
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
