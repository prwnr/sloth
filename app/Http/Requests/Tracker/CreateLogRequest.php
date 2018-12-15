<?php

namespace App\Http\Requests\Tracker;

use App\Http\Requests\RequiresTask;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CreateLogRequest
 * @package App\Http\Requests\Tracker
 */
class CreateLogRequest extends FormRequest
{

    use RequiresTask;

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
