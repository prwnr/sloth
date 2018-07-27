<?php

namespace App\Http\Requests\Tracker;

/**
 * Class UpdateLogRequest
 * @package App\Http\Requests\Tracker
 */
class UpdateLogRequest extends LogRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'project' => [
                'required', 'numeric'
            ],
            'task' => [
                $this->taskRequirementRule(), 'numeric'
            ],
            'description' => 'nullable|string|max:200'
        ];
    }
}
