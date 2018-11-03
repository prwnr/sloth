<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Project::class, function (Faker $faker) {
    $teamId = factory(\App\Models\Team::class)->create()->id;
    return [
        'code' => $faker->toUpper($faker->randomLetter . $faker->randomLetter . $faker->randomLetter),
        'name' => $faker->company,
        'budget' => $faker->numberBetween(0, 999999),
        'budget_period' => $faker->randomKey(\App\Models\Project::BUDGET_PERIOD),
        'budget_currency_id' => \App\Models\Currency::all()->random()->id,
        'client_id' => factory(\App\Models\Client::class)->create(['team_id' => $teamId])->id,
        'team_id' => $teamId,
        'billing_id' => factory(\App\Models\Billing::class)->create()->id
    ];
});
