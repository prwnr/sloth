<?php

use Faker\Generator as Faker;

$factory->define(App\Models\TodoTask::class, function (Faker $faker) {
    return [
        'description' => $faker->sentence,
        'member_id' => factory(\App\Models\Team\Member::class)->create()->id,
        'project_id' => factory(\App\Models\Project::class)->create()->id,
        'task_id' => factory(\App\Models\Project\Task::class)->create()->id,
        'timelog_id' => factory(\App\Models\TimeLog::class)->create()->id,
        'finished' => $faker->boolean,
        'priority' => $faker->numberBetween(0, 5)
    ];
});
