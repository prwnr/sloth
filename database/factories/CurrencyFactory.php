<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Currency::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'code' => $faker->currencyCode,
        'symbol' => $faker->randomElement(['$', 'PLN', 'C$', '€', '£'])
    ];
});
