<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Models\User::class, function (Faker $faker) {
    $teamId = factory(\App\Models\Team::class)->create()->id;
    return [
        'firstname' => $faker->name,
        'lastname' => $faker->lastName,
        'email' => $faker->unique()->safeEmail,
        'password' => \Illuminate\Support\Facades\Hash::make('secret'),
        'remember_token' => str_random(10),
        'team_id' => $teamId,
        'owns_team' => $teamId,
        'first_login' => false
    ];
});
