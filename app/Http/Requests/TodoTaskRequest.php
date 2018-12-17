<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class TodoTaskRequest
 * @package App\Http\Requests
 */
class TodoTaskRequest extends FormRequest
{

    use RequiresTask;

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
            'description' => [
                'required', 'string', 'max:500', 'min:3'
            ],
            'project_id' => [
                'required', 'numeric',
            ],
            'task_id' => [
                $this->taskRequirementRule(), 'numeric'
            ],
            'timelog_id' => [
                'nullable', 'numeric'
            ],
            'finished' => [
                'nullable', 'boolean'
            ],
            'priority' => [
                'required', 'numeric'
            ]
        ];
    }
}
