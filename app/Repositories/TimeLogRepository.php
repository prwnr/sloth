<?php

namespace App\Repositories;

use App\Models\TimeLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class TimeLogRepository
 * @package App\Repositories
 */
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
     * @return TimeLog
     */
    public function find(int $id, array $columns = ['*']): TimeLog
    {
        return $this->timeLog->findOrFail($id, $columns);
    }

    /**
     * {@inheritdoc}
     * @return TimeLog
     */
    public function findWith(int $id, array $relations, array $columns = ['*']): TimeLog
    {
        return $this->timeLog->with($relations)->findOrFail($id, $columns);
    }

    /**
     * {@inheritdoc}
     * @return TimeLog
     */
    public function create(array $data): TimeLog
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
     * @return TimeLog
     */
    public function update(int $id, array $data): TimeLog
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
     * @param array $data
     * @return TimeLog
     */
    public function updateTime(int $id, array $data): TimeLog
    {
        $timeLog = $this->find($id);
        if (isset($data['time'])) {
            if ($data['time'] === TimeLog::STOP) {
                $data['start'] = null;
            }

            if ($data['time'] === TimeLog::START) {
                $data['start'] = Carbon::now();
            }

            unset($data['time']);
        }

        $timeLog->update($data);
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