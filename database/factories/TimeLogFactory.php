<?php

use Faker\Generator as Faker;

$factory->define(App\Models\TimeLog::class, function (Faker $faker) {
    return [
        'project_id' => factory(\App\Models\Project::class)->create()->id,
        'task_id' => factory(\App\Models\Task::class)->create()->id,
        'member_id' => factory(\App\Models\Team\Member::class)->create()->id,
        'description' => $faker->sentence,
        'start' => null,
        'duration' => $faker->numberBetween(0, 99999),
        'created_at' => $faker->date('Y-m-d H:i:s')
    ];
});
