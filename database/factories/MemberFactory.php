<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Team\Member::class, function (Faker $faker) {
    $teamId = factory(\App\Models\Team::class)->create()->id;
    return [
        'team_id' => $teamId,
        'user_id' => factory(\App\Models\User::class)->create(['team_id' => $teamId, 'owns_team' => $teamId])->id,
        'billing_id' => factory(\App\Models\Billing::class)->create()->id,
    ];
});
