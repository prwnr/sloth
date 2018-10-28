<?php

namespace App\Http\Requests\Tracker;

use App\Models\Project;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class LogRequest
 * @package App\Http\Requests\Tracker
 */
class LogRequest extends FormRequest
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
            //
        ];
    }

    /**
     * @return string
     */
    protected function taskRequirementRule(): string
    {
        if ($this->isTaskRequired()) {
            return 'required';
        }

        return 'nullable';
    }

    /**
     * @return bool
     */
    private function isTaskRequired(): bool
    {
        $projectInput = $this->get('project');
        if (!$projectInput) {
            return false;
        }

        try {
            /** @var Project $project */
            $project = Project::findOrFail($projectInput);
            $tasks = $project->tasks()->where('is_deleted', false)->get();
            return \count($tasks) > 0;
        } catch (ModelNotFoundException $ex) {
            return false;
        }
    }
}
