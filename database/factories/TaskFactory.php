<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Task::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'type' => $faker->randomKey(\App\Models\Task::getTypes()),
        'billable' => $faker->boolean,
        'billing_rate' => $faker->numberBetween(0, 100),
        'currency_id' => \App\Models\Currency::all()->random()->id,
        'project_id' => factory(\App\Models\Project::class)->create()->id,
        'is_deleted' => false
    ];
});
