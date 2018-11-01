<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Billing::class, function (Faker $faker) {
    return [
        'rate' => $faker->numberBetween(0, 50),
        'type' => $faker->randomKey(\App\Models\Billing::getRateTypes()),
        'currency_id' => \App\Models\Currency::all()->random()->id
    ];
});
