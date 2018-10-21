<?php

namespace App\Http\Requests\Tracker;

/**
 * Class CreateLogRequest
 * @package App\Http\Requests\Tracker
 */
class CreateLogRequest extends LogRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'member' => [
                'required', 'numeric'
            ],
            'project' => [
                'required', 'numeric'
            ],
            'task' => [
                $this->taskRequirementRule(), 'numeric'
            ],
            'description' => [
                'nullable', 'string', 'max:200'
            ],
            'duration' => [
                'nullable', 'numeric'
            ],
            'created_at' => [
                'required', 'date'
            ]
        ];
    }
}
