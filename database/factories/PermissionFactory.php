<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Permission::class, function (Faker $faker) {
    return [
        'name' => $faker->toLower($faker->word),
        'display_name' => $faker->word,
        'description' => $faker->words(5)
    ];
});
