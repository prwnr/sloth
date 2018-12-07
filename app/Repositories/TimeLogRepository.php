<?php

namespace App\Repositories;

use App\Models\TimeLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class TimeLogRepository implements RepositoryInterface
{

    /**
     * @var TimeLog
     */
    private $timeLog;

    /**
     * TimeLogRepository constructor.
     * @param TimeLog $timeLog
     */
    public function __construct(TimeLog $timeLog)
    {
        $this->timeLog = $timeLog;
    }

    /**
     * {@inheritdoc}
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->timeLog->all($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function allWith(array $relations, array $columns = ['*']): Collection
    {
        return $this->timeLog->with($relations)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function find(int $id, array $columns = ['*']): Model
    {
        return $this->timeLog->findOrFail($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function findWith(int $id, array $relations, array $columns = ['*']): Model
    {
        return $this->timeLog->with($relations)->findOrFail($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): Model
    {
        $hasDuration = $data['duration'] ?? false;
        return $this->timeLog->create([
            'member_id' => $data['member'],
            'project_id' => $data['project'],
            'task_id' => $data['task'] ?? null,
            'description' => $data['description'],
            'start' => $hasDuration ? null : Carbon::now(),
            'duration' => $hasDuration ? $data['duration'] : 0,
            'created_at' => $data['created_at']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function update(int $id, array $data): Model
    {
        $timeLog = $this->find($id);
        $timeLog->update([
            'project_id' => $data['project'],
            'task_id' => $data['task'] ?? null,
            'description' => $data['description'],
            'created_at' => $data['created_at']
        ]);

        return $timeLog;
    }

    /**
     * @param int $id
     * @param int $duration
     * @return Model
     */
    public function startTime(int $id, int $duration): Model
    {
        $timeLog = $this->find($id);

        $timeLog->update([
            'start' => Carbon::now(),
            'duration' => $duration
        ]);
        return $timeLog;
    }

    /**
     * @param int $id
     * @param int $duration
     * @return Model
     */
    public function stopTime(int $id, int $duration): Model
    {
        $timeLog = $this->find($id);

        $timeLog->update([
            'start' => null,
            'duration' => $duration
        ]);
        return $timeLog;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id): bool
    {
        $timeLog = $this->find($id);
        if ($timeLog->delete()) {
            return true;
        }

        return false;
    }
}