<?php

namespace App\Http\Requests;

use App\Models\Project;
use App\Repositories\ProjectRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Trait RequiresTask
 * @package App\Http\Requests
 */
trait RequiresTask
{

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
    protected function isTaskRequired(): bool
    {
        if (!$this instanceof FormRequest) {
            throw new \RuntimeException('RequiresTask must be used within FormRequest instance');
        }

        $projectId = $this->get('project');
        if (!$projectId) {
            return false;
        }

        if (is_array($projectId)) {
            $projectId = $this->get('project_id');
        }

        $repository = new ProjectRepository(new Project());

        try {
            $project = $repository->find($projectId);
            $tasks = $project->tasks()->where('is_deleted', false)->get();
            return \count($tasks) > 0;
        } catch (ModelNotFoundException $ex) {
            return false;
        }
    }
}