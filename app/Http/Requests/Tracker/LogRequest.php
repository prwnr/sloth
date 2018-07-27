<?php

namespace App\Http\Requests\Tracker;

use App\Models\Project;
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
        if ($projectInput) {
            /** @var Project $project */
            $project = Project::find($projectInput);
            if ($project) {
                $tasks = $project->tasks()->where('is_deleted', false)->get();
                return \count($tasks) > 0;
            }
        }

        return false;
    }
}
