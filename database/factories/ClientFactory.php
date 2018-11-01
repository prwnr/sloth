<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Client::class, function (Faker $faker) {
    return [
        'company_name' => $faker->company,
        'city' => $faker->city,
        'zip' => $faker->postcode,
        'country' => $faker->country,
        'street' => $faker->streetAddress,
        'vat' => $faker->numberBetween(1111111, 9999999),
        'fullname' => $faker->name,
        'email' => $faker->email,
        'team_id' => factory(\App\Models\Team::class)->create()->id,
        'billing_id' => factory(\App\Models\Billing::class)->create()->id
    ];
});
