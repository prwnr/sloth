<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Role::class, function (Faker $faker) {
    return [
        'name' => $faker->toLower($faker->word),
        'display_name' => $faker->word,
        'description' => $faker->words(10),
        'team_id' => factory(\App\Models\Team::class)->create()->id
    ];
});
