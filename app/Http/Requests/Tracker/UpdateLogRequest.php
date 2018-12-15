<?php

namespace App\Http\Requests\Tracker;

use App\Http\Requests\RequiresTask;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateLogRequest
 * @package App\Http\Requests\Tracker
 */
class UpdateLogRequest extends FormRequest
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
            'project' => [
                'required', 'numeric'
            ],
            'task' => [
                $this->taskRequirementRule(), 'numeric'
            ],
            'description' => [
                'nullable', 'string', 'max:200'
            ],
            'created_at' => [
                'required', 'date'
            ]
        ];
    }
}
